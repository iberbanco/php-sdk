<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Iberbanco\SDK\IberbancoClient;
use Iberbanco\SDK\Exceptions\ApiException;
use Iberbanco\SDK\Exceptions\AuthenticationException;


// Initialize client
$client = IberbancoClient::create([
    'sandbox' => true, // Set to false for production
    'username' => $_ENV['IBERBANCO_USERNAME'] ?? 'your_agent_username',
    'verify_ssl' => true,
    'timeout' => 30,
    'debug' => false
]);

try {
    // 1. Authenticate
    echo "🔐 Authenticating...\n";
    $client->authenticate(
        $_ENV['IBERBANCO_USERNAME'] ?? 'your_agent_username', 
        $_ENV['IBERBANCO_PASSWORD'] ?? 'your_password'
    );
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

    // 5. Note about User Creation
    echo "👤 Note: User creation requires file uploads and complex validation.\n";
    echo "📝 For user creation examples, see dedicated user management examples.\n";
    echo "🔍 Working with existing users instead...\n\n";
    
    // For this example, we'll work with existing users
    $existingUsers = $client->users()->list(['per_page' => 1]);
    if (!empty($existingUsers['data'])) {
        $firstUser = $existingUsers['data'][0];
        echo "✅ Using existing user: " . $firstUser['user_number'] . "\n\n";
        
        // Create account for existing user
        echo "🏦 Creating account for existing user...\n";
        try {
            $newAccount = $client->accounts()->create([
                'user_number' => $firstUser['user_number'],
                'currency' => 2, // EUR (internal ID) - trying different currency as user might already have USD
                'reference' => 'Demo EUR Account via SDK'
            ]);
            echo "✅ Account created: " . ($newAccount['data']['account_number'] ?? 'N/A') . "\n\n";
        } catch (\Exception $e) {
            echo "❌ Account creation failed: " . $e->getMessage() . "\n";
            echo "💡 Note: User might already have accounts in available currencies.\n\n";
        }
    } else {
        echo "⚠️  No existing users found. Please create a user first.\n\n";
    }

    // 6. Export users data
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