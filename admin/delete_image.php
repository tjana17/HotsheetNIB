<?php
require 'db.php';
header('Content-Type: application/json; charset=utf-8');
if (empty($_SESSION['user_id'])) {
    echo json_encode(['success'=>false,'message'=>'Not authenticated']);
    exit;
}
$input = json_decode(file_get_contents('php://input'), true);
$filename = basename($input['filename'] ?? '');
if (!$filename) {
    echo json_encode(['success'=>false,'message'=>'No filename']);
    exit;
}
$path = IMAGES_DIR . $filename;
$thumb = IMAGES_DIR . 'thumbs/' . $filename;
if (!file_exists($path)) {
    echo json_encode(['success'=>false,'message'=>'File not found']);
    exit;
}
$ok = @unlink($path);
if (file_exists($thumb)) @unlink($thumb);
if($ok){
    try {
        if (isset($pdo)) {
            $stmt = $pdo->prepare("DELETE FROM offer_metadata WHERE filename = ?");
            $stmt->execute([$filename]);
        }
    } catch (Exception $e) {
        // Log error but don't fail the primary file deletion response
        error_log("Metadata deletion error: " . $e->getMessage());
    }
    log_activity($conn, $_SESSION['user_id'], 'Deleted image: '.$filename);
    echo json_encode(['success'=>true]);
}
else echo json_encode(['success'=>false,'message'=>'Unable to delete file']);
?>
