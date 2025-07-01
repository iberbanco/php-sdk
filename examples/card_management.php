<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Iberbanco\SDK\IberbancoClient;
use Iberbanco\SDK\Exceptions\ApiException;

// Initialize client
$client = IberbancoClient::create([
    'base_url' => 'https://sandbox.api.iberbanco.finance/v2',
    'username' => 'your_agent_username',
    'verify_ssl' => true
]);

try {
    // Authenticate
    echo "🔐 Authenticating...\n";
    $client->authenticate('your_agent_username', 'your_password');
    echo "✅ Authenticated successfully!\n\n";

    // 1. List all cards
    echo "💳 Fetching cards...\n";
    $cards = $client->cards()->list([
        'per_page' => 10,
        'visibility' => 'active'
    ]);
    
    echo "📊 Found " . count($cards['data'] ?? []) . " active cards\n\n";

    // 2. Create a virtual card
    echo "🆕 Creating virtual card...\n";
    $virtualCard = $client->cards()->create([
        'user_number' => 'USER123456', // Replace with actual user number
        'account_number' => 'ACC1234567890', // Replace with actual account number
        'card_type' => 'virtual',
        'currency' => 840, // USD
        'daily_limit' => 1000.00,
        'monthly_limit' => 5000.00
    ]);
    
    echo "✅ Virtual card created with ID: " . ($virtualCard['data']['card_id'] ?? 'N/A') . "\n\n";

    // 3. Get card details
    if (isset($virtualCard['data']['remote_id'])) {
        echo "📄 Fetching card details...\n";
        $cardDetails = $client->cards()->show($virtualCard['data']['remote_id']);
        echo "💳 Card status: " . ($cardDetails['data']['status'] ?? 'Unknown') . "\n";
        echo "💰 Daily limit: $" . ($cardDetails['data']['daily_limit'] ?? 'N/A') . "\n\n";
    }

    // 4. Get card transactions
    echo "📋 Fetching card transactions...\n";
    $cardTransactions = $client->cards()->transactions([
        'card_id' => $virtualCard['data']['card_id'] ?? 'CARD123',
        'per_page' => 10,
        'date_from' => date('Y-m-d', strtotime('-30 days'))
    ]);
    
    echo "📊 Found " . count($cardTransactions['data'] ?? []) . " card transactions\n\n";

    // 5. Request physical card
    echo "📮 Requesting physical card...\n";
    $physicalCardRequest = $client->cards()->requestPhysical([
        'card_id' => $virtualCard['data']['card_id'] ?? 'CARD123',
        'delivery_address' => [
            'street' => '123 Main Street, Apt 4B',
            'city' => 'New York',
            'country' => 'US',
            'postal_code' => '10001',
            'state' => 'NY'
        ],
        'express_delivery' => true
    ]);
    
    echo "✅ Physical card requested: " . ($physicalCardRequest['data']['request_id'] ?? 'N/A') . "\n\n";

    // 6. Create another card with different settings
    echo "💳 Creating EUR card...\n";
    $eurCard = $client->cards()->create([
        'user_number' => 'USER123456', // Replace with actual user number
        'account_number' => 'ACC1234567891', // Replace with actual EUR account number
        'card_type' => 'virtual',
        'currency' => 978, // EUR
        'daily_limit' => 800.00,
        'monthly_limit' => 4000.00
    ]);
    
    echo "✅ EUR card created with ID: " . ($eurCard['data']['card_id'] ?? 'N/A') . "\n\n";

    // 7. List cards with filters
    echo "🔍 Searching for USD cards...\n";
    $usdCards = $client->cards()->list([
        'currency' => 840, // USD
        'status' => 'ACTIVE',
        'per_page' => 5
    ]);
    
    echo "💰 Found " . count($usdCards['data'] ?? []) . " active USD cards\n\n";

    // 8. Get supported card types and currencies
    echo "📝 Available card options:\n";
    
    echo "Card Types:\n";
    $cardTypes = $client->cards()->getSupportedCardTypes();
    foreach ($cardTypes as $type => $name) {
        echo "  • {$type}: {$name}\n";
    }
    
    echo "\nSupported Currencies:\n";
    $currencies = $client->cards()->getSupportedCurrencies();
    foreach ($currencies as $code => $name) {
        echo "  • {$code}: {$name}\n";
    }
    
    echo "\nCard Statuses:\n";
    $statuses = $client->cards()->getSupportedStatuses();
    foreach ($statuses as $status => $name) {
        echo "  • {$status}: {$name}\n";
    }
    echo "\n";

    // 9. Export card data
    echo "📤 Starting card export...\n";
    $cardExport = $client->export()->cards([
        'format' => 'csv',
        'columns' => ['card_id', 'user_number', 'card_type', 'status', 'currency', 'created_at'],
        'date_from' => date('Y-m-d', strtotime('-90 days')),
        'compressed' => true
    ]);
    
    echo "✅ Card export job started: " . ($cardExport['data']['job_id'] ?? 'N/A') . "\n\n";

    echo "🎉 Card management demo completed!\n";

} catch (ApiException $e) {
    echo "❌ API Error: " . $e->getMessage() . "\n";
    echo "🔍 HTTP Status: " . $e->getHttpStatusCode() . "\n";
    if ($e->getErrors()) {
        echo "📝 Error details: " . implode(', ', $e->getErrors()) . "\n";
    }
} catch (Exception $e) {
    echo "❌ Unexpected error: " . $e->getMessage() . "\n";
} 