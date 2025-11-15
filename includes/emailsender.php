<?php
/**
 * Email Sender Class
 * 
 * Handles sending emails through the mail API
 */

class EmailSender {
    private string $apiUrl = EMAIL_API_URL . '/send_email.php';
    private string $accessToken = API_ACCESS_TOKEN;
    private string $userAgent = API_USER_AGENT;
    private string $origin = API_ORIGIN;
    private string $logFile = '/email_errors.log';
    private array $lastError = [];
    /**
     * Constructor - ensures log directory exists and sets logger if provided
     *
     * @param Logger|null $logger Optional Logger instance for centralized logging
     */

    /**
     * Send an email through the API
     *
     * @param string $to Recipient email address
     * @param string $subject Email subject
     * @param string $body Email body content (HTML supported)
     * @param string $fromName Custom sender name (optional)
     * @param bool $debug Whether this is a debug email to admin (default: false)
     * @return array Response data with success/error information
     */
    public function sendEmail(string $to, string $subject, string $body, string $fromName = '', bool $debug = false): array
    {
        // Reset last error
        $this->lastError = [];
        
        try {
            // Validate email address
            if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Invalid email address: {$to}");
            }
            
            // Prepare the request data
            $data = [
                'to' => $to,
                'subject' => $subject,
                'body' => $body,
                'debug' => $debug
            ];
            
            // Add from_name if provided
            if (!empty($fromName)) {
                $data['from_name'] = $fromName;
            }
            
            // Prepare the cURL request
            $ch = curl_init($this->apiUrl);
            
            if ($ch === false) {
                throw new Exception("Failed to initialize cURL");
            }
            
            // Set cURL options
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Access-Token: ' . $this->accessToken,
                'User-Agent: ' . $this->userAgent,
                'Origin: ' . $this->origin
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Add timeout to avoid hanging
            
            // Execute the request
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            $errorNo = curl_errno($ch);
            
            curl_close($ch);
            
            // Process the response
            if ($response === false) {
                throw new Exception("cURL error ({$errorNo}): {$error}");
            }
            
            // Decode the JSON response
            $responseData = json_decode($response, true);
            
            // If the response couldn't be decoded, throw an exception
            if ($responseData === null) {
                throw new Exception("Invalid JSON response from email API: {$response}");
            }
            
            // If API returned an error
            if (!isset($responseData['success']) || $responseData['success'] !== true) {
                $errorMsg = $responseData['message'] ?? 'Unknown API error';
                throw new Exception("API error: {$errorMsg}", $httpCode);
            }
            
            // Return the API response
            return $responseData;
        }
        catch (Exception $e) {
            // Prepare error data
            $this->lastError = [
                'success' => false,
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'time' => date('Y-m-d H:i:s'),
                'email_to' => $to,
                'email_subject' => $subject,
                'http_code' => $httpCode ?? 0
            ];
            
            // Log the error
            $this->logError($this->lastError);
            
            // Return error response
            return $this->lastError;
        }
    }
    
    /**
     * Log an error using Logger if available, fallback to file logging
     *
     * @param array $error The error details to log
     * @return bool Whether logging was successful
     */
    private function logError(array $error): bool
    {

        try {
            // Create log entry
            $logEntry = sprintf(
                "[%s] ERROR: %s (Code: %s, HTTP: %s, To: %s, Subject: %s)\n",
                $error['time'],
                $error['message'],
                $error['code'],
                $error['http_code'],
                $error['email_to'],
                $error['email_subject']
            );
            
            // Write to log file
            return file_put_contents($this->logFile, $logEntry, FILE_APPEND) !== false;
        } catch (Exception $e) {
            // If logging fails, use PHP's error_log as fallback
            error_log("Failed to log email error: " . $e->getMessage());
            error_log("Original error: " . $error['message']);
            return false;
        }
    }
    
    /**
     * Get detailed information about the last error
     *
     * @return array The last error information
     */
    public function getLastError(): array
    {
        return $this->lastError;
    }
    
    /**
     * Get a user-friendly error message for display
     *
     * @return string A user-friendly error message
     */
    public function getErrorMessage(): string
    {
        if (empty($this->lastError)) {
            return "No errors occurred";
        }
        
        $errorCode = $this->lastError['http_code'] ?? $this->lastError['code'] ?? 0;
        $errorMsg = $this->lastError['message'] ?? "Unknown error";
        
        // Create user-friendly messages based on error
        if (strpos($errorMsg, 'cURL error') !== false) {
            return "Failed to connect to the email server. Please try again later.";
        }
        
        if ($errorCode == 401 || $errorCode == 403) {
            return "Authentication failed with the email server. Please contact the administrator.";
        }
        
        if ($errorCode >= 500) {
            return "The email server is experiencing problems. Please try again later.";
        }
        
        if (strpos($errorMsg, 'Invalid email address') !== false) {
            return "The email address you provided is invalid. Please check it and try again.";
        }
        
        // Default fallback message
        return "Failed to send email. Please try again later.";
    }
    
    /**
     * Get technical error details (for admin display only)
     *
     * @return string Technical error details
     */
    public function getTechnicalErrorDetails(): string
    {
        if (empty($this->lastError)) {
            return "No errors occurred";
        }
        
        return sprintf(
            "Error: %s\nCode: %s\nHTTP Code: %s\nTime: %s\nSent to: %s\nSubject: %s", 
            $this->lastError['message'] ?? 'Unknown', 
            $this->lastError['code'] ?? 'N/A',
            $this->lastError['http_code'] ?? 'N/A', 
            $this->lastError['time'] ?? date('Y-m-d H:i:s'),
            $this->lastError['email_to'] ?? 'N/A', 
            $this->lastError['email_subject'] ?? 'N/A'
        );
    }
    
    /**
     * Set a Logger instance for centralized logging
     *
     * @param Logger $logger The Logger instance to use
     * @return void
     */
    /**
     * Send a verification email to a new user
     *
     * @param string $to User's email address
     * @param string $name User's name
     * @param string $verificationUrl URL for email verification
     * @return array Response data with success/error information
     */

}