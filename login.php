<?php
ini_set('log_errors', 1);
ini_set('error_log', '/home/bohaowu2/logs/php_errors.log');

require 'vendor/autoload.php';
use \Firebase\JWT\JWT;

function getRedirEnv($var)
{
    if (getenv($var)) return getenv($var);
    if (getenv('REDIRECT_' . $var)) return getenv('REDIRECT_' . $var);
    // httpd can rewrite vars on redirects, this is the most common case
    $var = preg_replace('/-/', '_', $var);
    if (getenv($var)) return getenv($var);
    if (getenv('REDIRECT_' . $var)) return getenv('REDIRECT_' . $var);
    return FALSE;
}

// Extract "env" query parameter
$env = isset($_GET['env']) ? $_GET['env'] : 'development';

// Determine callback URL based on "env"
$base_url = '';
switch ($env) {
    case 'staging':
        $base_url = 'https://nretm44tpb.us-east-2.awsapprunner.com';
        break;
    case 'production':
        $base_url = 'https://bvfyfpramn.us-east-2.awsapprunner.com';
        break;
    case 'development':
    default:
        $base_url = 'http://localhost:3001';
        break;
}

$memberString = getRedirEnv('member');
$uin = getRedirEnv('iTrustUIN');
$email = getRedirEnv('eppn');
$netID = explode("@", $email)[0];

// Check if "uiuc_allstaff" is a substring of $memberString
$isStaff = strpos($memberString, 'uiuc_allstaff') !== false;

// Create the token (use a strong secret key)
$jwt_secret = 'you_jwt_secret';
$payload = [
    'netId' => $netID,
    'uin' => $uin,
    'isStaff' => $isStaff,
    'exp' => time() + 600
];
$jwt = JWT::encode($payload, $jwt_secret, 'HS256');

// Redirect the user to the appropriate application, passing the JWT
$redirect_url = $base_url . '/api/login_callback/shibboleth?token=' . urlencode($jwt);
header('Location: ' . $redirect_url);
exit();
?>

