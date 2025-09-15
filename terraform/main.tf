terraform {
  required_providers {
    google = {
      source  = "hashicorp/google"
      version = "~> 5.0"
    }
  }
}

provider "google" {
  project = var.project_id
  region  = var.region
}

variable "project_id" {
  description = "The GCP project ID to deploy to."
  type        = string
  default     = "nuage-money-api-dev"
}

variable "region" {
  description = "The GCP region to deploy resources in."
  type        = string
  default     = "us-central1"
}

resource "google_compute_network" "vpc" {
  project                 = var.project_id
  name                    = "nuage-money-api-dev-vpc"
  auto_create_subnetworks = false
  description             = "VPC for nuage-money-api dev environment"
}

resource "google_compute_subnetwork" "subnet" {
  project       = var.project_id
  name          = "nuage-money-api-dev-subnet"
  ip_cidr_range = "10.10.0.0/24"
  network       = google_compute_network.vpc.id
  region        = var.region
  description   = "Subnet for nuage-money-api dev services"
}

# Enable the Service Networking API. required for VPC peering.
resource "google_project_service" "service_networking" {
  project                    = var.project_id
  service                    = "servicenetworking.googleapis.com"

}

# Reserve a private IP range for Google's services to use within our VPC.
resource "google_compute_global_address" "private_ip_address" {
  project       = var.project_id
  name          = "nuage-money-api-dev-db-private-ip"
  purpose       = "VPC_PEERING"
  address_type  = "INTERNAL"
  prefix_length = 16
  network       = google_compute_network.vpc.id
}

# Establish the VPC peering connection.
resource "google_service_networking_connection" "private_vpc_connection" {
  network                 = google_compute_network.vpc.id
  service                 = "servicenetworking.googleapis.com"
  reserved_peering_ranges = [google_compute_global_address.private_ip_address.name]

  # This dependency ensures the API is enabled before attempting to create the connection.
  depends_on = [google_project_service.service_networking]
}

# Provision the Cloud SQL instance for MySQL.
resource "google_sql_database_instance" "instance" {
  project             = var.project_id
  name                = "nuage-money-api-dev-db"
  database_version    = "MYSQL_8_0"
  region              = var.region

  settings {
    tier = "db-n1-standard-1" # A small, cost-effective tier for dev

    ip_configuration {
      ipv4_enabled    = true
      private_network = google_compute_network.vpc.id
    }

    # It's good practice to have backups, even for dev
    backup_configuration {
      enabled = true
      binary_log_enabled = true
    }
  }

  # This dependency ensures the VPC peering is established before the instance is created.
  depends_on = [google_service_networking_connection.private_vpc_connection]
}

# Enable the Redis API
resource "google_project_service" "redis" {
  project = var.project_id
  service = "redis.googleapis.com"
  # Ensure service networking is enabled first
  depends_on = [google_project_service.service_networking]
}

# Provision the Memorystore for Redis instance for cache and queues
resource "google_redis_instance" "cache" {
  project          = var.project_id
  name             = "nuage-money-api-dev-redis"
  tier             = "BASIC" # Basic tier for dev/testing, not highly available
  memory_size_gb   = 1

  # The location must be a specific zone
  location_id      = "${var.region}-a"

  authorized_network = google_compute_network.vpc.id
  connect_mode       = "PRIVATE_SERVICE_ACCESS"

  depends_on = [
    google_service_networking_connection.private_vpc_connection,
    google_project_service.redis
  ]
}

# --- Secrets Management ---

# Enable the Secret Manager API
resource "google_project_service" "secret_manager" {
  project = var.project_id
  service = "secretmanager.googleapis.com"
  depends_on = [google_project_service.service_networking]
}

// Define the secrets to be created from the .env.example
locals {
  secrets = toset([
    "APP_KEY",
    "DB_PASSWORD",
    "REDIS_PASSWORD",
    "MAIL_USERNAME",
    "MAIL_PASSWORD",
    "AWS_ACCESS_KEY_ID",
    "AWS_SECRET_ACCESS_KEY",
    "PUSHER_APP_ID",
    "PUSHER_APP_KEY",
    "PUSHER_APP_SECRET"
  ])
}

// Create the secret containers
resource "google_secret_manager_secret" "secrets" {
  for_each = local.secrets
  project  = var.project_id
  secret_id = each.key

  replication {
    auto {}
  }

  

  depends_on = [google_project_service.secret_manager]
}

// Create the initial secret versions with placeholder values
resource "google_secret_manager_secret_version" "secret_versions" {
  for_each      = local.secrets
  secret        = google_secret_manager_secret.secrets[each.key].id
  secret_data   = "changeme"

  lifecycle {
    ignore_changes = [secret_data]
  }
}

# --- Application & Container Setup ---

# Enable the Artifact Registry API
resource "google_project_service" "artifact_registry" {
  project    = var.project_id
  service    = "artifactregistry.googleapis.com"
  depends_on = [google_project_service.service_networking]
}

# Create the Docker repository in Artifact Registry
resource "google_artifact_registry_repository" "repository" {
  project       = var.project_id
  location      = var.region
  repository_id = "nuage-money-api-dev"
  description   = "Docker repository for the Nuage Money API"
  format        = "DOCKER"

  depends_on = [google_project_service.artifact_registry]
}

# --- IAM & Service Account ---

