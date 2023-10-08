<?php

// Allow only POST; GET requests will be cached in the browser
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Redirect to an error page or deny access
    header('HTTP/1.1 403 Forbidden');
    echo 'Access denied. Only POST requests are allowed.';
    exit;
}

// XenForo only runs on PHP 7.0
$phpVersion = phpversion();
if (version_compare($phpVersion, '7.0.0', '<'))
{
	die("PHP 7.0.0 or newer is required. $phpVersion does not meet this requirement. Please ask your host to upgrade PHP.");
}

// Function to generate a random name
function generateRandomName($length = 10) {
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $randomName = '';
    for ($i = 0; $i < $length; $i++) {
        $randomName .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomName;
}

function downloadClient() {
    // Replace 'path_to_your_loader_exe.exe' with the actual path to your Loader EXE file
    $filePath = 'path_to_your_loader_exe.exe';

    if (file_exists($filePath)) {
        // Generate a random name for the downloaded file
        $randomFileName = generateRandomName(13) . '.exe';

        // Set the content type and headers for download
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $randomFileName . '"');
        header('Content-Length: ' . filesize($filePath));

        // Read and output the file content
        readfile($filePath);
    } else {
        // File not found
        echo "File not found.";
    }
}

function doesUserHavePermissionToDownload($user) {
    // User is not logged in or is banned
    if (!$user->user_id || $user->is_banned)
        return false;
    
    // User is a customer/VIP member
    $customerGroupId = 5;
    if (in_array($customerGroupId, $user->secondary_group_ids))
        return true;
    
    // User is a staff member
    if ($user->is_moderator || $user->is_admin || $user->is_super_admin)
        return true;
    
    return false;
}

$dir = __DIR__;
require($dir . '/src/XF.php');

XF::start($dir);
$app = \XF::setupApp('XF\Pub\App');
$app->start();

$user = \XF::visitor();

if (doesUserHavePermissionToDownload($user)) {
    downloadClient();
} else {
    header('HTTP/1.1 403 Forbidden');
    echo 'You are not allowed to download this Cheat.';
}

?>
