# API Documentation

## Overview

The Habesha Finance Platform provides a RESTful API for managing financial data. All API endpoints require authentication using Laravel Sanctum.

## Authentication

### Login
```http
POST /api/login
Content-Type: application/json

{
    "email": "user@example.com",
    "password": "password"
}
```

**Response:**
```json
{
    "token": "1|abc123...",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "user@example.com"
    }
}
```

### Logout
```http
POST /api/logout
Authorization: Bearer {token}
```

## Invoices

### List Invoices
```http
GET /api/invoices
Authorization: Bearer {token}
```

**Query Parameters:**
- `page` (optional): Page number for pagination
- `per_page` (optional): Items per page (default: 15)
- `status` (optional): Filter by status (draft, sent, paid, overdue, cancelled)
- `client_id` (optional): Filter by client ID

**Response:**
```json
{
    "data": [
        {
            "id": 1,
            "invoice_number": "INV-0001",
            "client": {
                "id": 1,
                "name": "Client Name",
                "email": "client@example.com"
            },
            "project": {
                "id": 1,
                "name": "Project Name"
            },
            "issue_date": "2025-01-15",
            "due_date": "2025-02-15",
            "status": "sent",
            "subtotal": 1000.00,
            "tax_rate": 21.00,
            "tax_amount": 210.00,
            "total_amount": 1210.00,
            "created_at": "2025-01-15T10:00:00Z"
        }
    ],
    "links": {
        "first": "http://localhost/api/invoices?page=1",
        "last": "http://localhost/api/invoices?page=1",
        "prev": null,
        "next": null
    },
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 1,
        "per_page": 15,
        "to": 1,
        "total": 1
    }
}
```

### Create Invoice
```http
POST /api/invoices
Authorization: Bearer {token}
Content-Type: application/json

{
    "client_id": 1,
    "project_id": 1,
    "issue_date": "2025-01-15",
    "due_date": "2025-02-15",
    "items": [
        {
            "description": "Web Development",
            "quantity": 10,
            "unit_price": 75.00
        },
        {
            "description": "Design Services",
            "quantity": 5,
            "unit_price": 50.00
        }
    ],
    "tax_rate": 21,
    "notes": "Payment within 30 days",
    "terms": "Standard terms and conditions"
}
```

**Response:**
```json
{
    "id": 1,
    "invoice_number": "INV-0001",
    "client_id": 1,
    "project_id": 1,
    "issue_date": "2025-01-15",
    "due_date": "2025-02-15",
    "status": "draft",
    "subtotal": 1000.00,
    "tax_rate": 21.00,
    "tax_amount": 210.00,
    "total_amount": 1210.00,
    "notes": "Payment within 30 days",
    "terms": "Standard terms and conditions",
    "created_at": "2025-01-15T10:00:00Z",
    "items": [
        {
            "id": 1,
            "description": "Web Development",
            "quantity": 10,
            "unit_price": 75.00,
            "total_price": 750.00
        },
        {
            "id": 2,
            "description": "Design Services",
            "quantity": 5,
            "unit_price": 50.00,
            "total_price": 250.00
        }
    ]
}
```

### Get Invoice
```http
GET /api/invoices/{id}
Authorization: Bearer {token}
```

### Update Invoice
```http
PUT /api/invoices/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
    "client_id": 1,
    "project_id": 1,
    "issue_date": "2025-01-15",
    "due_date": "2025-02-15",
    "status": "sent",
    "items": [
        {
            "description": "Updated Service",
            "quantity": 1,
            "unit_price": 100.00
        }
    ],
    "tax_rate": 21,
    "notes": "Updated notes",
    "terms": "Updated terms"
}
```

### Delete Invoice
```http
DELETE /api/invoices/{id}
Authorization: Bearer {token}
```

## Clients

### List Clients
```http
GET /api/clients
Authorization: Bearer {token}
```

### Create Client
```http
POST /api/clients
Authorization: Bearer {token}
Content-Type: application/json

{
    "name": "Client Name",
    "email": "client@example.com",
    "phone": "+251911234567",
    "company": "Company Name",
    "vat_number": "ET123456789",
    "address": "Street Address",
    "city": "Addis Ababa",
    "postal_code": "1000",
    "country": "Ethiopia"
}
```

### Get Client
```http
GET /api/clients/{id}
Authorization: Bearer {token}
```

