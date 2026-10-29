<?php
/**
 * Upload Helper Functions
 * Includes extension validation, MIME verification, and serverless read-only detection.
 */

// Upload directory
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('UPLOAD_URL', 'uploads/');

// Allowed image types & extensions
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp']);
define('ALLOWED_IMAGE_EXTS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

/**
 * Upload image file
 * @param array $file $_FILES array
 * @param string $subfolder Subfolder dalam uploads (optional)
 * @return array ['success' => bool, 'message' => string, 'path' => string]
 */
function uploadImage($file, $subfolder = '')
{
    // Check if file was uploaded
    if (!isset($file) || !isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return [
            'success' => false,
            'message' => 'File upload failed. Error code: ' . ($file['error'] ?? 'No file uploaded')
        ];
    }

    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        return [
            'success' => false,
            'message' => 'File is too large. Maximum size is 5MB.'
        ];
    }

    // Validate file extension against strictly allowed list
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, ALLOWED_IMAGE_EXTS, true)) {
        return [
            'success' => false,
            'message' => 'File extension not allowed. Only JPG, JPEG, PNG, GIF, and WEBP are permitted.'
        ];
    }

    // Check MIME type using finfo
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, ALLOWED_IMAGE_TYPES, true)) {
            return [
                'success' => false,
                'message' => 'File MIME type not allowed. Detected: ' . htmlspecialchars($mimeType)
            ];
        }
    }

    // Check if running in a read-only serverless environment
    $uploadPath = rtrim(UPLOAD_DIR . $subfolder, '/\\') . DIRECTORY_SEPARATOR;
    if (!file_exists($uploadPath)) {
        if (!@mkdir($uploadPath, 0755, true)) {
            return [
                'success' => false,
                'message' => 'Upload directory cannot be created. Serverless environments like Vercel have a read-only filesystem. Please use image URLs or external cloud storage.'
            ];
        }
    }

    if (!is_writable($uploadPath)) {
        return [
            'success' => false,
            'message' => 'Upload directory is not writable. Serverless environments like Vercel have a read-only filesystem. Please use image URLs or external cloud storage.'
        ];
    }

    // Generate unique filename
    $filename = uniqid('img_', true) . '.' . $extension;
    $filepath = $uploadPath . $filename;

    // Move uploaded file
    if (@move_uploaded_file($file['tmp_name'], $filepath)) {
        $relativePath = UPLOAD_URL . ($subfolder ? rtrim($subfolder, '/') . '/' : '') . $filename;
        return [
            'success' => true,
            'message' => 'File uploaded successfully.',
            'path' => $relativePath,
            'filename' => $filename
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Failed to move uploaded file. Check directory permissions.'
        ];
    }
}

/**
 * Delete uploaded file
 * @param string $filepath Relative path dari uploads/
 * @return bool
 */
function deleteUploadedFile($filepath)
{
    if (empty($filepath)) {
        return false;
    }

    // Remove uploads/ prefix if exists
    $filepath = str_replace('uploads/', '', $filepath);
    $fullPath = UPLOAD_DIR . $filepath;

    if (file_exists($fullPath) && is_writable($fullPath)) {
        return @unlink($fullPath);
    }

    return false;
}

/**
 * Get all uploaded images
 * @param string $subfolder Subfolder dalam uploads (optional)
 * @return array
 */
function getUploadedImages($subfolder = '')
{
    $uploadPath = UPLOAD_DIR . $subfolder;
    $images = [];

    if (file_exists($uploadPath) && is_dir($uploadPath)) {
        $files = scandir($uploadPath);
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..' && preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $file)) {
                $images[] = [
                    'filename' => $file,
                    'path' => UPLOAD_URL . ($subfolder ? rtrim($subfolder, '/') . '/' : '') . $file,
                    'size' => filesize($uploadPath . '/' . $file),
                    'modified' => filemtime($uploadPath . '/' . $file)
                ];
            }
        }
    }

    return $images;
}
?>
