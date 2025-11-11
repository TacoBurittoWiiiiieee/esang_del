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

// Check if file was uploaded without errors
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    $error = $_FILES['image']['error'] ?? 'No file uploaded';
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'File upload error: ' . $error]);
    exit();
}

$file = $_FILES['image'];

// Validate file type
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$fileType = mime_content_type($file['tmp_name']);

if (!in_array($fileType, $allowedTypes)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, GIF, and WebP are allowed.']);
    exit();
}

// Validate file size (max 5MB)
$maxFileSize = 5 * 1024 * 1024; // 5MB
if ($file['size'] > $maxFileSize) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'File is too large. Maximum size is 5MB.']);
    exit();
}

// Create uploads directory if it doesn't exist
$uploadDir = '../../uploads/products/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Generate a unique filename
$fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
$fileName = uniqid('img_') . '_' . time() . '.' . $fileExtension;
$targetPath = $uploadDir . $fileName;

// Move the uploaded file to the target directory
if (move_uploaded_file($file['tmp_name'], $targetPath)) {
    // Return the relative path to the uploaded file
    $relativePath = '/uploads/products/' . $fileName;
    // Database configuration (if needed for image reference)
    $config = [
        'servername' => 'localhost',
        'username' => 'root',
        'password' => '',
        'dbname' => 'esang'
    ];
    echo json_encode([
        'success' => true, 
        'filePath' => $relativePath,
        'fileName' => $fileName
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file.']);
}
?>
