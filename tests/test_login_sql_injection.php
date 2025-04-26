<?php
// SQL Injection Test for login.php

$testCases = [
    "' OR '1'='1" => "Basic OR injection",
    "admin' --" => "Comment injection",
    "' OR 1=1 --" => "OR injection with comment",
    "' OR '1'='1' #" => "Hash comment injection",
    "' OR \"1\"=\"1" => "Double quote injection",
    "' OR 1=1 LIMIT 1; --" => "LIMIT injection",
    "' UNION SELECT 1, 'admin', 'password', 'admin'; --" => "UNION injection",
    "' OR username IS NOT NULL OR username = '" => "IS NOT NULL injection",
    
    "'; DROP TABLE users; --" => "Destructive query attempt",
    "' OR 1=1; INSERT INTO users (username,password) VALUES ('hacker','password'); --" => "Data insertion attempt",
    "' OR sleep(5) --" => "Time-based blind injection",
    "admin@example.com' AND (SELECT 1 FROM (SELECT COUNT(*),CONCAT(VERSION(),FLOOR(RAND(0)*2))x FROM information_schema.TABLES GROUP BY x)a) --" => "Error-based injection"
];

function testLoginEndpoint($email, $password, $attackType) {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, "http://localhost/know-way/controller/login.php");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'email' => $email,
        'password' => $password
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headers = curl_getinfo($ch, CURLINFO_HEADER_OUT);
    
    $redirectSuccess = false;
    if (strpos($response, 'Location: ../view/dashboard.php') !== false || 
        strpos($response, 'Location: ../view/admin.php') !== false) {
        $redirectSuccess = true;
    }
    
    curl_close($ch);
    
    return [
        'attack_type' => $attackType,
        'email_payload' => $email,
        'password_payload' => $password,
        'http_code' => $httpCode,
        'success' => $redirectSuccess,
        'vulnerable' => $redirectSuccess 
    ];
}

echo "==============================================\n";
echo "SQL INJECTION TEST FOR LOGIN ENDPOINT\n";
echo "==============================================\n\n";

$testEmail = "user@example.com";

$vulnerableCount = 0;
foreach ($testCases as $payload => $description) {
    echo "Testing: $description\n";
    echo "Payload: $payload\n";
    
    $result1 = testLoginEndpoint($testEmail . $payload, "anything", "Email field: " . $description);
    
    $result2 = testLoginEndpoint($testEmail, $payload, "Password field: " . $description);
    
    if ($result1['vulnerable']) {
        echo "✘ VULNERABLE! Injection in email field successful\n";
        $vulnerableCount++;
    } else {
        echo "✓ Email field passed\n";
    }
    
    if ($result2['vulnerable']) {
        echo "✘ VULNERABLE! Injection in password field successful\n";
        $vulnerableCount++;
    } else {
        echo "✓ Password field passed\n";
    }
    
    echo "----------------------------------------------\n";
}

// Summary
echo "\n==============================================\n";
echo "TEST SUMMARY\n";
echo "==============================================\n";
if ($vulnerableCount > 0) {
    echo "RESULT: ✘ VULNERABLE - $vulnerableCount injection attempts succeeded\n";
    echo "The login endpoint appears to be vulnerable to SQL injection attacks.\n";
} else {
    echo "RESULT: ✓ SECURE - All injection attempts failed\n";
    echo "The login endpoint appears to be secure against SQL injection attacks.\n";
}
echo "==============================================\n"; 