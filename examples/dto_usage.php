<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Iberbanco\SDK\IberbancoClient;
use Iberbanco\SDK\DTOs\Auth\AuthLoginDTO;
use Iberbanco\SDK\DTOs\User\RegisterPersonalUserDTO;
use Iberbanco\SDK\DTOs\User\RegisterBusinessUserDTO;
use Iberbanco\SDK\DTOs\User\ListUsersDTO;
use Iberbanco\SDK\DTOs\Account\CreateAccountDTO;
use Iberbanco\SDK\DTOs\Account\SearchAccountsDTO;
use Iberbanco\SDK\DTOs\Transaction\CreateSwiftTransactionDTO;
use Iberbanco\SDK\DTOs\Transaction\CreateSepaTransactionDTO;
use Iberbanco\SDK\DTOs\Card\CreateCardDTO;
use Iberbanco\SDK\DTOs\Card\RequestPhysicalCardDTO;
use Iberbanco\SDK\DTOs\CryptoTransaction\CreatePaymentLinkDTO;
use Iberbanco\SDK\DTOs\Exchange\GetRateDTO;
use Iberbanco\SDK\DTOs\Export\ExportDataDTO;
use Iberbanco\SDK\Exceptions\ValidationException;
use Iberbanco\SDK\Exceptions\ApiException;

// Create SDK client
$client = IberbancoClient::create([
    'base_url' => 'https://sandbox.api.iberbanco.finance/v2',
    'username' => 'your_agent_username',
    'timeout' => 30,
    'verify_ssl' => true,
    'debug' => true
]);