# Create the dedicated service account for the application
resource "google_service_account" "service_account" {
  project      = var.project_id
  account_id   = "nuage-money-api-sa"
  display_name = "Nuage Money API Service Account"
}

# Grant the service account the Cloud SQL Client role for the project
resource "google_project_iam_member" "sql_client" {
  project = var.project_id
  role    = "roles/cloudsql.client"
  member  = "serviceAccount:${google_service_account.service_account.email}"
}

# Grant the service account access to each of the secrets
resource "google_secret_manager_secret_iam_member" "secret_accessor" {
  for_each  = google_secret_manager_secret.secrets
  project   = each.value.project
  secret_id = each.value.secret_id
  role      = "roles/secretmanager.secretAccessor"
  member    = "serviceAccount:${google_service_account.service_account.email}"
}

# --- Compute & Deployment ---

# Enable the Serverless VPC Access API
resource "google_project_service" "vpc_access" {
  project    = var.project_id
  service    = "vpcaccess.googleapis.com"
  depends_on = [google_project_service.service_networking]
}

# Create a Serverless VPC Access connector
resource "google_vpc_access_connector" "connector" {
  project       = var.project_id
  name          = "nuage-money-api-connector"
  region        = var.region
  ip_cidr_range = "10.8.0.0/28" # A dedicated /28 range for the connector
  network       = google_compute_network.vpc.id
  depends_on    = [google_project_service.vpc_access]
}

# Enable the Cloud Run API
resource "google_project_service" "cloud_run" {
  project    = var.project_id
  service    = "run.googleapis.com"
  depends_on = [google_project_service.service_networking]
}

# Create the main web application service in Cloud Run
resource "google_cloud_run_v2_service" "web" {
  project  = var.project_id
  name     = "nuage-money-api-web-dev"
  location = var.region

  template {
    service_account = google_service_account.service_account.email
    
    containers {
      image = "${var.region}-docker.pkg.dev/${var.project_id}/${google_artifact_registry_repository.repository.repository_id}/nuage-money-api:latest"
      
      ports {
        container_port = 8000
      }

      env {
        name = "APP_ENV"
        value = "production"
      }
      env {
        name = "APP_DEBUG"
        value = "false"
      }
      env {
        name = "APP_URL"
        value = "https://api.dev.nuage.money"
      }
      env {
        name = "LOG_CHANNEL"
        value = "stackdriver"
      }
      env {
        name = "DB_CONNECTION"
        value = "mysql"
      }
      env {
        name = "DB_HOST"
        value = google_sql_database_instance.instance.private_ip_address
      }
      env {
        name = "DB_PORT"
        value = "3306"
      }
      env {
        name = "DB_DATABASE"
        value = "laravel"
      }
      env {
        name = "DB_USERNAME"
        value = "root"
      }
      env {
        name = "CACHE_DRIVER"
        value = "redis"
      }
      env {
        name = "QUEUE_CONNECTION"
        value = "redis"
      }
      env {
        name = "SESSION_DRIVER"
        value = "redis"
      }
      env {
        name = "REDIS_HOST"
        value = google_redis_instance.cache.host
      }
      env {
        name = "REDIS_PORT"
        value = google_redis_instance.cache.port
      }

      # Reference secrets from Secret Manager
      env {
        name = "APP_KEY"
        value_source {
          secret_key_ref {
            secret = "APP_KEY"
            version = "latest"
          }
        }
      }
      env {
        name = "DB_PASSWORD"
        value_source {
          secret_key_ref {
            secret = "DB_PASSWORD"
            version = "latest"
          }
        }
      }
      env {
        name = "REDIS_PASSWORD"
        value_source {
          secret_key_ref {
            secret = "REDIS_PASSWORD"
            version = "latest"
          }
        }
      }
      env {
        name = "MAIL_USERNAME"
        value_source {
          secret_key_ref {
            secret = "MAIL_USERNAME"
            version = "latest"
          }
        }
      }
      env {
        name = "MAIL_PASSWORD"
        value_source {
          secret_key_ref {
            secret = "MAIL_PASSWORD"
            version = "latest"
          }
        }
      }
      env {
        name = "AWS_ACCESS_KEY_ID"
        value_source {
          secret_key_ref {
            secret = "AWS_ACCESS_KEY_ID"
            version = "latest"
          }
        }
      }
      env {
        name = "AWS_SECRET_ACCESS_KEY"
        value_source {
          secret_key_ref {
            secret = "AWS_SECRET_ACCESS_KEY"
            version = "latest"
          }
        }
      }
      env {
        name = "PUSHER_APP_ID"
        value_source {
          secret_key_ref {
            secret = "PUSHER_APP_ID"
            version = "latest"
          }
        }
      }
      env {
        name = "PUSHER_APP_KEY"
        value_source {
          secret_key_ref {
            secret = "PUSHER_APP_KEY"
            version = "latest"
          }
        }
      }
      env {
        name = "PUSHER_APP_SECRET"
        value_source {
          secret_key_ref {
            secret = "PUSHER_APP_SECRET"
            version = "latest"
          }
        }
      }
    }

    vpc_access {
      connector = google_vpc_access_connector.connector.id
      egress    = "ALL_TRAFFIC"
    }
  }

  depends_on = [
    google_vpc_access_connector.connector,
    google_project_service.cloud_run
  ]
}
