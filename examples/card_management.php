<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Iberbanco\SDK\IberbancoClient;
use Iberbanco\SDK\Exceptions\ApiException;

// Initialize client
$client = IberbancoClient::create([
    'sandbox' => true, // Set to false for production
    'username' => $_ENV['IBERBANCO_USERNAME'] ?? 'your_agent_username',
    'verify_ssl' => true,
    'timeout' => 30,
    'debug' => false
]);

try {
    // Authenticate
    echo "🔐 Authenticating...\n";
    $client->authenticate(
        $_ENV['IBERBANCO_USERNAME'] ?? 'your_agent_username', 
        $_ENV['IBERBANCO_PASSWORD'] ?? 'your_password'
    );
    echo "✅ Authenticated successfully!\n\n";

    // 1. List all cards
    echo "💳 Fetching cards...\n";
    $cards = $client->cards()->list([
        'per_page' => 10,
        'visibility' => 'active'
    ]);
    
    echo "📊 Found " . count($cards['data'] ?? []) . " active cards\n\n";

    // 2. Create a card
    echo "🆕 Creating card...\n";
    $newCard = $client->cards()->create([
        'user_number' => 'USER123456', // Replace with actual user number
        'account_number' => 'ACC1234567890', // Replace with actual account number
        'amount' => 100.00, // Initial card amount (1-5000)
        'currency' => 1, // USD (1) or EUR (2) only
        'shipping_address' => '123 Main Street, Apt 4B',
        'shipping_city' => 'New York',
        'shipping_state' => 'NY',
        'shipping_country_code' => 'US', // 2-letter country code
        'shipping_post_code' => '10001',
        'delivery_method' => 'Standard', // 'Standard' or 'Registered'
        'product_type' => null // Optional
    ]);
    
    echo "✅ Card created with ID: " . ($newCard['data']['card_id'] ?? 'N/A') . "\n\n";

    // 3. Get card details
    if (isset($newCard['data']['remote_id'])) {
        echo "📄 Fetching card details...\n";
        $cardDetails = $client->cards()->show($newCard['data']['remote_id']);
        echo "💳 Card status: " . ($cardDetails['data']['status'] ?? 'Unknown') . "\n";
        echo "💰 Card amount: $" . ($cardDetails['data']['amount'] ?? 'N/A') . "\n\n";
    }

    // 4. Get card transactions
    echo "📋 Fetching card transactions...\n";
    $cardTransactions = $client->cards()->transactions([
        'remote_id' => $newCard['data']['remote_id'] ?? 'CARD123',
        'userNumber' => 'USER123456',
        'san' => $newCard['data']['san'] ?? 'SAN123',
        'year' => (int)date('Y'),
        'month' => (int)date('m')
    ]);
    
    echo "📊 Found " . count($cardTransactions['data'] ?? []) . " card transactions\n\n";

    // 5. Request physical card
    echo "📮 Requesting physical card...\n";
    $physicalCardRequest = $client->cards()->requestPhysical([
        'remote_id' => $newCard['data']['remote_id'] ?? 'CARD123'
    ]);
    
    echo "✅ Physical card requested: " . ($physicalCardRequest['data']['request_id'] ?? 'N/A') . "\n\n";

    // 6. Create EUR card
    echo "💳 Creating EUR card...\n";
    $eurCard = $client->cards()->create([
        'user_number' => 'USER123456', // Replace with actual user number
        'account_number' => 'ACC1234567891', // Replace with actual EUR account number
        'amount' => 200.00, // Initial card amount
        'currency' => 2, // EUR (internal ID)
        'shipping_address' => '456 European Street',
        'shipping_city' => 'Berlin',
        'shipping_state' => 'Berlin',
        'shipping_country_code' => 'DE',
        'shipping_post_code' => '10115',
        'delivery_method' => 'Registered' // Express delivery
    ]);
    
    echo "✅ EUR card created with ID: " . ($eurCard['data']['card_id'] ?? 'N/A') . "\n\n";

    // 7. List cards with filters
    echo "🔍 Searching for USD cards...\n";
    $usdCards = $client->cards()->list([
        'currency' => 1, // USD (internal ID)
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