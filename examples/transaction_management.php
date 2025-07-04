<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Iberbanco\SDK\IberbancoClient;
use Iberbanco\SDK\Exceptions\ApiException;
use Iberbanco\SDK\Exceptions\ValidationException;

// Initialize client
$client = IberbancoClient::create([
    'sandbox' => true, // Set to false for production
    'username' => $_ENV['IBERBANCO_USERNAME'] ?? 'your_agent_username',
    'verify_ssl' => true,
    'timeout' => 30,
    'debug' => true // Enable debug mode for detailed logs
]);

try {
    // Authenticate
    echo "🔐 Authenticating...\n";
    $client->authenticate('your_agent_username', 'your_password');
    echo "✅ Authenticated successfully!\n\n";

    // 1. List recent transactions
    echo "📋 Fetching recent transactions...\n";
    $transactions = $client->transactions()->list([
        'per_page' => 10,
        'status' => 'COMPLETED',
        'date_from' => date('Y-m-d', strtotime('-7 days'))
    ]);
    
    echo "📊 Found " . count($transactions['data'] ?? []) . " completed transactions in the last 7 days\n\n";

    // 2. Search for specific transactions
    echo "🔍 Searching for high-value transactions...\n";
    $highValueTransactions = $client->transactions()->search([
        'min_amount' => 10000,
        'date_from' => date('Y-m-d', strtotime('-30 days')),
        'per_page' => 5
    ]);
    
    echo "💰 Found " . count($highValueTransactions['data'] ?? []) . " high-value transactions\n\n";

    // 3. Create a SEPA transaction
    echo "🌍 Creating SEPA transaction...\n";
    $sepaTransaction = $client->transactions()->createSepa([
        'account_number' => 'YOUR_ACCOUNT_NUMBER', // Replace with actual account number
        'amount' => 250.50,
        'currency' => 'EUR',
        'recipient_name' => 'John Doe',
        'recipient_iban' => 'DE89370400440532013000',
        'reference' => 'Invoice payment #12345',
        'description' => 'Payment for consulting services'
    ]);
    
    echo "✅ SEPA transaction created: " . ($sepaTransaction['data']['transaction_number'] ?? 'N/A') . "\n\n";

    // 4. Create a SWIFT transaction
    echo "🌐 Creating SWIFT transaction...\n";
    $swiftTransaction = $client->transactions()->createSwift([
        'account_number' => 'YOUR_ACCOUNT_NUMBER', // Replace with actual account number
        'amount' => 1500.00,
        'currency' => 'USD',
        'recipient_name' => 'Jane Smith',
        'recipient_account_number' => '1234567890',
        'recipient_bank_code' => 'CHASUS33',
        'recipient_address' => '456 Oak Avenue, Chicago, IL',
        'reference' => 'Contract payment',
        'description' => 'International wire transfer'
    ]);
    
    echo "✅ SWIFT transaction created: " . ($swiftTransaction['data']['transaction_number'] ?? 'N/A') . "\n\n";

    // 5. Get transaction details
    if (isset($sepaTransaction['data']['transaction_number'])) {
        echo "📄 Fetching transaction details...\n";
        $transactionDetails = $client->transactions()->show($sepaTransaction['data']['transaction_number']);
        echo "📋 Transaction status: " . ($transactionDetails['data']['status'] ?? 'Unknown') . "\n";
        echo "💵 Amount: " . ($transactionDetails['data']['amount'] ?? 'N/A') . " " . ($transactionDetails['data']['currency'] ?? '') . "\n\n";
    }

    // 6. List crypto transactions
    echo "₿ Fetching crypto transactions...\n";
    $cryptoTransactions = $client->cryptoTransactions()->list([
        'per_page' => 5,
        'cryptocurrency' => 'BTC'
    ]);
    
    echo "📊 Found " . count($cryptoTransactions['data'] ?? []) . " BTC transactions\n\n";

    // 7. Create crypto payment link
    echo "🔗 Creating crypto payment link...\n";
    $paymentLink = $client->cryptoTransactions()->createPaymentLink([
        'amount' => 100.00,
        'currency' => 'USD',
        'description' => 'Product purchase',
        'return_url' => 'https://yoursite.com/payment/success',
        'callback_url' => 'https://yoursite.com/webhooks/payment',
        'expires_at' => date('Y-m-d H:i:s', strtotime('+24 hours'))
    ]);
    
    echo "✅ Payment link created: " . ($paymentLink['data']['payment_url'] ?? 'N/A') . "\n\n";

    // 8. Get supported transaction types
    echo "📝 Available transaction types:\n";
    $transactionTypes = $client->transactions()->getSupportedTransactionTypes();
    foreach ($transactionTypes as $type => $name) {
        echo "  • {$type}: {$name}\n";
    }
    echo "\n";

    // 9. Export transaction data
    echo "📤 Starting transaction export...\n";
    $transactionExport = $client->export()->transactions([
        'format' => 'xlsx',
        'date_from' => date('Y-m-d', strtotime('-30 days')),
        'date_to' => date('Y-m-d'),
        'columns' => ['transaction_number', 'amount', 'currency', 'status', 'type', 'created_at'],
        'notify_email' => 'admin@yourcompany.com'
    ]);
    
    echo "✅ Export job queued: " . ($transactionExport['data']['job_id'] ?? 'N/A') . "\n\n";

    echo "🎉 Transaction management demo completed!\n";

} catch (ValidationException $e) {
    echo "❌ Validation Error: " . $e->getMessage() . "\n";
    if ($e->getErrors()) {
        echo "📝 Validation details:\n";
        foreach ($e->getErrors() as $error) {
            echo "  • {$error}\n";
        }
    }
} catch (ApiException $e) {
    echo "❌ API Error: " . $e->getMessage() . "\n";
    echo "🔍 HTTP Status: " . $e->getHttpStatusCode() . "\n";
} catch (Exception $e) {
    echo "❌ Unexpected error: " . $e->getMessage() . "\n";
} 