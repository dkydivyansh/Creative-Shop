<?php
// Environment settings
// In production, you would set this to 'production'
define('ENVIRONMENT', 'development');
define('siteDomain', 'shop.dkydivyansh.com');
// Error reporting
if (ENVIRONMENT === 'development') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}

// Database settings
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_db_name');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_password');
define('DB_CHARSET', 'utf8mb4');

// SSO server settings (OAuth2 Authorization Code Grant)
define('SSO_BASE_URL', 'https://sso.dkydivyansh.com');
define('SSO_CLIENT_ID', 'your_sso_client_id');
define('SSO_CLIENT_SECRET', 'your_sso_client_secret');
define('SSO_SCOPE', 'basic');                  // 'basic', 'internal', or 'extended'
define('SSO_CALLBACK_URL', 'https://' . siteDomain . '/auth/callback');
define('CSC_API_KEY', 'your_csc_api_key');

// API Authentication settings
define('API_USER_AGENT', 'AuthSystem-1.0');
define('API_ACCESS_TOKEN', 'your_api_access_token');
define('API_ORIGIN', 'https://shop.dkydivyansh.com');
define('EMAIL_API_URL', 'https://dkydivyansh.com/Project/api/maler');
define('EMAIL_FROM_NAME', 'Shop - Dkydivyansh.com');

define('TAX_PERCENT', 18); // Tax percentage
define('TRANSACTION_FEE_PERCENT', 2); // Transaction fee percentage
define('SERVICE_MAINTENANCE_PERCENT', 1); // Service maintenance fee percentage

define('GOOGLE_PAY_ENVIRONMENT', 'PRODUCTION');

// Get this from your Google Pay Business Console
define('GOOGLE_PAY_MERCHANT_ID', 'your_google_pay_merchant_id');
define('GOOGLE_PAY_MERCHANT_NAME', 'Your Merchant Name');

// --- Razorpay Specific Settings ---
define('PAYMENT_GATEWAY_NAME', 'razorpay');

// This is the special Merchant ID for Google Pay provided BY RAZORPAY
define('PAYMENT_GATEWAY_MERCHANT_ID', 'your_razorpay_google_pay_merchant_id');

// --- Razorpay API Keys ---
define('RAZORPAY_KEY_ID', 'your_razorpay_key_id');
define('RAZORPAY_KEY_SECRET', 'your_razorpay_key_secret');