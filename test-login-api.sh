#!/bin/bash
# Test the login API to see if it returns simplified user object

echo "🔬 Testing Login API Response Format..."
echo ""

curl -X POST https://amakomomo.com/api/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email":"sabstha98@gmail.com","password":"your_password"}' \
  | jq '.user | keys'

echo ""
echo ""
echo "Full response (pretty):"
curl -X POST https://amakomomo.com/api/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email":"sabstha98@gmail.com","password":"your_password"}' \
  | jq '.'

echo ""
echo "✅ Check if user object has 'roles' array (should NOT have it after fix)"

