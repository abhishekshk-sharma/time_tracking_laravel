<?php

// Simple VAPID key generator
function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

// Generate keys
$keys = openssl_pkey_new([
    'curve_name' => 'prime256v1',
    'private_key_type' => OPENSSL_KEYTYPE_EC,
]);

if (!$keys) {
    die("Error: Could not generate keys. OpenSSL error: " . openssl_error_string() . "\n");
}

$details = openssl_pkey_get_details($keys);

$publicKey = base64url_encode($details['ec']['x'] . $details['ec']['y']);
$privateKey = base64url_encode($details['ec']['d']);

echo "\n========================================\n";
echo "VAPID Keys Generated Successfully!\n";
echo "========================================\n\n";

echo "Add these to your .env file:\n\n";

echo "VAPID_PUBLIC_KEY=\"{$publicKey}\"\n";
echo "VAPID_PRIVATE_KEY=\"{$privateKey}\"\n";
echo "VAPID_SUBJECT=\"mailto:your-email@example.com\"\n\n";

echo "WEBPUSH_VAPID_PUBLIC_KEY=\${VAPID_PUBLIC_KEY}\n";
echo "WEBPUSH_VAPID_PRIVATE_KEY=\${VAPID_PRIVATE_KEY}\n";
echo "WEBPUSH_VAPID_SUBJECT=\${VAPID_SUBJECT}\n\n";

echo "========================================\n";
echo "After updating .env, run:\n";
echo "php artisan config:clear\n";
echo "========================================\n\n";
