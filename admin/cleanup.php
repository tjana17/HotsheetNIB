<?php
/**
 * Cleanup expired offers from disk and database.
 */
function cleanup_expired_files($pdo) {
    try {
        // Find expired files
        $stmt = $pdo->prepare("SELECT filename FROM offer_metadata WHERE expiry_time <= NOW()");
        $stmt->execute();
        $expiredFiles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($expiredFiles) {
            foreach ($expiredFiles as $row) {
                $filename = $row['filename'];
                $mainFile = IMAGES_DIR . $filename;
                $thumbFile = IMAGES_DIR . 'thumbs/' . $filename;

                // Delete main file
                if (is_file($mainFile)) {
                    unlink($mainFile);
                }
                // Delete thumbnail
                if (is_file($thumbFile)) {
                    unlink($thumbFile);
                }
                
                // Delete from DB metadata
                $delStmt = $pdo->prepare("DELETE FROM offer_metadata WHERE filename = ?");
                $delStmt->execute([$filename]);
                
                // Note: log_activity might not be available globally or needs $conn
            }
        }
    } catch (Exception $e) {
        // Silently fail or log to error log to avoid breaking public site
        error_log("Cleanup error: " . $e->getMessage());
    }
}
