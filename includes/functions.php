<?php
// user_helper_functions.php

function auth_toke_exchage($token) {   
    $base_url = rtrim(AUTH_SERVER_URL, '/') . '/api/v1/';
    $url = $base_url . '?type=exchange_token';
    
    $payload = json_encode([
        'token' => $token,
        'client_id' => AUTH_CLIENT_ID,
        'client_secret' => AUTH_CLIENT_SECRET
    ]);
    
    $headers = [
        'Content-Type: application/json',
        'User-Agent: ' . AUTH_USER_AGENT,
        'Accept: application/json'
    ];
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $result = curl_exec($ch);
    curl_close($ch);
    
    if (!$result) {
        return ['success' => false, 'message' => 'Could not connect to the authentication server.'];
    }
    
    $data = json_decode($result, true);

    // FIX: Return the specific error message from the auth server
    if (!isset($data['success']) || !$data['success']) {
        $errorMessage = $data['message'] ?? 'An unknown authentication error occurred.';
        return ['success' => false, 'message' => $errorMessage];
    }
    
    return ['success' => true, 'data' => $data['data']];
}

function auth_server_logout($user_id, $session_token) {
    // Construct URL as per API documentation
    $base_url = rtrim(AUTH_SERVER_URL, '/') . '/api/v1/';
    $url = $base_url . '?type=logout';
    
    // Construct payload as per API documentation
    $payload = json_encode([
        'client_id' => AUTH_CLIENT_ID,
        'client_secret' => AUTH_CLIENT_SECRET
    ], JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION);
    
    $headers = [
        'Content-Type: application/json',
        'User-Agent: ' . AUTH_USER_AGENT,
        'Accept: application/json',
        'Authorization: Bearer ' . $session_token,
        'X-User-ID: ' . $user_id
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $result = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    
    if (!$result) {
        return [
            'success' => false,
            'error_code' => 'SERVER_ERROR',
            'message' => 'An error occurred on the server. Please try again later.'
        ];
    }
    
    $data = json_decode($result, true);
      if (!isset($data['success']) || !$data['success']) {
        // Handle specific error cases
        if (isset($data['error_code'])) {
            if ($data['error_code'] === 'INVALID_TOKEN') {
                return [
                    'success' => false,
                    'error_code' => 'INVALID_TOKEN',
                    'message' => 'Invalid or expired session token'
                ];
            }
            // Check for account deactivation message
            if (isset($data['message']) && $data['message'] === 'ACCOUNT_DEACTIVE') {
                return [
                    'success' => false,
                    'error_code' => 'ACCOUNT_DEACTIVE',
                    'message' => 'Your account has been deactivated by auth server. Please contact support.'
                ];
            }
        }
        
        // Log unknown errors and return generic error
        error_log("Auth server unknown logout error: HTTP $http_code - Response: $result");
        return [
            'success' => false,
            'error_code' => 'SERVER_ERROR',
            'message' => 'An error occurred on the server. Please try again later.'
        ];
    }
    
    return ['success' => true];
}

function auth_server_refresh($user_id, $session_token, $refresh_token) {
    // Construct URL as per API documentation
    $base_url = rtrim(AUTH_SERVER_URL, '/') . '/api/v1/';
    $url = $base_url . '?type=refresh';
    
    // Construct payload as per API documentation
    $payload = json_encode([
        'refresh_token' => $refresh_token,
        'old_session_token' => $session_token,
        'client_id' => AUTH_CLIENT_ID,
        'client_secret' => AUTH_CLIENT_SECRET
    ], JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION);
    
    $headers = [
        'Content-Type: application/json',
        'User-Agent: ' . AUTH_USER_AGENT,
        'Accept: application/json',
        'Authorization: Bearer ' . $session_token,
        'X-User-ID: ' . $user_id
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $result = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if (!$result) {
        return [
            'success' => false,
            'error_code' => 'SERVER_ERROR',
            'message' => 'An error occurred on the server. Please try again later.'
        ];
    }
    
    $data = json_decode($result, true);
      if (!isset($data['success']) || !$data['success']) {
        // Handle specific error cases
        if (isset($data['error_code'])) {
            if ($data['error_code'] === 'INVALID_TOKEN') {
                return [
                    'success' => false,
                    'error_code' => 'INVALID_TOKEN',
                    'message' => 'Invalid or expired refresh token'
                ];
            }
            // Check for account deactivation message
            if (isset($data['message']) && $data['message'] === 'ACCOUNT_DEACTIVE') {
                return [
                    'success' => false,
                    'error_code' => 'ACCOUNT_DEACTIVE',
                    'message' => 'Your account has been deactivated by auth server. Please contact support.'
                ];
            }
        }
        
        // Log unknown errors and return generic error
        error_log("Auth server unknown refresh error: HTTP $http_code - Response: $result");
        return [
            'success' => false,
            'error_code' => 'SERVER_ERROR',
            'message' => 'An error occurred on the server. Please try again later.'
        ];
    }
    
    return ['success' => true, 'data' => $data['data']];
}

function auth_server_validate($user_id, $session_token) {
    // Construct URL as per API documentation
    $base_url = rtrim(AUTH_SERVER_URL, '/') . '/api/v1/';
    $url = $base_url . '?type=validate';
    
    // Construct payload as per API documentation
    $payload = json_encode([
        'client_id' => AUTH_CLIENT_ID,
        'client_secret' => AUTH_CLIENT_SECRET
    ], JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION);
    
    $headers = [
        'Content-Type: application/json',
        'User-Agent: ' . AUTH_USER_AGENT,
        'Accept: application/json',
        'Authorization: Bearer ' . $session_token,
        'X-User-ID: ' . $user_id
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $result = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
   
    if (!$result) {
        return [
            'success' => false,
            'error_code' => 'SERVER_ERROR',
            'message' => 'An error occurred on the server. Please try again later.'
        ];
    }
    
    $data = json_decode($result, true);
      if (!isset($data['success']) || !$data['success']) {
        // Handle specific error cases
        if (isset($data['error_code'])) {
            if ($data['error_code'] === 'INVALID_TOKEN') {
                return [
                    'success' => false,
                    'error_code' => 'INVALID_TOKEN',
                    'message' => 'Invalid or expired session token'
                ];
            }
            // Check for account deactivation message
            if (isset($data['message']) && $data['message'] === 'ACCOUNT_DEACTIVE') {
                return [
                    'success' => false,
                    'error_code' => 'ACCOUNT_DEACTIVE',
                    'message' => 'Your account has been deactivated by auth server. Please contact support.'
                ];
            }
        }
        
        // Log unknown errors and return generic error
        error_log("Auth server unknown validate error: HTTP $http_code - Response: $result");
        return [
            'success' => false,
            'error_code' => 'SERVER_ERROR',
            'message' => 'An error occurred on the server. Please try again later.'
        ];
    }
    
    return ['success' => true, 'data' => $data['data']];
}


function auth_server_profile($user_id, $session_token) {
    // Construct URL as per API documentation
    $base_url = rtrim(AUTH_SERVER_URL, '/') . '/api/v1/';
    $url = $base_url . '?type=profile';
    
    // Construct payload as per API documentation
    $payload = json_encode([
        'client_id' => AUTH_CLIENT_ID,
        'client_secret' => AUTH_CLIENT_SECRET
    ], JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION);
    
    $headers = [
        'Content-Type: application/json',
        'User-Agent: ' . AUTH_USER_AGENT,
        'Accept: application/json',
        'Authorization: Bearer ' . $session_token,
        'X-User-ID: ' . $user_id
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $result = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    
    if (!$result) {
        return [
            'success' => false,
            'error_code' => 'SERVER_ERROR',
            'message' => 'An error occurred on the server. Please try again later.'
        ];
    }
    
    $data = json_decode($result, true);
    
    if (!isset($data['success']) || !$data['success']) {
        // Handle specific error cases
        if (isset($data['error_code'])) {
            if ($data['error_code'] === 'INVALID_TOKEN') {
                return [
                    'success' => false,
                    'error_code' => 'INVALID_TOKEN',
                    'message' => 'Invalid or expired session token'
                ];
            }
            // Check for account deactivation message
            if (isset($data['message']) && $data['message'] === 'ACCOUNT_DEACTIVE') {
                return [
                    'success' => false,
                    'error_code' => 'ACCOUNT_DEACTIVE',
                    'message' => 'Your account has been deactivated by auth server. Please contact support.'
                ];
            }
        }
        
        // Log unknown errors and return generic error
        error_log("Auth server unknown profile error: HTTP $http_code - Response: $result");
        return [
            'success' => false,
            'error_code' => 'SERVER_ERROR',
            'message' => 'An error occurred on the server. Please try again later.'
        ];
    }
    
    return ['success' => true, 'data' => $data['data']];
}
