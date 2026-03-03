<?php
// sso_helper_functions.php
// Functions for interacting with the Dkydivyansh.com SSO API (OAuth2)

/**
 * Exchanges a short-lived authorization code for a long-lived access token.
 * POST /api/token
 *
 * @param string $code The authorization code received from the SSO callback.
 * @return array ['success' => bool, 'data' => [...] | 'message' => string]
 *               On success, data contains: user_id, access_token, scope, expires_in
 */
function sso_exchange_code($code)
{
    $url = rtrim(SSO_BASE_URL, '/') . '/api/token';

    $payload = json_encode([
        'client_id' => SSO_CLIENT_ID,
        'client_secret' => SSO_CLIENT_SECRET,
        'code' => $code
    ]);

    $headers = [
        'Content-Type: application/json',
        'Accept: application/json'
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if (!$result) {
        return ['success' => false, 'message' => 'Could not connect to the SSO server.'];
    }

    $data = json_decode($result, true);

    if (!isset($data['success']) || !$data['success']) {
        $errorDesc = $data['error_description'] ?? $data['error'] ?? 'An unknown SSO error occurred.';
        return ['success' => false, 'message' => $errorDesc];
    }

    // Success response: { success, user_id, access_token, scope, expires_in }
    return [
        'success' => true,
        'data' => [
            'user_id' => $data['user_id'],
            'access_token' => $data['access_token'],
            'scope' => $data['scope'],
            'expires_in' => $data['expires_in']
        ]
    ];
}

/**
 * Retrieves the profile data of the authenticated user.
 * POST /api/userinfo
 *
 * @param string $accessToken The access token obtained from token exchange.
 * @return array ['success' => bool, 'data' => ['user' => [...]] | 'message' => string]
 */
function sso_get_userinfo($accessToken)
{
    $url = rtrim(SSO_BASE_URL, '/') . '/api/userinfo';

    $payload = json_encode([
        'client_id' => SSO_CLIENT_ID,
        'client_secret' => SSO_CLIENT_SECRET,
        'access_token' => $accessToken
    ]);

    $headers = [
        'Content-Type: application/json',
        'Accept: application/json'
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if (!$result) {
        return ['success' => false, 'message' => 'Could not connect to the SSO server.'];
    }

    $data = json_decode($result, true);

    if (!isset($data['success']) || !$data['success']) {
        $errorDesc = $data['error_description'] ?? $data['error'] ?? 'Failed to fetch user profile.';
        return ['success' => false, 'message' => $errorDesc];
    }

    // Success response: { success, user: { user_id, email, first_name, last_name, ... } }
    return ['success' => true, 'data' => $data];
}
