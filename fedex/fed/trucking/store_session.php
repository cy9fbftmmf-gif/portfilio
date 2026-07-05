
<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $masked_card = $_POST['masked_card'] ?? '';
    if ($masked_card) {
        $_SESSION['masked_card_number'] = $masked_card;
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success']);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'No masked card provided']);
    }
    exit;
}
?>