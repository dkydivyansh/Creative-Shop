<?php
// File: check.php

header('Content-Type: text/plain'); // Use plain text for clean output

echo "=========================================\n";
echo " Composer & Class Health Check Script 🩺 \n";
echo "=========================================\n\n";

// --- Check 1: Composer Autoloader ---
$autoloaderPath = __DIR__ . '/../others/vendor/autoload.php';

echo "1. Checking for Composer autoloader...\n";
echo "   - Looking for file: " . $autoloaderPath . "\n";

if (file_exists($autoloaderPath)) {
    echo "   - ✅ SUCCESS: Autoloader file found. Including it.\n\n";
    require_once $autoloaderPath;

    // --- Check 2: Specific Class (Razorpay API) ---
    $className = 'Razorpay\Api\Api';
    echo "2. Checking if a specific class exists...\n";
    echo "   - Looking for class: '" . $className . "'\n";
    
    if (class_exists($className)) {
        echo "   - ✅ SUCCESS: The class '" . $className . "' is available and ready to use.\n\n";
        echo "-----------------------------------------\n";
        echo "CONCLUSION: Your Composer setup is working perfectly!\n";
        echo "-----------------------------------------\n";
    } else {
        echo "   - ❌ ERROR: The class '" . $className . "' was NOT found.\n";
        echo "   - SUGGESTION: Run 'composer require razorpay/razorpay' in your terminal.\n";
    }

} else {
    echo "   - ❌ ERROR: Autoloader file NOT found.\n";
    echo "   - SUGGESTION: Make sure you are in your project's root directory and run 'composer install'.\n";
}