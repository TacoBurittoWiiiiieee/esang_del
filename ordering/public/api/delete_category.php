<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Function to send JSON response
function sendResponse($success, $message, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode([
        'success' => $success,
        'message' => $message
    ]);
    exit();
}

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    sendResponse(true, 'OK', 200);
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Method not allowed', 405);
}

// Get the raw POST data
$input = json_decode(file_get_contents('php://input'), true);

// Log the received data for debugging
error_log('Received data: ' . print_r($input, true));

// Validate input
if (!isset($input['category_id']) || empty(trim($input['category_id']))) {
    sendResponse(false, 'Category ID is required', 400);
}

$categoryId = trim($input['category_id']);

// Database configuration - adjust these values according to your database setup
$config = [
    'servername' => 'localhost',
    'username' => 'root',
    'password' => '',
    'dbname' => 'esang'
];

try {
    // Create connection with error mode enabled
    $conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception('Connection failed: ' . $conn->connect_error);
    }
    
    // Set charset to utf8mb4
    if (!$conn->set_charset('utf8mb4')) {
        throw new Exception('Error setting charset: ' . $conn->error);
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Check if the category exists
        $checkStmt = $conn->prepare("SELECT category_id FROM categories WHERE category_id = ?");
        if (!$checkStmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        
        $checkStmt->bind_param('s', $categoryId);
        if (!$checkStmt->execute()) {
            throw new Exception('Execute failed: ' . $checkStmt->error);
        }
        
        $result = $checkStmt->get_result();
        if ($result->num_rows === 0) {
            throw new Exception('Category not found');
        }
        $checkStmt->close();
        
        // First, delete all products in this category
        $deleteProductsStmt = $conn->prepare("DELETE FROM products WHERE category_id = ?");
        if (!$deleteProductsStmt) {
            throw new Exception('Prepare failed for products delete: ' . $conn->error);
        }
        
        $deleteProductsStmt->bind_param('s', $categoryId);
        if (!$deleteProductsStmt->execute()) {
            throw new Exception('Delete products failed: ' . $deleteProductsStmt->error);
        }
        
        // Then delete the category
        $deleteCategoryStmt = $conn->prepare("DELETE FROM categories WHERE category_id = ?");
        if (!$deleteCategoryStmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        
        $deleteCategoryStmt->bind_param('s', $categoryId);
        if (!$deleteCategoryStmt->execute()) {
            throw new Exception('Delete category failed: ' . $deleteCategoryStmt->error);
        }
        
        if ($deleteCategoryStmt->affected_rows === 0) {
            throw new Exception('No rows affected - category might have been deleted already');
        }
        
        // Commit the transaction
        $conn->commit();
        
        sendResponse(true, 'Category and its products deleted successfully');
        
    } catch (Exception $e) {
        // Rollback the transaction on error
        if (isset($conn)) {
            $conn->rollback();
        }
        
        error_log('Error in delete_category.php: ' . $e->getMessage());
        error_log('Stack trace: ' . $e->getTraceAsString());
        
        sendResponse(false, 'Error: ' . $e->getMessage(), 500);
    } finally {
        // Close statements and connection
        if (isset($checkStmt)) $checkStmt->close();
        if (isset($deleteProductsStmt)) $deleteProductsStmt->close();
        if (isset($deleteCategoryStmt)) $deleteCategoryStmt->close();
        if (isset($conn)) $conn->close();
    }
    
} catch (Exception $e) {
    error_log('Database connection error: ' . $e->getMessage());
    sendResponse(false, 'Database error: ' . $e->getMessage(), 500);
}
