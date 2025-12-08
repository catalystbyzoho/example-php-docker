<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Simple routing
$requestMethod = $_SERVER['REQUEST_METHOD'];
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestUri = ($requestUri === '' || $requestUri === '/') ? '/' : rtrim($requestUri, '/');

// Get request body
$input = json_decode(file_get_contents('php://input'), true);

// Route handling
switch ($requestUri) {
    case '/':
        handleEmpty($requestMethod);
        break;
    case '/api/users':
        handleUsers($requestMethod, $input);
        break;
    
    case '/api/products':
        handleProducts($requestMethod, $input);
        break;
    
    case '/api/health':
        handleHealth($requestMethod);
        break;
    
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
        break;
}

// Handle empty root endpoint
function handleEmpty($method) {
    if ($method === 'GET') {
        http_response_code(200);
        echo json_encode([
            'message' => 'Welcome to the PHP API. Available endpoints: /api/users, /api/products, /api/health'
        ]);
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
    }
}

// API 1: Users endpoint
function handleUsers($method, $input) {
    $users = [
        ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com'],
        ['id' => 2, 'name' => 'Jane Smith', 'email' => 'jane@example.com'],
    ];
    
    switch ($method) {
        case 'GET':
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $users,
                'count' => count($users)
            ]);
            break;
        
        case 'POST':
            if (!isset($input['name']) || !isset($input['email'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Name and email are required']);
                return;
            }
            
            $newUser = [
                'id' => count($users) + 1,
                'name' => $input['name'],
                'email' => $input['email']
            ];
            
            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'User created successfully',
                'data' => $newUser
            ]);
            break;
        
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
    }
}

// API 2: Products endpoint
function handleProducts($method, $input) {
    $products = [
        ['id' => 1, 'name' => 'Laptop', 'price' => 999.99, 'stock' => 50],
        ['id' => 2, 'name' => 'Mouse', 'price' => 29.99, 'stock' => 100],
        ['id' => 3, 'name' => 'Keyboard', 'price' => 79.99, 'stock' => 75],
    ];
    
    switch ($method) {
        case 'GET':
            // Optional query parameter for filtering
            $productId = isset($_GET['id']) ? (int)$_GET['id'] : null;
            
            if ($productId) {
                $product = array_filter($products, fn($p) => $p['id'] === $productId);
                if (empty($product)) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Product not found']);
                    return;
                }
                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'data' => array_values($product)[0]
                ]);
            } else {
                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'data' => $products,
                    'count' => count($products)
                ]);
            }
            break;
        
        case 'POST':
            if (!isset($input['name']) || !isset($input['price']) || !isset($input['stock'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Name, price, and stock are required']);
                return;
            }
            
            $newProduct = [
                'id' => count($products) + 1,
                'name' => $input['name'],
                'price' => (float)$input['price'],
                'stock' => (int)$input['stock']
            ];
            
            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'Product created successfully',
                'data' => $newProduct
            ]);
            break;
        
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
    }
}

// API 3: Health check endpoint
function handleHealth($method) {
    if ($method !== 'GET') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        return;
    }
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'status' => 'healthy',
        'timestamp' => date('Y-m-d H:i:s'),
        'server' => [
            'php_version' => PHP_VERSION,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'PHP Built-in Server'
        ]
    ]);
}