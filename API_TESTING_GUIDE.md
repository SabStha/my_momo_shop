# API Testing Guide

## Overview

This guide provides specific examples for testing the security implementations in your Laravel API.

## Authentication Setup

### 1. Create Test Users with Roles

```bash
# Create admin user
php artisan user:assign-admin admin@test.com

# Create other test users via seeder or manually
php artisan db:seed --class=UserSeeder
```

### 2. Generate API Tokens

```php
// In Laravel Tinker or test script
$admin = User::where('email', 'admin@test.com')->first();
$adminToken = $admin->createToken('admin-token')->plainTextToken;

$cashier = User::where('email', 'cashier@test.com')->first();
$cashierToken = $cashier->createToken('cashier-token')->plainTextToken;

$employee = User::where('email', 'employee@test.com')->first();
$employeeToken = $employee->createToken('employee-token')->plainTextToken;

$customer = User::where('email', 'customer@test.com')->first();
$customerToken = $customer->createToken('customer-token')->plainTextToken;
```

## API Security Tests

### 1. Authentication Tests

#### Test Unauthenticated Access
```bash
# Should return 401 Unauthorized
curl -X GET "http://localhost:8000/api/pos/orders" \
  -H "Accept: application/json"

# Expected Response:
{
  "success": false,
  "message": "Authentication required"
}
```

#### Test Invalid Token
```bash
# Should return 401 Unauthorized
curl -X GET "http://localhost:8000/api/pos/orders" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer invalid-token"
```

### 2. Authorization Tests

#### Admin Access
```bash
# Admin should access admin endpoints
curl -X GET "http://localhost:8000/api/admin/dashboard" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {admin_token}"

# Expected: 200 OK with dashboard data
```

#### Cashier Access
```bash
# Cashier should access POS but NOT admin endpoints
curl -X GET "http://localhost:8000/api/pos/orders" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {cashier_token}"
# Expected: 200 OK

curl -X GET "http://localhost:8000/api/admin/dashboard" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {cashier_token}"
# Expected: 403 Forbidden
```

#### Employee Access
```bash
# Employee should access POS but NOT reports
curl -X GET "http://localhost:8000/api/pos/orders" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {employee_token}"
# Expected: 200 OK (only their orders)

curl -X POST "http://localhost:8000/api/reports/generate" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {employee_token}"
# Expected: 403 Forbidden
```

#### Customer Access
```bash
# Customer should NOT access any POS/admin endpoints
curl -X GET "http://localhost:8000/api/pos/orders" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {customer_token}"
# Expected: 403 Forbidden
```

### 3. Order Creation Security Tests

#### Valid Order Creation (Admin)
```bash
curl -X POST "http://localhost:8000/api/pos/orders" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {admin_token}" \
  -d '{
    "type": "takeaway",
    "items": [
      {
        "product_id": 1,
        "quantity": 2
      }
    ],
    "guest_name": "John Doe",
    "guest_email": "john@example.com"
  }'

# Expected: 201 Created with calculated totals
```

#### Test Mass Assignment Protection
```bash
curl -X POST "http://localhost:8000/api/pos/orders" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {admin_token}" \
  -d '{
    "type": "takeaway",
    "items": [{"product_id": 1, "quantity": 1}],
    "total_amount": 999999.99,
    "tax_amount": 99999.99,
    "grand_total": 1099998.98,
    "paid_by": 999
  }'

# Expected: 201 Created but financial amounts should be CALCULATED, not the submitted values
# Check response that totals don't match the malicious input
```

#### Test Input Validation
```bash
# Test invalid order type
curl -X POST "http://localhost:8000/api/pos/orders" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {admin_token}" \
  -d '{
    "type": "invalid-type",
    "items": [{"product_id": 1, "quantity": 1}]
  }'
# Expected: 422 Validation Error

# Test excessive quantity
curl -X POST "http://localhost:8000/api/pos/orders" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {admin_token}" \
  -d '{
    "type": "takeaway",
    "items": [{"product_id": 1, "quantity": 100}]
  }'
# Expected: 422 Validation Error

# Test dine-in without table
curl -X POST "http://localhost:8000/api/pos/orders" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {admin_token}" \
  -d '{
    "type": "dine-in",
    "items": [{"product_id": 1, "quantity": 1}]
  }'
# Expected: 422 Validation Error (table_id required for dine-in)
```

### 4. Data Exposure Tests

#### Employee Data Exposure
```bash
# Create order as admin
ORDER_ID="1"

# Employee should see order but NOT financial details
curl -X GET "http://localhost:8000/api/pos/orders/${ORDER_ID}" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {employee_token}"

# Check response does NOT include:
# - amount_received
# - change
# - guest_email (if employee is not admin/cashier)
```

#### Admin Data Exposure
```bash
# Admin should see ALL financial details
curl -X GET "http://localhost:8000/api/pos/orders/${ORDER_ID}" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {admin_token}"

# Check response INCLUDES:
# - amount_received
# - change
# - guest_email
# - All financial data
```

### 5. Order Authorization Tests

