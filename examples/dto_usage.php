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
use Iberbanco\SDK\DTOs\Account\TotalBalanceDTO;
use Iberbanco\SDK\Exceptions\ValidationException;
use Iberbanco\SDK\Exceptions\ApiException;

// Create SDK client
$client = IberbancoClient::create([
    'sandbox' => true, // Set to false for production
    'username' => $_ENV['IBERBANCO_USERNAME'] ?? 'your_agent_username',
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
    
    // Personal user DTO (includes all required fields for card+crypto services)
    $personalUserDTO = RegisterPersonalUserDTO::fromArray([
        'email' => 'john.doe.demo+' . time() . '@example.com',
        'password' => 'SecurePassword123!',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'date_of_birth' => '1990-01-15',
        'citizenship' => 'US',
        'address' => '123 Main Street',
        'city' => 'New York',
        'state_or_province' => 'NY',
        'post_code' => '10001',
        'country' => 'US',
        'call_number' => '+1234567890',
        'currencies' => [13], // USDT for card+crypto services
        'selected_service' => ['card', 'crypto'],
        'sources_of_wealth' => ['employment'],
        'is_pep' => false,
        'terms_accepted' => true
    ]);
    
    echo "✅ Personal user DTO validated\n";
    echo "Required fields: " . implode(', ', $personalUserDTO->getRequiredFields()) . "\n";
    
    // Business user DTO (complete V2 API structure - note: requires file uploads in real usage)
    $businessUserDTO = RegisterBusinessUserDTO::fromArray([
        'email' => 'business.demo+' . time() . '@company.com',
        'password' => 'SecurePassword123!',
        'first_name' => 'Jane',
        'last_name' => 'Smith',
        'call_number' => '+1987654321',
        'date_of_birth' => '1985-05-20',
        'address' => '456 Business Ave',
        'city' => 'Chicago',
        'country' => 'US',
        'state_or_province' => 'IL',
        'post_code' => '60601',
        'identity_card_type' => 1, // e.g., passport
        'identity_card_id' => 'P123456789',
        'tax_number' => 'TAX123456789',
        'citizenship' => 'US',
        'currencies' => [1, 2], // USD, EUR
        'sources_of_wealth' => ['business_income'],
        'company_name' => 'Demo Corporation',
        'company_type' => 1, // e.g., LLC
        'registration_date' => '2020-01-15',
        'registration_number' => 'REG123456789',
        'nature_of_business' => 1, // e.g., technology
        'financial_regulator' => 'SEC',
        'regulatory_license_number' => 'LIC123456',
        'industry_id' => 'TECH001',
        'authorized_person_country_of_residence' => 'US',
        'authorized_person_city' => 'Chicago',
        'authorized_person_address' => '456 Business Ave',
        'authorized_person_postal_code' => '60601',
        'selected_service' => ['card', 'crypto', 'bank']
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
    
    // Create account using DTO
    $createAccountDTO = CreateAccountDTO::fromArray([
        'user_number' => 'USER123456',
        'currency' => [1, 2], // USD and EUR (internal IDs)
        'reference' => 'Multi-currency account'
    ]);
    
    echo "✅ Create account DTO validated\n";
    echo "Required fields: " . implode(', ', $createAccountDTO->getRequiredFields()) . "\n";
    
    // Search accounts using DTO
    $searchDTO = SearchAccountsDTO::fromArray([
        'currency' => 1, // USD (internal ID)
        'min_balance' => 1000.00,
        'date_from' => '2024-01-01'
    ]);
    
    echo "✅ Search accounts DTO validated\n";
    echo "Search criteria: " . json_encode($searchDTO->toArray()) . "\n\n";

    // Get total balance using DTO
    $balanceDTO = TotalBalanceDTO::fromArray([
        'currency' => 1 // USD (internal ID)
    ]);

    // 5. Transaction DTOs
    echo "💸 5. Transaction Operations using DTOs\n";
    
    // SWIFT transaction DTO (using correct V2 API structure)
    $swiftDTO = CreateSwiftTransactionDTO::fromArray([
        'account_number' => 'ACC123456789',
        'amount' => 1500.00,
        'reference' => 'Demo payment',
        'iban_code' => 'DE89370400440532013000',
        'beneficiary_name' => 'John Smith',
        'beneficiary_country' => 'Germany',
        'beneficiary_state' => 'Hesse',
        'beneficiary_city' => 'Frankfurt',
        'beneficiary_address' => '789 International Blvd',
        'beneficiary_zip_code' => '60311',
        'beneficiary_email' => 'john.smith@example.com',
        'swift_code' => 'DEUTDEFF',
        'bank_name' => 'Deutsche Bank',
        'bank_country' => 'Germany',
        'bank_state' => 'Hesse',
        'bank_city' => 'Frankfurt',
        'bank_address' => 'Taunusanlage 12',
        'bank_zip_code' => '60325'
    ]);
    
    echo "✅ SWIFT transaction DTO validated\n";
    echo "Required fields: " . implode(', ', $swiftDTO->getRequiredFields()) . "\n";
    
    // SEPA transaction DTO (using correct V2 API structure)
    $sepaDTO = CreateSepaTransactionDTO::fromArray([
        'account_number' => 'ACC123456789',
        'amount' => 500.00,
        'reference' => 'Demo SEPA payment',
        'iban_code' => 'DE89370400440532013000',
        'beneficiary_name' => 'Maria Garcia',
        'beneficiary_country' => 'Germany',
        'beneficiary_state' => 'Bavaria',
        'beneficiary_city' => 'Munich',
        'beneficiary_address' => '456 European Street',
        'beneficiary_zip_code' => '80331',
        'beneficiary_email' => 'maria.garcia@example.com',
        'swift_code' => 'DEUTDEMM',
        'bank_name' => 'Deutsche Bank Munich',
        'bank_country' => 'Germany',
        'bank_state' => 'Bavaria',
        'bank_city' => 'Munich',
        'bank_address' => 'Maximilianstrasse 1',
        'bank_zip_code' => '80539'
    ]);
    
    echo "✅ SEPA transaction DTO validated\n";
    echo "Required fields: " . implode(', ', $sepaDTO->getRequiredFields()) . "\n\n";

    // 6. Card Operations with DTOs
    echo "💳 6. Card Operations using DTOs\n";
    
    // Create card DTO (using correct V2 API structure)
    $createCardDTO = CreateCardDTO::fromArray([
        'user_number' => 'USER123456',
        'account_number' => 'ACC123456789',
        'amount' => 1000.00,
        'currency' => 1, // USD (internal ID)
        'shipping_address' => '123 Main Street',
        'shipping_city' => 'New York',
        'shipping_state' => 'NY',
        'shipping_country_code' => 'US',
        'shipping_post_code' => '10001',
        'delivery_method' => 'Standard',
        'product_type' => 'virtual'
    ]);
    
    echo "✅ Create card DTO validated\n";
    echo "Required fields: " . implode(', ', $createCardDTO->getRequiredFields()) . "\n";
    
    // Request physical card DTO (using correct V2 API structure)
    $physicalCardDTO = RequestPhysicalCardDTO::fromArray([
        'remote_id' => 'CARD123456'
    ]);
    
    echo "✅ Physical card request DTO validated\n";
    echo "Required fields: " . implode(', ', $physicalCardDTO->getRequiredFields()) . "\n\n";

    // 7. Crypto Payment Link DTO (using correct V2 API structure)
    echo "₿ 7. Crypto Payment Link using DTO\n";
    $paymentLinkDTO = CreatePaymentLinkDTO::fromArray([
        'email' => 'customer@example.com',
        'order_id' => 'ORDER_' . time(),
        'fiat_amount' => 250.00,
        'fiat_currency' => 'USD',
        'redirect_url' => 'https://yoursite.com/payment/success'
    ]);
    
    echo "✅ Payment link DTO validated\n";
    echo "Required fields: " . implode(', ', $paymentLinkDTO->getRequiredFields()) . "\n\n";

    // 8. Exchange Rate DTO (using correct V2 API structure)
    echo "💱 8. Exchange Rate using DTO\n";
    $rateDTO = GetRateDTO::fromArray([
        'from' => 'USD',
        'to' => 'EUR',
        'amount' => 1000.00
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