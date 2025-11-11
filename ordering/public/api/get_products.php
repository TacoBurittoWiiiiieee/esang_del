<?php
// Disable output buffering to prevent any unwanted output
if (ob_get_level()) ob_end_clean();

// Set error handling
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Function to send JSON response
function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    
    // Ensure we're not sending any other output
    if (ob_get_level()) {
        ob_clean();
    }
    
    echo json_encode($data);
    exit();
}

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    sendJsonResponse(['success' => true], 200);
}

// Database configuration
$config = [
    'servername' => 'localhost',
    'username' => 'root',
    'password' => '',
    'dbname' => 'esang'
];

// Function to send JSON response
function sendResponse($success, $data = null, $message = '') {
    $response = ['success' => $success];
    if ($data !== null) $response['data'] = $data;
    if ($message) $response['message'] = $message;
    echo json_encode($response);
    exit();
}

try {
    // Create database connection
    $conn = @new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception('Database connection failed: ' . $conn->connect_error);
    }
    
    // Set charset to utf8mb4
    $conn->set_charset("utf8mb4");

    // Get all categories
    $categories = [];
    $categoryQuery = "SELECT * FROM categories ORDER BY category_name";
    $categoryResult = $conn->query($categoryQuery);
    
    if ($categoryResult === false) {
        throw new Exception('Failed to fetch categories: ' . $conn->error);
    }
    
    if ($categoryResult->num_rows > 0) {
        while($category = $categoryResult->fetch_assoc()) {
            $categories[$category['category_id']] = [
                'id' => $category['category_id'],
                'name' => $category['category_name'],
                'item_type' => 'single-price', // Default value
                'items' => []
            ];
        }
    }
    
    // Get all products from inventory table
    $products = [];
    $productQuery = "SELECT i.*, c.category_name 
                    FROM inventory i 
                    LEFT JOIN categories c ON i.category_id = c.category_id 
                    ORDER BY i.product_name";
    $productResult = $conn->query($productQuery);
    
    if ($productResult === false) {
        throw new Exception('Failed to fetch products: ' . $conn->error);
    }
    
    if ($productResult->num_rows > 0) {
        while($product = $productResult->fetch_assoc()) {
            $productData = [
                'id' => $product['product_id'],
                'name' => $product['product_name'],
                'description' => $product['product_description'] ?? '',
                'price' => (float)$product['unit_price'],
                'image' => $product['product_image'] ?? '',
                'stock' => (int)$product['stock'],
                'category' => $product['category_name'] ?? 'Uncategorized',
                'category_id' => $product['category'],
                'item_type' => 'single-price' // Default value
            ];
            
            // Add to products array
            $products[] = $productData;
            
            // Add to categories array
            if (isset($categories[$product['category']])) {
                $categories[$product['category']]['items'][] = $productData;
            }
        }
    }
    
    // Close connection
    $conn->close();
    
// Send success response
    sendJsonResponse([
        'success' => true,
        'data' => [
            'categories' => array_values($categories),
            'products' => $products
        ]
    ]);
    
} catch (Exception $e) {
    // Log the error
    error_log('Error in get_products.php: ' . $e->getMessage());
    
    // Send error response
    sendJsonResponse([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ], 500);
}