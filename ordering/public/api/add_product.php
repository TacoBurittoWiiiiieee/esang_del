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

// Validate required fields
$requiredFields = ['name', 'category_id', 'item_type'];
foreach ($requiredFields as $field) {
    if (!isset($input[$field]) || (is_string($input[$field]) && trim($input[$field]) === '')) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required']);
        exit();
    }
}

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
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Insert the product
        $name = $input['name'];
        $categoryId = $input['category_id'];
        $description = $input['description'] ?? '';
        $price = $input['price'] ?? 0;
        $image = $input['image'] ?? null;
        $itemType = $input['item_type'];
        
        // Insert product into inventory table
        $stmt = $conn->prepare("INSERT INTO inventory (product_name, product_description, unit_price, category_id, product_image, stock) VALUES (?, ?, ?, ?, ?, ?)");
        $stock = 0; // Default stock value
        $stmt->bind_param("ssdiss", $name, $description, $price, $categoryId, $image, $stock);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to add product to inventory: ' . $stmt->error);
        }
        
        $productId = $conn->insert_id;
        $stmt->close();
        
        // Handle variations for multi-price items
        if ($itemType === 'multi-price' && !empty($input['variations'])) {
            $stmt = $conn->prepare("INSERT INTO product_variations (product_id, size, price) VALUES (?, ?, ?)");
            
            foreach ($input['variations'] as $variation) {
                if (empty($variation['size']) || !isset($variation['price'])) {
                    throw new Exception('Invalid variation data');
                }
                
                $size = $variation['size'];
                $price = $variation['price'];
                $stmt->bind_param("isd", $productId, $size, $price);
                
                if (!$stmt->execute()) {
                    throw new Exception('Failed to add product variation: ' . $stmt->error);
                }
            }
            
            $stmt->close();
        }
        // Handle flavors for flavor items
        elseif ($itemType === 'flavors' && !empty($input['flavors'])) {
            $stmt = $conn->prepare("INSERT INTO product_flavors (product_id, name) VALUES (?, ?)");
            
            foreach ($input['flavors'] as $flavor) {
                if (empty(trim($flavor['name']))) {
                    continue; // Skip empty flavor names
                }
                
                $flavorName = trim($flavor['name']);
                $stmt->bind_param("is", $productId, $flavorName);
                
                if (!$stmt->execute()) {
                    throw new Exception('Failed to add product flavor: ' . $stmt->error);
                }
            }
            
            $stmt->close();
        }
        
        // Commit transaction
        $conn->commit();
        
        // Get the newly created product with all its details
        $product = [
            'id' => $productId,
            'name' => $name,
            'description' => $description,
            'price' => (float)$price,
            'image' => $image,
            'category_id' => $categoryId,
            'item_type' => $itemType,
            'variations' => [],
            'flavors' => []
        ];
        
        // If it's a multi-price item, get the variations
        if ($itemType === 'multi-price') {
            $variations = [];
            $varStmt = $conn->prepare("SELECT id, size, price FROM product_variations WHERE product_id = ?");
            $varStmt->bind_param("i", $productId);
            $varStmt->execute();
            $varResult = $varStmt->get_result();
            
            while ($row = $varResult->fetch_assoc()) {
                $variations[] = [
                    'id' => $row['id'],
                    'size' => $row['size'],
                    'price' => (float)$row['price']
                ];
            }
            $product['variations'] = $variations;
            $varStmt->close();
        }
        // If it's a flavors item, get the flavors
        elseif ($itemType === 'flavors') {
            $flavors = [];
            $flavorStmt = $conn->prepare("SELECT id, name FROM product_flavors WHERE product_id = ?");
            $flavorStmt->bind_param("i", $productId);
            $flavorStmt->execute();
            $flavorResult = $flavorStmt->get_result();
            
            while ($row = $flavorResult->fetch_assoc()) {
                $flavors[] = [
                    'id' => $row['id'],
                    'name' => $row['name']
                ];
            }
            $product['flavors'] = $flavors;
            $flavorStmt->close();
        }
        
        echo json_encode([
            'success' => true, 
            'message' => 'Product added successfully',
            'product' => $product
        ]);
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        throw $e;
    }
    
    $conn->close();
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
