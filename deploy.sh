#!/bin/bash
#
# Deployment script for manual updates
# This script can also be called by the webhook
#

# Set the repository path
REPO_PATH="/home/runner/work/lebonplan/lebonplan"
LOG_FILE="$REPO_PATH/deployment.log"

# Function to log messages
log_message() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"
}

# Navigate to repository
cd "$REPO_PATH" || exit 1

log_message "Starting deployment..."

# Fetch latest changes
log_message "Fetching changes from origin..."
git fetch origin 2>&1 | tee -a "$LOG_FILE"

if [ $? -ne 0 ]; then
    log_message "ERROR: Git fetch failed"
    exit 1
fi

# Get current branch
CURRENT_BRANCH=$(git rev-parse --abbrev-ref HEAD)
log_message "Current branch: $CURRENT_BRANCH"

# Pull changes
log_message "Pulling changes for branch $CURRENT_BRANCH..."
git pull origin "$CURRENT_BRANCH" 2>&1 | tee -a "$LOG_FILE"

if [ $? -ne 0 ]; then
    log_message "ERROR: Git pull failed"
    exit 1
fi

log_message "Deployment completed successfully"
exit 0
