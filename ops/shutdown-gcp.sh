#!/bin/bash
# This script DESTROYS the dev environment by running terraform destroy.

echo "🔥 DESTROYING all GCP infrastructure for the dev environment..."
echo "You have 5 seconds to cancel (Ctrl+C)..."
sleep 5

# The -chdir flag runs terraform from the correct directory.
# The -auto-approve flag confirms the action without an interactive prompt.
terraform -chdir=./terraform destroy -auto-approve

echo "🛑 Shutdown complete."
