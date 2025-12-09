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
        // Override default JSON header for HTML response
        header('Content-Type: text/html; charset=utf-8');

        $endpoints = [
            ['method' => 'GET', 'path' => '/', 'desc' => 'Welcome page'],
            ['method' => 'GET', 'path' => '/api/health', 'desc' => 'Health check'],
            ['method' => 'GET', 'path' => '/api/users', 'desc' => 'List users'],
            ['method' => 'POST', 'path' => '/api/users', 'desc' => 'Create user'],
            ['method' => 'GET', 'path' => '/api/products', 'desc' => 'List products (optional ?id=)'],
            ['method' => 'POST', 'path' => '/api/products', 'desc' => 'Create product'],
        ];

        $rows = '';
        foreach ($endpoints as $ep) {
            $rows .= '<tr>'
                . '<td><code>' . htmlspecialchars($ep['method']) . '</code></td>'
                . '<td><code>' . htmlspecialchars($ep['path']) . '</code></td>'
                . '<td>' . htmlspecialchars($ep['desc']) . '</td>'
                . '</tr>';
        }

        $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>PHP API</title>
  <style>
    body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; margin: 32px; color: #1f2933; }
    h1 { margin-bottom: 4px; }
    p  { margin-top: 4px; color: #52606d; }
    table { border-collapse: collapse; margin-top: 16px; width: 100%; max-width: 720px; }
    th, td { border: 1px solid #e0e7ef; padding: 10px; text-align: left; }
    th { background: #f7f9fc; }
    code { background: #f1f5f9; padding: 2px 4px; border-radius: 4px; }
  </style>
</head>
<body>
  <h1>PHP API</h1>
  <p>Available endpoints and methods:</p>
  <table>
    <thead>
      <tr><th>Method</th><th>Path</th><th>Description</th></tr>
    </thead>
    <tbody>
      $rows
    </tbody>
  </table>
</body>
</html>
HTML;

        http_response_code(200);
        echo $html;
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