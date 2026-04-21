<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../core/config.php';
require_once '../core/database.php';

if(isset($_GET['q']) && strlen($_GET['q']) > 2) {
    $db = new Database();
    $conn = $db->connect();
    // ค้นหาบทความที่มีคำที่พยาบาลกำลังพิมพ์
    $stmt = $conn->prepare("SELECT article_id, title FROM knowledge_articles WHERE title LIKE ? LIMIT 3");
    $stmt->execute(["%" . $_GET['q'] . "%"]);
    echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll()]);
} else {
    echo json_encode(['status' => 'empty']);
}
?>