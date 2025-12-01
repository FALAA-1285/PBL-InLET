<?php
/**
 * Upload Helper Functions
 */

// Upload directory
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('UPLOAD_URL', 'uploads/');

// Allowed image types
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp']);
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

/**
 * Upload image file
 * @param array $file $_FILES array
 * @param string $subfolder Subfolder dalam uploads (optional)
 * @return array ['success' => bool, 'message' => string, 'path' => string]
 */
function uploadImage($file, $subfolder = '') {
    // Check if file was uploaded
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return [
            'success' => false,
            'message' => 'File upload failed. Error: ' . ($file['error'] ?? 'Unknown error')
        ];
    }
    
    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        return [
            'success' => false,
            'message' => 'File is too large. Maximum 5MB.'
        ];
    }
    
    // Check file type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, ALLOWED_IMAGE_TYPES)) {
        return [
            'success' => false,
            'message' => 'File type not allowed. Only JPG, PNG, GIF, and WEBP.'
        ];
    }
    
    // Create upload directory if not exists
    $uploadPath = UPLOAD_DIR . $subfolder;
    if (!file_exists($uploadPath)) {
        mkdir($uploadPath, 0755, true);
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('img_', true) . '.' . $extension;
    $filepath = $uploadPath . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        $relativePath = UPLOAD_URL . $subfolder . $filename;
        return [
            'success' => true,
            'message' => 'File uploaded successfully.',
            'path' => $relativePath,
            'filename' => $filename
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Failed to move file to uploads folder.'
        ];
    }
}

/**
 * Delete uploaded file
 * @param string $filepath Relative path dari uploads/
 * @return bool
 */
function deleteUploadedFile($filepath) {
    if (empty($filepath)) {
        return false;
    }
    
    // Remove uploads/ prefix if exists
    $filepath = str_replace('uploads/', '', $filepath);
    $fullPath = UPLOAD_DIR . $filepath;
    
    if (file_exists($fullPath)) {
        return unlink($fullPath);
    }
    
    return false;
}

/**
 * Get all uploaded images
 * @param string $subfolder Subfolder dalam uploads (optional)
 * @return array
 */
function getUploadedImages($subfolder = '') {
    $uploadPath = UPLOAD_DIR . $subfolder;
    $images = [];
    
    if (file_exists($uploadPath)) {
        $files = scandir($uploadPath);
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..' && preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $file)) {
                $images[] = [
                    'filename' => $file,
                    'path' => UPLOAD_URL . $subfolder . $file,
                    'size' => filesize($uploadPath . $file),
                    'modified' => filemtime($uploadPath . $file)
                ];
            }
        }
    }
    
    return $images;
}
?>

