<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Iberbanco\SDK\IberbancoClient;
use Iberbanco\SDK\Exceptions\ApiException;
use Iberbanco\SDK\Exceptions\AuthenticationException;

// Create SDK client
$client = IberbancoClient::create([
    'base_url' => 'https://sandbox.api.iberbanco.finance/v2', // Use sandbox for testing
    'username' => 'your_agent_username', // Replace with your agent username
    'timeout' => 30,
    'verify_ssl' => true,
    'debug' => false // Set to true for debugging
]);

try {
    // 1. Authenticate
    echo "🔐 Authenticating...\n";
    $authResponse = $client->authenticate('your_agent_username', 'your_password');
    echo "✅ Authentication successful! Token: " . substr($client->getAuthToken(), 0, 10) . "...\n\n";

    // 2. List users
    echo "👥 Fetching users...\n";
    $users = $client->users()->list(['per_page' => 5]);
    echo "📊 Found " . count($users['data'] ?? []) . " users\n\n";

    // 3. List accounts
    echo "🏦 Fetching accounts...\n";
    $accounts = $client->accounts()->list(['per_page' => 5]);
    echo "📊 Found " . count($accounts['data'] ?? []) . " accounts\n\n";

    // 4. Get exchange rates
    echo "💱 Fetching exchange rates...\n";
    $exchangeRate = $client->exchange()->getConversion('USD', 'EUR', 100.00);
    echo "💰 100 USD = " . ($exchangeRate['data']['converted_amount'] ?? 'N/A') . " EUR\n\n";

    // 5. Create a personal user (example)
    echo "👤 Creating a new personal user...\n";
    $newUser = $client->users()->registerPersonal([
        'email' => 'john.doe.test+' . time() . '@example.com',
        'password' => 'SecurePassword123!',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'date_of_birth' => '1990-01-15',
        'country' => 'US',
        'citizenship' => 'US',
        'address' => '123 Main Street',
        'city' => 'New York',
        'post_code' => '10001',
        'call_number' => '+1234567890'
    ]);
    echo "✅ User created with number: " . ($newUser['data']['user_number'] ?? 'N/A') . "\n\n";

    // 6. Create account for the user
    echo "🏦 Creating account for user...\n";
    $newAccount = $client->accounts()->create([
        'user_number' => $newUser['data']['user_number'],
        'currency' => 840, // USD
        'reference' => 'Primary USD Account'
    ]);
    echo "✅ Account created: " . ($newAccount['data']['account_number'] ?? 'N/A') . "\n\n";

    // 7. Export users data
    echo "📤 Starting users export...\n";
    $exportJob = $client->export()->users([
        'format' => 'csv',
        'date_from' => date('Y-m-d', strtotime('-30 days')),
        'date_to' => date('Y-m-d'),
        'limit' => 1000
    ]);
    echo "✅ Export job started: " . ($exportJob['data']['job_id'] ?? 'N/A') . "\n\n";

    echo "🎉 Demo completed successfully!\n";

} catch (AuthenticationException $e) {
    echo "❌ Authentication failed: " . $e->getMessage() . "\n";
    echo "💡 Please check your username and password.\n";
} catch (ApiException $e) {
    echo "❌ API Error: " . $e->getMessage() . "\n";
    echo "🔍 HTTP Status: " . $e->getHttpStatusCode() . "\n";
    if ($e->getErrors()) {
        echo "📝 Details: " . implode(', ', $e->getErrors()) . "\n";
    }
} catch (Exception $e) {
    echo "❌ Unexpected error: " . $e->getMessage() . "\n";
} 