### Update Client
```http
PUT /api/clients/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
    "name": "Updated Client Name",
    "email": "updated@example.com"
}
```

### Delete Client
```http
DELETE /api/clients/{id}
Authorization: Bearer {token}
```

## Expenses

### List Expenses
```http
GET /api/expenses
Authorization: Bearer {token}
```

**Query Parameters:**
- `page` (optional): Page number for pagination
- `per_page` (optional): Items per page (default: 15)
- `category` (optional): Filter by category
- `project_id` (optional): Filter by project ID
- `date_from` (optional): Filter expenses from date (YYYY-MM-DD)
- `date_to` (optional): Filter expenses to date (YYYY-MM-DD)

### Create Expense
```http
POST /api/expenses
Authorization: Bearer {token}
Content-Type: application/json

{
    "description": "Office supplies",
    "amount": 150.00,
    "category": "office",
    "expense_date": "2025-01-15",
    "project_id": 1,
    "is_billable": true,
    "notes": "Purchased office supplies"
}
```

## Projects

### List Projects
```http
GET /api/projects
Authorization: Bearer {token}
```

### Create Project
```http
POST /api/projects
Authorization: Bearer {token}
Content-Type: application/json

{
    "client_id": 1,
    "name": "Project Name",
    "description": "Project description",
    "status": "active",
    "start_date": "2025-01-01",
    "end_date": "2025-12-31",
    "budget": 50000.00,
    "hourly_rate": 75.00
}
```

## Dashboard

### Get Dashboard Statistics
```http
GET /api/dashboard
Authorization: Bearer {token}
```

**Response:**
```json
{
    "recent_invoices": [
        {
            "id": 1,
            "invoice_number": "INV-0001",
            "client": {
                "name": "Client Name"
            },
            "total_amount": 1210.00,
            "status": "sent",
            "created_at": "2025-01-15T10:00:00Z"
        }
    ],
    "recent_expenses": [
        {
            "id": 1,
            "description": "Office supplies",
            "amount": 150.00,
            "category": "office",
            "expense_date": "2025-01-15"
        }
    ],
    "stats": {
        "total_invoices": 25,
        "total_revenue": 50000.00,
        "total_expenses": 15000.00,
        "overdue_invoices": 3,
        "net_profit": 35000.00
    },
    "monthly_revenue": [
        {
            "month": "2024-12",
            "total": 5000.00
        },
        {
            "month": "2025-01",
            "total": 7500.00
        }
    ],
    "monthly_expenses": [
        {
            "month": "2024-12",
            "total": 2000.00
        },
        {
            "month": "2025-01",
            "total": 3000.00
        }
    ]
}
```

## Error Handling

All API endpoints return appropriate HTTP status codes and error messages:

### Validation Errors (422)
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "client_id": [
            "Een klant is verplicht."
        ],
        "items": [
            "Er moet minimaal één item toegevoegd worden."
        ]
    }
}
```

### Authorization Errors (403)
```json
{
    "message": "This action is unauthorized."
}
```

### Not Found Errors (404)
```json
{
    "message": "No query results for model [App\\Models\\Invoice] 999"
}
```

### Rate Limiting (429)
```json
{
    "message": "Te veel verzoeken. Probeer het later opnieuw.",
    "retry_after": 60
}
```

## Rate Limiting

API endpoints are rate limited to prevent abuse:
- **General endpoints**: 60 requests per minute per IP
- **Financial endpoints**: Additional audit logging
- **Authentication endpoints**: Stricter limits

## Pagination

List endpoints support pagination with the following query parameters:
- `page`: Page number (default: 1)
- `per_page`: Items per page (default: 15, max: 100)

## Filtering and Sorting

Most list endpoints support filtering and sorting:
- Use query parameters for filtering
- Use `sort` parameter for sorting (e.g., `sort=created_at` or `sort=-created_at` for descending)

## Data Validation

All input data is validated according to business rules:
- Required fields are enforced
- Data types are validated
- Business logic constraints are applied
- Custom error messages in Dutch/Amharic

## Security Considerations

- All endpoints require authentication
- CSRF protection is enabled
- Input sanitization prevents XSS attacks
- SQL injection prevention through Eloquent ORM
- Rate limiting prevents abuse
- Audit logging tracks all financial operations
