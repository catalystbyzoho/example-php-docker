# PHP REST API Application

A simple PHP REST API application with 3 endpoints, containerized with Docker.

## API Endpoints

### 1. Health Check
- **GET** `/api/health`
- Returns server health status and information

### 2. Users API
- **GET** `/api/users` - Get all users
- **POST** `/api/users` - Create a new user
  - Body: `{ "name": "John Doe", "email": "john@example.com" }`

### 3. Products API
- **GET** `/api/products` - Get all products
- **GET** `/api/products?id=1` - Get a specific product by ID
- **POST** `/api/products` - Create a new product
  - Body: `{ "name": "Product Name", "price": 99.99, "stock": 50 }`

## Running with Docker

### Build the image
```bash
docker build --platform=linux/amd64 -t php-rest-api .
```

### Run the container
```bash
docker run -d -p 8000:8000 --name php-api php-rest-api
```

### Test the API
```bash
# Health check
curl http://localhost:8000/api/health

# Get all users
curl http://localhost:8000/api/users

# Create a user
curl -X POST http://localhost:8000/api/users \
  -H "Content-Type: application/json" \
  -d '{"name":"John Doe","email":"john@example.com"}'

# Get all products
curl http://localhost:8000/api/products

# Get specific product
curl http://localhost:8000/api/products?id=1

# Create a product
curl -X POST http://localhost:8000/api/products \
  -H "Content-Type: application/json" \
  -d '{"name":"Laptop","price":999.99,"stock":50}'
```

## Running Locally (without Docker)

```bash
php -S localhost:8000
```

Then access the API at `http://localhost:8000`

## Docker Image Size

The Alpine-based image is lightweight (~50MB) and optimized for production use.

