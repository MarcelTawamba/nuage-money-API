#!/bin/bash
# This script starts up the dev environment by applying the Terraform configuration.

echo "🚀 Starting up all GCP infrastructure for the dev environment..."

# 1. Submit the build to Google Cloud Build
echo "📦 Building and pushing the Docker image to Artifact Registry..."
gcloud builds submit --config cloudbuild.yaml .

# 2. Apply the Terraform configuration
echo "🏗️ Applying Terraform configuration..."
# The -chdir flag runs terraform from the correct directory.
# The -auto-approve flag confirms the action without an interactive prompt.
terraform -chdir=./terraform apply -auto-approve

echo "✅ Startup complete."