#### Employee Order Access
```bash
# Employee creates order
curl -X POST "http://localhost:8000/api/pos/orders" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {employee_token}" \
  -d '{
    "type": "takeaway",
    "items": [{"product_id": 1, "quantity": 1}]
  }'

# Get the order ID from response, then:

# Employee should see their own orders
curl -X GET "http://localhost:8000/api/pos/orders" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {employee_token}"
# Should only return orders created by this employee

# Employee should be able to update status of pending orders they created
curl -X PUT "http://localhost:8000/api/pos/orders/${ORDER_ID}/status" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {employee_token}" \
  -d '{"status": "preparing"}'
# Expected: 200 OK

# Employee should NOT be able to delete orders
curl -X DELETE "http://localhost:8000/api/pos/orders/${ORDER_ID}" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {employee_token}"
# Expected: 403 Forbidden
```

### 6. Rate Limiting Tests

#### Authentication Rate Limiting
```bash
# Test login rate limiting (5 attempts per minute)
for i in {1..6}; do
  echo "Attempt $i:"
  curl -X POST "http://localhost:8000/login" \
    -H "Content-Type: application/x-www-form-urlencoded" \
    -H "Accept: application/json" \
    -d "email=wrong@email.com&password=wrongpassword"
  echo -e "\n"
done

# 6th attempt should return 429 Too Many Requests
```

#### API Rate Limiting
```bash
# Test API rate limiting (60 requests per minute)
for i in {1..5}; do
  echo "API Request $i:"
  curl -X GET "http://localhost:8000/api/pos/orders" \
    -H "Accept: application/json" \
    -H "Authorization: Bearer {admin_token}"
  echo -e "\n"
done

# Should all succeed (under rate limit)
```

#### Employee Verification Rate Limiting
```bash
# Test employee verification rate limiting (10 attempts per minute)
for i in {1..11}; do
  echo "Employee verification attempt $i:"
  curl -X POST "http://localhost:8000/api/employee/verify" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{
      "identifier": "wrong@email.com",
      "password": "wrongpassword"
    }'
  echo -e "\n"
done

# 11th attempt should return 429 Too Many Requests
```

### 7. Business Logic Tests

#### Order Status Transitions
```bash
# Create a completed order first, then try to change status
curl -X PUT "http://localhost:8000/api/pos/orders/${COMPLETED_ORDER_ID}/status" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {admin_token}" \
  -d '{"status": "pending"}'

# Expected: 422 Validation Error (cannot change completed order status)
```

#### Invalid Status Transitions
```bash
# Try invalid status transition (pending -> completed)
curl -X PUT "http://localhost:8000/api/pos/orders/${PENDING_ORDER_ID}/status" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {admin_token}" \
  -d '{"status": "completed"}'

# Expected: 422 Validation Error (must go through preparing -> prepared -> completed)
```

## Expected Security Responses

### Success Responses
- **200 OK**: Authorized access with proper data filtering
- **201 Created**: Successful resource creation with calculated values
- **204 No Content**: Successful deletion/update

### Security Responses
- **401 Unauthorized**: Missing or invalid authentication
- **403 Forbidden**: Insufficient permissions for action
- **422 Unprocessable Entity**: Validation failed (with error details)
- **429 Too Many Requests**: Rate limit exceeded

### Error Response Format
```json
{
  "success": false,
  "message": "Error description",
  "errors": {
    "field": ["Specific validation error"]
  }
}
```

## Automated Testing Scripts

### Create Test Script
```bash
#!/bin/bash
# test_security.sh

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

BASE_URL="http://localhost:8000"
ADMIN_TOKEN="your_admin_token_here"
EMPLOYEE_TOKEN="your_employee_token_here"

echo -e "${YELLOW}Testing API Security...${NC}\n"

# Test 1: Unauthenticated access
echo "Test 1: Unauthenticated access to protected endpoint"
RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" "$BASE_URL/api/pos/orders")
if [ "$RESPONSE" -eq 401 ]; then
    echo -e "${GREEN}✓ PASS: Unauthenticated access properly blocked${NC}"
else
    echo -e "${RED}✗ FAIL: Expected 401, got $RESPONSE${NC}"
fi

# Test 2: Employee accessing admin endpoint
echo -e "\nTest 2: Employee accessing admin endpoint"
RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" \
  -H "Authorization: Bearer $EMPLOYEE_TOKEN" \
  "$BASE_URL/api/admin/dashboard")
if [ "$RESPONSE" -eq 403 ]; then
    echo -e "${GREEN}✓ PASS: Employee access to admin endpoint properly blocked${NC}"
else
    echo -e "${RED}✗ FAIL: Expected 403, got $RESPONSE${NC}"
fi

# Add more tests...
echo -e "\n${YELLOW}Security testing complete!${NC}"
```

## Performance Testing

### Load Testing with Authentication
```bash
# Test authenticated endpoints under load
ab -n 100 -c 10 -H "Authorization: Bearer {token}" \
  http://localhost:8000/api/pos/orders

# Monitor for:
# - Response times under load
# - Rate limiting behavior
# - Memory usage
# - Database connection pooling
```

Run these tests regularly to ensure your security implementations remain effective!