<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Get the raw POST data
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($input['name']) || empty(trim($input['name']))) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Category name is required']);
    exit();
}

$name = trim($input['name']);
$itemType = isset($input['item_type']) ? $input['item_type'] : 'single-price';

// Database configuration
$config = [
    'servername' => 'localhost',
    'username' => 'root',
    'password' => '',
    'dbname' => 'esang'
];

try {
    // Create connection
    $conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception('Connection failed: ' . $conn->connect_error);
    }
    
    // Set charset to utf8mb4
    $conn->set_charset("utf8mb4");
    
    // Check if category already exists
    $checkStmt = $conn->prepare("SELECT category_id FROM categories WHERE category_name = ?");
    $checkStmt->bind_param("s", $name);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows > 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Category already exists']);
        $checkStmt->close();
        $conn->close();
        exit();
    }
    $checkStmt->close();
    
    // Insert new category
    $stmt = $conn->prepare("INSERT INTO categories (category_name, item_type) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $itemType);
    
    if ($stmt->execute()) {
        $categoryId = $stmt->insert_id;
        echo json_encode([
            'success' => true, 
            'message' => 'Category added successfully',
            'category' => [
                'id' => (int)$categoryId,
                'name' => $name,
                'item_type' => $itemType
            ]
        ]);
    } else {
        throw new Exception('Failed to add category: ' . $stmt->error);
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
