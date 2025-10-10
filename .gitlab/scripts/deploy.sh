#!/bin/sh

set -euo pipefail

# Check if environment parameter is provided
if [ -z "$1" ]; then
  echo "Error: Environment parameter is required (staging or production)"
  exit 1
fi

ENVIRONMENT=$1

# Validate environment parameter
if [ "$ENVIRONMENT" != "staging" ] && [ "$ENVIRONMENT" != "production" ]; then
  echo "Error: Environment must be 'staging' or 'production'"
  exit 1
fi

CURRENT_CONTENT=$(curl --silent --header "PRIVATE-TOKEN: ${CONFIG_REPO_TOKEN}" \
  "https://gitlab.com/api/v4/projects/eternaltwin%2Fconfig/repository/files/pousty%2Fapps%2Femush%2Fchannels%2F${ENVIRONMENT}.json?ref=main" \
  | jq -r '.content' | base64 -d)

NEW_CONTENT=$(echo "$CURRENT_CONTENT" | jq --arg hash "$CI_COMMIT_SHA" \
'.release = $hash')

echo "Updating ${ENVIRONMENT}.json with commit $CI_COMMIT_SHA"

echo "$NEW_CONTENT" | jq '.'

curl --request PUT \
  --header "PRIVATE-TOKEN: ${CONFIG_REPO_TOKEN}" \
  --header "Content-Type: application/json" \
  --data "{
    \"branch\": \"main\",
    \"content\": $(echo "$NEW_CONTENT" | jq -Rs .),
    \"commit_message\": \"[emush] [${ENVIRONMENT}] $CI_COMMIT_SHA\"
  }" \
  "https://gitlab.com/api/v4/projects/eternaltwin%2Fconfig/repository/files/pousty%2Fapps%2Femush%2Fchannels%2F${ENVIRONMENT}.json"
