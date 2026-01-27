#!/bin/bash
#
# Test script for webhook functionality
# This simulates a GitHub webhook request
#

WEBHOOK_URL="http://localhost/webhook.php"
SECRET_TOKEN="your-secret-token-here"

# Sample GitHub push event payload
PAYLOAD='{
  "ref": "refs/heads/main",
  "repository": {
    "full_name": "Copcorico/lebonplan",
    "name": "lebonplan"
  },
  "pusher": {
    "name": "test-user",
    "email": "test@example.com"
  },
  "commits": [
    {
      "id": "abc123",
      "message": "Test commit",
      "author": {
        "name": "Test User",
        "email": "test@example.com"
      }
    }
  ]
}'

# Generate signature
SIGNATURE=$(echo -n "$PAYLOAD" | openssl dgst -sha256 -hmac "$SECRET_TOKEN" | sed 's/^.* //')

echo "Testing webhook endpoint..."
echo "URL: $WEBHOOK_URL"
echo "Payload: $PAYLOAD"
echo ""
echo "Sending request..."

# Send the request
RESPONSE=$(curl -s -w "\nHTTP_CODE:%{http_code}" \
  -X POST \
  -H "Content-Type: application/json" \
  -H "X-GitHub-Event: push" \
  -H "X-Hub-Signature-256: sha256=$SIGNATURE" \
  -d "$PAYLOAD" \
  "$WEBHOOK_URL")

HTTP_CODE=$(echo "$RESPONSE" | grep "HTTP_CODE:" | cut -d: -f2)
BODY=$(echo "$RESPONSE" | grep -v "HTTP_CODE:")

echo "Response Code: $HTTP_CODE"
echo "Response Body: $BODY"
echo ""

if [ "$HTTP_CODE" = "200" ]; then
    echo "✓ Test PASSED - Webhook responded successfully"
    exit 0
else
    echo "✗ Test FAILED - Unexpected response code"
    exit 1
fi
