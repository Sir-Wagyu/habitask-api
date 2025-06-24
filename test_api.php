<?php

// Test API endpoints using curl
echo "=== HABITASK API TEST ===\n\n";

function testEndpoint($url, $method = 'GET', $data = null, $headers = [])
{
      $ch = curl_init();

      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_TIMEOUT, 30);
      curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge([
            'Content-Type: application/json',
            'Accept: application/json'
      ], $headers));

      if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data) {
                  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
      }

      $response = curl_exec($ch);
      $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      $error = curl_error($ch);

      curl_close($ch);

      return [
            'response' => $response,
            'http_code' => $httpCode,
            'error' => $error
      ];
}

// Start server check
echo "1. Checking if server is running...\n";
$baseUrl = 'http://127.0.0.1:8000';

// Test basic connection
$result = testEndpoint($baseUrl);
if ($result['error']) {
      echo "❌ Server not running. Please start with: php artisan serve\n";
      echo "Error: " . $result['error'] . "\n";
      exit(1);
}

echo "✅ Server is running!\n\n";

// Test API Registration
echo "2. Testing User Registration...\n";
$userData = [
      'name' => 'Test User',
      'email' => 'test' . time() . '@example.com',
      'password' => 'password123',
      'password_confirmation' => 'password123'
];

$result = testEndpoint($baseUrl . '/api/auth/register', 'POST', $userData);
echo "Status Code: " . $result['http_code'] . "\n";
echo "Response: " . $result['response'] . "\n\n";

if ($result['http_code'] === 201) {
      echo "✅ Registration successful!\n";
      $response = json_decode($result['response'], true);
      $token = $response['data']['token'] ?? null;

      if ($token) {
            echo "✅ Token received: " . substr($token, 0, 20) . "...\n\n";

            // Test authenticated endpoint
            echo "3. Testing Authenticated Endpoint (User Profile)...\n";
            $authResult = testEndpoint($baseUrl . '/api/auth/user', 'GET', null, [
                  'Authorization: Bearer ' . $token
            ]);

            echo "Status Code: " . $authResult['http_code'] . "\n";
            echo "Response: " . $authResult['response'] . "\n\n";

            if ($authResult['http_code'] === 200) {
                  echo "✅ Authentication working!\n";
            } else {
                  echo "❌ Authentication failed!\n";
            }
      }
} else {
      echo "❌ Registration failed!\n";
}

echo "\n=== API TEST COMPLETE ===\n";
