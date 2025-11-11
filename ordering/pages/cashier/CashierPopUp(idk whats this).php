<?php
// Use shared bootstrap for db() and session helpers
require_once __DIR__ . '/../_bootstrap.php';

// Provide flexible order/cashier inputs (GET, POST, or session)
$orderId = isset($_REQUEST['orderId']) ? (int)$_REQUEST['orderId'] : 1;
$cashierId = isset($_REQUEST['cashierId']) ? (int)$_REQUEST['cashierId'] : ($_SESSION['cashierId'] ?? 1);

// Helper: detect AJAX requests
$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
        || (isset($_REQUEST['ajax']) && (string)$_REQUEST['ajax'] === '1');

$conn = db(); // mysqli from _bootstrap.php

// Fetch latest request status safely
$status = null;
$stmt = @$conn->prepare("SELECT status FROM INVOICE_MODIFICATION_REQUEST WHERE orderId = ? AND cashierId = ? ORDER BY requestId DESC LIMIT 1");
if ($stmt) {
    $stmt->bind_param("ii", $orderId, $cashierId);
    $stmt->execute();
    $stmt->bind_result($status);
    $stmt->fetch();
    $stmt->close();
}

// If this is an AJAX call, handle actions and return JSON
if ($isAjax) {
    header('Content-Type: application/json');
    $action = $_POST['action'] ?? $_GET['action'] ?? null;
    try {
        if ($action === 'request_modify') {
            // Insert a pending request only when no request exists yet
            if ($status === null) {
                $insert = $conn->prepare("INSERT INTO INVOICE_MODIFICATION_REQUEST (orderId, cashierId, status, requested_at) VALUES (?, ?, 'pending', NOW())");
                if ($insert) {
                    $insert->bind_param('ii', $orderId, $cashierId);
                    $insert->execute();
                    $insert->close();
                    $status = 'pending';
                    echo json_encode(['success' => true, 'status' => $status]);
                    exit;
                }
            }
            echo json_encode(['success' => true, 'status' => $status ?? 'none']);
            exit;
        } elseif ($action === 'modify_invoice') {
            // Allow modify only if the latest status is 'accepted'
            if ($status === 'accepted') {
                echo json_encode(['success' => true, 'allowed' => true, 'message' => 'You can now modify the invoice.']);
                exit;
            }
            $msg = $status === 'rejected' ? 'Modification not allowed' : 'Waiting for admin approval';
            echo json_encode(['success' => false, 'allowed' => false, 'message' => $msg, 'status' => $status]);
            exit;
        }
        echo json_encode(['success' => false, 'message' => 'Unknown action']);
        exit;
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
}

// Non-AJAX usage continues to render the small bootstrap demo page
// Handle non-AJAX POSTs for backwards compatibility (redirect back with message)
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['request_modify'])) {
        if ($status == null) {
            $insert = $conn->prepare("INSERT INTO INVOICE_MODIFICATION_REQUEST (orderId, cashierId, status, requested_at) VALUES (?, ?, 'pending', NOW())");
            if ($insert) {
                $insert->bind_param("ii", $orderId, $cashierId);
                $insert->execute();
                $insert->close();
                $status = "pending";
            }
        }
    }

    if (isset($_POST['modify_invoice'])) {
        if ($status == 'accepted') {
            $message = "You can now modify the invoice!";
        } elseif ($status == 'rejected') {
            $message = "Modify not allowed";
        } else {
            $message = "Waiting for admin approval";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Cashier Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-5">

    <h2>Cashier Panel - Invoice #<?php echo $orderId; ?></h2>

    <form method="POST" class="mt-3">
        <button type="submit" name="request_modify" class="btn btn-primary">Request Invoice Modification</button>
        <button type="submit" name="modify_invoice" class="btn btn-warning">Modify Invoice</button>
    </form>

    <p class="mt-3">Current Status: <b><?php echo $status ?? 'No Request Sent'; ?></b></p>

    <!-- Popup Modal -->
    <div class="modal fade" id="statusModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">Invoice Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php echo $message ?: "No action taken yet."; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <?php if ($message) { ?>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            let modal = new bootstrap.Modal(document.getElementById('statusModal'));
            modal.show();
        });
    </script>
    <?php } ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>