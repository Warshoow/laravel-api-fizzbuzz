# FizzBuzz REST API

A simple REST API endpoint that returns FizzBuzz, secured with API key authentication.

## Requirements

- PHP 8.2+
- Composer

## Installation

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Add your API key to `.env`:

```
APP_API_KEY=your-secret-api-key
```

## Running the Server

```bash
php artisan serve
```

The API will be available at `http://localhost:8000`

## Usage

### Endpoint

```
GET /fizzbuzz
```

### Headers

| Header    | Required | Description       |
|-----------|----------|-------------------|
| `API_KEY` | Yes      | Your API key      |

### Example Request

```bash
curl -H "API_KEY: your-secret-api-key" http://localhost:8000/fizzbuzz
```

### Response

```json
{
    "data": "FizzBuzz",
    "status": 200,
    "success": true,
    "timestamp": "2026-02-04T21:00:00.000000Z"
}
```

### Error Responses

**401 Unauthorized** - Missing API key
```json
{
    "success": false,
    "error": "Authentication Required",
    "message": "API key is required. Please provide a valid API key in the API_KEY header.",
    "status": 401
}
```

**403 Forbidden** - Invalid API key
```json
{
    "success": false,
    "error": "Authentication Failed",
    "message": "Invalid API key provided. Please check your API key and try again.",
    "status": 403
}
```

## Running Tests

```bash
php artisan test
```

## Project Structure

```
app/Http/
├── Controllers/
│   └── FizzBuzzController.php    # Main endpoint logic
└── Middleware/
    ├── EnsureApiKey.php          # API key validation
    └── ErrorManager.php          # Global error handler (optional)
```

## Note on ErrorManager

The `ErrorManager` middleware is included as a demonstration of production-ready error handling patterns. It provides consistent JSON error responses across all endpoints.

For this test's scope, it is not enabled globally, but can be activated in `bootstrap/app.php` if needed for a larger API.
