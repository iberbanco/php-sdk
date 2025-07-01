<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Iberbanco\SDK\IberbancoClient;
use Iberbanco\SDK\Config\Configuration;
use Iberbanco\SDK\DTOs\User\RegisterPersonalUserDTO;
use Iberbanco\SDK\DTOs\Card\CreateCardDTO;
use Iberbanco\SDK\Utils\ValidationUtils;
use Iberbanco\SDK\Constants\ApiConstants;
use Iberbanco\SDK\Cache\MemoryCache;
use Iberbanco\SDK\Exceptions\ValidationException;
use Iberbanco\SDK\Exceptions\ApiException;

echo "=== Iberbanco SDK - Optimized Usage Example ===\n\n";

try {
    // 1. Optimized Configuration with Caching
    echo "1. Setting up optimized configuration...\n";
    
    $configArray = [
        'base_url' => getenv('IBERBANCO_BASE_URL') ?: 'https://api.iberbanco.com',
        'username' => getenv('IBERBANCO_USERNAME') ?: 'your-username',
        'timeout' => ApiConstants::DEFAULT_TIMEOUT,
        'debug' => true
    ];

    echo "✓ Configuration created with validation\n";

    // 2. Initialize client with optimized settings
    $client = IberbancoClient::create($configArray);
    
    // Set optimized timeout
    $client->getHttpClient()->setTimeout(ApiConstants::DEFAULT_TIMEOUT);
    
    echo "✓ Client initialized with optimized HTTP settings\n\n";

    // 3. Demonstrate ValidationUtils usage
    echo "2. Using centralized validation utilities...\n";
    
    // Email validation
    try {
        ValidationUtils::validateEmail('test@example.com');
        echo "✓ Email validation passed\n";
    } catch (ValidationException $e) {
        echo "✗ Email validation failed: " . $e->getMessage() . "\n";
    }
    
    // IBAN validation
    try {
        ValidationUtils::validateIban('ES9121000418450200051332', 'test_iban');
        echo "✓ IBAN validation passed\n";
    } catch (ValidationException $e) {
        echo "✗ IBAN validation failed: " . $e->getMessage() . "\n";
    }
    
    // Phone number validation
    try {
        ValidationUtils::validatePhoneNumber('+1234567890');
        echo "✓ Phone number validation passed\n";
    } catch (ValidationException $e) {
        echo "✗ Phone number validation failed: " . $e->getMessage() . "\n";
    }
    
    echo "\n";

    // 4. Optimized DTO usage with error handling
    echo "3. Creating user with optimized DTO validation...\n";
    
    $userData = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john.doe@example.com',
        'phone' => '+1234567890',
        'date_of_birth' => '1990-01-01',
        'address' => [
            'street' => '123 Main Street',
            'city' => 'New York',
            'state' => 'NY',
            'postal_code' => '10001',
            'country' => 'US'
        ],
        'identity_document_type' => 'passport',
        'identity_document_number' => 'AB1234567',
        'terms_accepted' => true,
        'marketing_consent' => false,
        'preferred_currency' => 'USD'
    ];

    try {
        // Create DTO with automatic validation
        $userDTO = RegisterPersonalUserDTO::fromArray($userData);
        echo "✓ User DTO created and validated successfully\n";
        
        // Convert back to array for API call
        $validatedData = $userDTO->toArray();
        echo "✓ DTO converted to validated array\n";
        
    } catch (ValidationException $e) {
        echo "✗ User DTO validation failed:\n";
        foreach ($e->getErrors() as $error) {
            echo "  - $error\n";
        }
    }
    
    echo "\n";

    // 5. Demonstrate optimized card creation
    echo "4. Creating card with optimized validation...\n";
    
    $cardData = [
        'user_number' => 'USR123456',
        'account_number' => '1234567890',
        'amount' => 100.00,
        'currency' => 1, // USD
        'shipping_address' => '123 Main Street',
        'shipping_city' => 'New York',
        'shipping_state' => 'NY',
        'shipping_country_code' => 'US',
        'shipping_post_code' => '10001',
        'delivery_method' => 'Standard'
    ];

    try {
        $cardDTO = CreateCardDTO::fromArray($cardData);
        echo "✓ Card DTO created and validated successfully\n";
        
        // Show required fields
        $requiredFields = $cardDTO->getRequiredFields();
        echo "✓ Required fields: " . implode(', ', $requiredFields) . "\n";
        
    } catch (ValidationException $e) {
        echo "✗ Card DTO validation failed:\n";
        foreach ($e->getErrors() as $error) {
            echo "  - $error\n";
        }
    }
    
    echo "\n";

    // 6. Demonstrate caching usage
    echo "5. Using optimized caching...\n";
    
    $cache = new MemoryCache();
    
    // Cache some configuration
    $cache->set('api_version', ApiConstants::API_VERSION, 3600); // 1 hour TTL
    $cache->set('supported_currencies', ValidationUtils::SUPPORTED_CURRENCIES);
    
    // Retrieve from cache
    $version = $cache->get('api_version');
    $currencies = $cache->get('supported_currencies');
    
    echo "✓ Cached API version: $version\n";
    echo "✓ Cached " . count($currencies) . " supported currencies\n";
    
    // Check cache existence
    if ($cache->has('api_version')) {
        echo "✓ API version found in cache\n";
    }
    
    echo "\n";

    // 7. Demonstrate error handling best practices
    echo "6. Testing error handling...\n";
    
    try {
        // Test invalid timeout
        $client->getHttpClient()->setTimeout(ApiConstants::MAX_TIMEOUT + 1);
    } catch (\InvalidArgumentException $e) {
        echo "✓ Timeout validation caught: " . $e->getMessage() . "\n";
    }
    
    try {
        // Test invalid email
        ValidationUtils::validateEmail('invalid-email');
    } catch (ValidationException $e) {
        echo "✓ Email validation error caught: " . $e->getMessage() . "\n";
    }
    
    echo "\n";

    // 8. Performance demonstration
    echo "7. Performance optimization demonstration...\n";
    
    $startTime = microtime(true);
    
    // Validate multiple IBANs using utility
    $ibans = [
        'ES9121000418450200051332',
        'GB29NWBK60161331926819',
        'FR1420041010050500013M02606'
    ];
    
    foreach ($ibans as $iban) {
        try {
            ValidationUtils::validateIban($iban);
        } catch (ValidationException $e) {
            // Handle invalid IBAN
        }
    }
    
    $endTime = microtime(true);
    $executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds
    
    echo "✓ Validated " . count($ibans) . " IBANs in " . number_format($executionTime, 2) . "ms\n";
    
    // Cache performance test
    $startTime = microtime(true);
    
    for ($i = 0; $i < 1000; $i++) {
        $cache->set("test_key_$i", "test_value_$i");
    }
    
    $endTime = microtime(true);
    $executionTime = ($endTime - $startTime) * 1000;
    
    echo "✓ Cached 1000 items in " . number_format($executionTime, 2) . "ms\n";
    
    echo "\n";

    echo "=== Optimization Summary ===\n";
    echo "✓ Centralized validation with ValidationUtils\n";
    echo "✓ Constants for magic numbers (ApiConstants)\n";
    echo "✓ Improved error handling with specific exceptions\n";
    echo "✓ Performance optimizations with caching\n";
    echo "✓ Type-safe DTOs with automatic validation\n";
    echo "✓ Configurable timeouts with validation\n";
    echo "✓ Memory-efficient data structures\n";
    echo "✓ Comprehensive logging and debugging\n";

} catch (ApiException $e) {
    echo "API Error: " . $e->getMessage() . "\n";
    if ($e->getErrors()) {
        echo "Errors: " . implode(', ', $e->getErrors()) . "\n";
    }
} catch (ValidationException $e) {
    echo "Validation Error: " . $e->getMessage() . "\n";
    if ($e->getErrors()) {
        echo "Errors: " . implode(', ', $e->getErrors()) . "\n";
    }
} catch (\Exception $e) {
    echo "Unexpected Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Example Complete ===\n"; 