try {
    echo "🔧 === Iberbanco SDK DTO Usage Examples ===\n\n";

    // 1. Authentication with DTO
    echo "🔐 1. Authentication using DTO\n";
    $authDTO = AuthLoginDTO::fromArray([
        'username' => 'your_agent_username',
        'password' => 'your_password'
    ]);
    
    echo "✅ AuthDTO validated successfully\n";
    echo "Required fields: " . implode(', ', $authDTO->getRequiredFields()) . "\n\n";

    // Authenticate (commented out for demo)
    // $authResponse = $client->auth()->authenticate($authDTO->username, $authDTO->password);
    // $client->setAuthToken($authResponse['data']['token']);

    // 2. User Registration with DTOs
    echo "👤 2. User Registration using DTOs\n";
    
    // Personal user DTO
    $personalUserDTO = RegisterPersonalUserDTO::fromArray([
        'email' => 'john.doe.demo+' . time() . '@example.com',
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
    
    echo "✅ Personal user DTO validated\n";
    echo "Required fields: " . implode(', ', $personalUserDTO->getRequiredFields()) . "\n";
    
    // Business user DTO
    $businessUserDTO = RegisterBusinessUserDTO::fromArray([
        'email' => 'business.demo+' . time() . '@company.com',
        'password' => 'SecurePassword123!',
        'first_name' => 'Jane',
        'last_name' => 'Smith',
        'country' => 'US',
        'address' => '456 Business Ave',
        'city' => 'Chicago',
        'post_code' => '60601',
        'call_number' => '+1987654321',
        'company_name' => 'Demo Corporation',
        'company_registration_number' => 'REG123456789'
    ]);
    
    echo "✅ Business user DTO validated\n";
    echo "Required fields: " . implode(', ', $businessUserDTO->getRequiredFields()) . "\n\n";

    // 3. List Users with DTO
    echo "📋 3. List Users using DTO\n";
    $listUsersDTO = ListUsersDTO::fromArray([
        'per_page' => 25,
        'country' => 'US',
        'type' => 1,
        'date_from' => '2024-01-01'
    ]);
    
    echo "✅ List users DTO validated\n";
    echo "Filter applied: " . json_encode($listUsersDTO->toArray()) . "\n\n";

    // 4. Account Operations with DTOs
    echo "🏦 4. Account Operations using DTOs\n";
    
    // Create account DTO
    $createAccountDTO = CreateAccountDTO::fromArray([
        'user_number' => 'USER123456',
        'currency' => [840, 978], // USD and EUR
        'reference' => 'Multi-currency demo account'
    ]);
    
    echo "✅ Create account DTO validated\n";
    echo "Required fields: " . implode(', ', $createAccountDTO->getRequiredFields()) . "\n";
    
    // Search accounts DTO
    $searchAccountsDTO = SearchAccountsDTO::fromArray([
        'currency' => 840,
        'min_balance' => 1000.00,
        'date_from' => '2024-01-01',
        'per_page' => 20
    ]);
    
    echo "✅ Search accounts DTO validated\n";
    echo "Search criteria: " . json_encode($searchAccountsDTO->toArray()) . "\n\n";

    // 5. Transaction DTOs
    echo "💸 5. Transaction Operations using DTOs\n";
    
    // SWIFT transaction DTO
    $swiftDTO = CreateSwiftTransactionDTO::fromArray([
        'account_number' => 'ACC123456789',
        'amount' => 1500.00,
        'recipient_account_number' => 'REC987654321',
        'recipient_bank_code' => 'DEUTDEFF',
        'recipient_name' => 'John Smith',
        'recipient_address' => '789 International Blvd, Frankfurt, Germany',
        'reference' => 'Demo payment',
        'description' => 'SDK demonstration payment'
    ]);
    
    echo "✅ SWIFT transaction DTO validated\n";
    echo "Required fields: " . implode(', ', $swiftDTO->getRequiredFields()) . "\n";
    
    // SEPA transaction DTO
    $sepaDTO = CreateSepaTransactionDTO::fromArray([
        'account_number' => 'ACC123456789',
        'amount' => 500.00,
        'recipient_iban' => 'DE89370400440532013000',
        'recipient_name' => 'Maria Garcia',
        'reference' => 'Demo SEPA payment',
        'description' => 'SEPA demonstration'
    ]);
    
    echo "✅ SEPA transaction DTO validated\n";
    echo "Required fields: " . implode(', ', $sepaDTO->getRequiredFields()) . "\n\n";

    // 6. Card Operations with DTOs
    echo "💳 6. Card Operations using DTOs\n";
    
    // Create card DTO
    $createCardDTO = CreateCardDTO::fromArray([
        'user_number' => 'USER123456',
        'account_number' => 'ACC123456789',
        'card_type' => 'virtual',
        'currency' => 840,
        'daily_limit' => 1000.00,
        'monthly_limit' => 5000.00
    ]);
    
    echo "✅ Create card DTO validated\n";
    echo "Required fields: " . implode(', ', $createCardDTO->getRequiredFields()) . "\n";
    
    // Request physical card DTO
    $physicalCardDTO = RequestPhysicalCardDTO::fromArray([
        'card_id' => 'CARD123456',
        'delivery_address' => [
            'street' => '123 Main Street, Apt 4B',
            'city' => 'New York',
            'country' => 'US',
            'postal_code' => '10001',
            'state' => 'NY'
        ],
        'express_delivery' => true
    ]);
    
    echo "✅ Physical card request DTO validated\n";
    echo "Required fields: " . implode(', ', $physicalCardDTO->getRequiredFields()) . "\n\n";

    // 7. Crypto Payment Link DTO
    echo "₿ 7. Crypto Payment Link using DTO\n";
    $paymentLinkDTO = CreatePaymentLinkDTO::fromArray([
        'amount' => 250.00,
        'currency' => 'USD',
        'description' => 'Demo product purchase',
        'callback_url' => 'https://yoursite.com/webhooks/payment',
        'return_url' => 'https://yoursite.com/payment/success',
        'expires_at' => date('Y-m-d H:i:s', strtotime('+24 hours'))
    ]);
    
    echo "✅ Payment link DTO validated\n";
    echo "Required fields: " . implode(', ', $paymentLinkDTO->getRequiredFields()) . "\n\n";

    // 8. Exchange Rate DTO
    echo "💱 8. Exchange Rate using DTO\n";
    $rateDTO = GetRateDTO::fromArray([
        'from' => 'USD',
        'to' => 'EUR',
        'amount' => 1000.00,
        'precision' => 4
    ]);
    
    echo "✅ Exchange rate DTO validated\n";
    echo "Rate request: " . json_encode($rateDTO->toArray()) . "\n\n";

    // 9. Export Data DTO
    echo "📤 9. Export Data using DTO\n";
    $exportDTO = ExportDataDTO::fromArray([
        'format' => 'csv',
        'date_from' => '2024-01-01',
        'date_to' => '2024-12-31',
        'columns' => ['user_number', 'email', 'first_name', 'last_name', 'created_at'],
        'notify_email' => 'admin@yourcompany.com',
        'compressed' => true,
        'limit' => 10000
    ]);
    
    echo "✅ Export data DTO validated\n";
    echo "Export configuration: " . json_encode($exportDTO->toArray()) . "\n\n";

    // 10. Demonstrate Validation Errors
    echo "❌ 10. Demonstrating Validation Errors\n";
    try {
        // Invalid email and password
        $invalidUserDTO = RegisterPersonalUserDTO::fromArray([
            'email' => 'invalid-email-format',
            'password' => '123', // Too short
            'first_name' => 'A', // Too short
            'date_of_birth' => '2010-01-01' // Too young
        ]);
    } catch (ValidationException $e) {
        echo "🚨 Validation errors caught:\n";
        echo "Message: " . $e->getMessage() . "\n";
        echo "Errors:\n";
        foreach ($e->getErrors() as $error) {
            echo "  • {$error}\n";
        }
    }

    echo "\n🎉 DTO demonstration completed successfully!\n";
    echo "\n📝 Key Benefits Demonstrated:\n";
    echo "✓ Type safety with clear field definitions\n";
    echo "✓ Automatic validation with descriptive errors\n";
    echo "✓ Self-documenting code with required fields\n";
    echo "✓ Consistent data structure across all operations\n";
    echo "✓ Better IDE support and autocomplete\n";

} catch (ValidationException $e) {
    echo "❌ Validation Error: " . $e->getMessage() . "\n";
    if ($e->getErrors()) {
        echo "📝 Details:\n";
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