<?php
require 'db.php';
header('Content-Type: application/json; charset=utf-8');
$dir = IMAGES_DIR;
$webBase = IMAGES_URL;

if (!is_dir($dir)) {
    echo json_encode([]);
    exit;
}
$files = array_values(array_filter(scandir($dir), function($f) use ($dir) {
    return !in_array($f, ['.','..','thumbs']) && is_file($dir.$f);
}));
$items = [];
try {
    // Fetch all metadata to map filename to times
    $stmt = $pdo->query("SELECT filename, start_time, expiry_time FROM offer_metadata");
    $meta = $stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC);
    // PDO::FETCH_GROUP makes filename the key [ 'file.jpg' => [ ['start_time' => ...], ... ] ]
    
    foreach ($files as $f) {
        $path = $dir . $f;
        $mtime = filemtime($path);
        
        $startTime = $meta[$f][0]['start_time'] ?? null;
        $expiryTime = $meta[$f][0]['expiry_time'] ?? null;
        
        $items[] = [
            'filename' => $f, 
            'mtime' => $mtime, 
            'name' => $f, 
            'url' => $webBase . $f, 
            'time' => date('Y-m-d H:i:s', $mtime),
            'start_time' => $startTime ? date('d M Y - h:i A', strtotime($startTime)) : 'N/A',
            'expiry_time' => $expiryTime ? date('d M Y - h:i A', strtotime($expiryTime)) : 'N/A'
        ];
    }
} catch (Exception $e) {
    // If metadata fails, fallback to basic file info
    foreach ($files as $f) {
        $path = $dir . $f;
        $mtime = filemtime($path);
        $items[] = ['filename' => $f, 'mtime' => $mtime, 'name' => $f, 'url' => $webBase . $f, 'time' => date('Y-m-d H:i:s', $mtime)];
    }
}
usort($items, function($a,$b){ return $b['mtime'] <=> $a['mtime']; });
echo json_encode($items);
?>
