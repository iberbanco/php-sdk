# Iberbanco PHP SDK

🏦 **Official PHP SDK for [Iberbanco API v2](https://sandbox.api.iberbanco.finance/)** - Professional banking platform supporting 80+ currencies, 185+ jurisdictions, and comprehensive financial services.

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

## 🌟 Key Features

- **Complete Banking Operations**: Users, Accounts, Cards, Transactions, Export
- **Enhanced Security**: 3-layer authentication system (Token + Timestamp + Hash)
- **Multiple Transaction Types**: SWIFT, SEPA, ACH, BACS, Crypto, and more
- **Export Capabilities**: Comprehensive data export with email delivery
- **Card Management**: Virtual and physical card operations
- **Exchange Services**: Real-time currency exchange rates
- **Robust Error Handling**: Comprehensive exception management
- **Type-Safe DTOs**: Data Transfer Objects with validation for all requests
- **Professional Enums**: Type-safe enums for currencies, statuses, and types
- **PSR-4 Compliant**: Modern PHP standards and best practices

## 📋 Requirements

- PHP 8.1 or higher
- cURL extension
- JSON extension
- Hash extension

## 📦 Installation

Install the SDK via Composer:

```bash
composer require iberbanco/php-sdk
```

### 📚 Example Files

The SDK includes comprehensive example files:

- `examples/basic_usage.php` - Basic SDK operations and authentication
- `examples/transaction_management.php` - Transaction processing examples
- `examples/card_management.php` - Card operations and management
- `examples/dto_usage.php` - Complete DTO usage demonstrations
- `examples/configuration_examples.php` - Configuration options

## 🚀 Quick Start

### Basic Setup

```php
<?php

require_once 'vendor/autoload.php';

use Iberbanco\SDK\IberbancoClient;

// Recommended: Use sandbox boolean for environment switching
$client = IberbancoClient::create([
    'sandbox' => true, // Set to false for production
    'username' => $_ENV['IBERBANCO_USERNAME'] ?? 'your_agent_username',
    'timeout' => 30,
    'verify_ssl' => true
]);
```

## ⚙️ Configuration

### Basic Configuration

```php
// Sandbox environment (for testing)
$client = IberbancoClient::create([
    'sandbox' => true,
    'username' => 'your_agent_username'
]);

// Production environment
$client = IberbancoClient::create([
    'sandbox' => false,
    'username' => 'your_agent_username'
]);
```

### Environment Variables

```bash
IBERBANCO_SANDBOX=true
IBERBANCO_USERNAME=your_agent_username
```

```php
$client = IberbancoClient::createFromEnvironment();
```

### Configuration Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `sandbox` | boolean | `true` | Environment (sandbox/production) |
| `username` | string | required | Your agent username |
| `timeout` | integer | `30` | Request timeout in seconds |
| `verify_ssl` | boolean | `true` | SSL certificate verification |
| `debug` | boolean | `false` | Debug mode |

## 🔐 Authentication

```php
// Authenticate and get access token
$authResponse = $client->auth()->authenticate('your_username', 'your_password');
$token = $authResponse['data']['token'];

// Set token for subsequent requests
$client->setAuthToken($token);
```

## 💰 Currency System

**Important**: The Iberbanco API uses internal currency IDs, not ISO codes.

```php
// Supported Currency IDs
1  => 'USD'    // US Dollar
2  => 'EUR'    // Euro  
3  => 'GBP'    // British Pound
4  => 'CHF'    // Swiss Franc
5  => 'RUB'    // Russian Ruble
6  => 'TRY'    // Turkish Lira
7  => 'AED'    // UAE Dirham
8  => 'CNH'    // Chinese Yuan
9  => 'AUD'    // Australian Dollar
10 => 'CZK'    // Czech Koruna
11 => 'PLN'    // Polish Zloty
12 => 'CAD'    // Canadian Dollar
13 => 'USDT'   // Tether (USDT)
14 => 'HKD'    // Hong Kong Dollar
15 => 'SGD'    // Singapore Dollar
16 => 'JPY'    // Japanese Yen

// Always use internal IDs in requests
$accountData = [
    'user_number' => 'USER123456',
    'currency' => 1, // USD
    'reference' => 'Primary USD account'
];
```

## 🔧 Using Data Transfer Objects (DTOs)

The SDK includes comprehensive DTOs that provide type safety and validation:

### Account Operations

```php
use Iberbanco\SDK\DTOs\Account\CreateAccountDTO;
use Iberbanco\SDK\DTOs\Account\SearchAccountsDTO;

// Create account with DTO
$accountDTO = CreateAccountDTO::fromArray([
    'user_number' => 'USER123456',
    'currency' => 1, // USD (internal ID)
    'reference' => 'Primary account'
]);

$account = $client->accounts()->create($accountDTO);
```

### Card Operations

```php
use Iberbanco\SDK\DTOs\Card\CreateCardDTO;

// Create card with DTO
$cardDTO = CreateCardDTO::fromArray([
    'user_number' => 'USER123456',
    'account_number' => 'ACC123456789',
    'amount' => 1000.00,
    'currency' => 1, // USD (internal ID)
    'shipping_address' => '123 Main Street',
    'shipping_city' => 'New York',
    'shipping_state' => 'NY',
    'shipping_country_code' => 'US',
    'shipping_post_code' => '10001',
    'delivery_method' => 'Standard'
]);

$card = $client->cards()->create($cardDTO);
```

### Transaction Operations

```php
use Iberbanco\SDK\DTOs\Transaction\CreateSwiftTransactionDTO;
use Iberbanco\SDK\DTOs\Transaction\CreateSepaTransactionDTO;

// SWIFT transaction
$swiftDTO = CreateSwiftTransactionDTO::fromArray([
    'account_number' => 'ACC123456789',
    'amount' => 1500.00,
    'reference' => 'International payment',
    'iban_code' => 'DE89370400440532013000',
    'beneficiary_name' => 'John Smith',
    'beneficiary_country' => 'Germany',
    'beneficiary_city' => 'Frankfurt',
    'beneficiary_address' => '789 International Blvd',
    'beneficiary_zip_code' => '60311',
    'beneficiary_email' => 'john.smith@example.com',
    'swift_code' => 'DEUTDEFF',
    'bank_name' => 'Deutsche Bank',
    'bank_country' => 'Germany',
    'bank_city' => 'Frankfurt',
    'bank_address' => 'Taunusanlage 12',
    'bank_zip_code' => '60325'
]);

$transaction = $client->transactions()->create('swift', $swiftDTO);
```

### Crypto Operations

```php
use Iberbanco\SDK\DTOs\CryptoTransaction\CreatePaymentLinkDTO;

// Create crypto payment link
$paymentLinkDTO = CreatePaymentLinkDTO::fromArray([
    'email' => 'customer@example.com',
    'order_id' => 'ORDER_' . time(),
    'fiat_amount' => 250.00,
    'fiat_currency' => 'USD',
    'redirect_url' => 'https://yoursite.com/payment/success'
]);

$paymentLink = $client->cryptoTransactions()->createPaymentLink($paymentLinkDTO);
```

## 📊 Basic Operations

### Working with Accounts

```php
// List accounts
$accounts = $client->accounts()->list([
    'per_page' => 50,
    'currency' => 1 // USD
]);

// Get account details
$account = $client->accounts()->show('ACC123456789');

// Get total balance
$balance = $client->accounts()->totalBalance(['currency' => 1]);
```

### Working with Cards

```php
// List cards
$cards = $client->cards()->list(['visibility' => 'active']);

// Get card details
$card = $client->cards()->show('CARD123456');

// Get card transactions
$transactions = $client->cards()->transactions([
    'remote_id' => 'CARD123456',
    'userNumber' => 'USER123456',
    'san' => 'SAN123456',
    'year' => 2024,
    'month' => 12
]);

// Request physical card
$physicalCard = $client->cards()->requestPhysical([
    'remote_id' => 'CARD123456'
]);
```

### Working with Transactions

```php
// List transactions
$transactions = $client->transactions()->list([
    'per_page' => 50
]);

// Get transaction details
$transaction = $client->transactions()->show('TXN123456789');

// Search transactions
$searchResults = $client->transactions()->search([
    'amount_min' => 100.00,
    'date_from' => '2024-01-01',
    'date_to' => '2024-12-31'
]);
```

### Exchange Services

```php
// Get exchange rate
$rate = $client->exchange()->getRate([
    'from' => 'USD',
    'to' => 'EUR',
    'amount' => 1000.00
]);
```

## 🔒 Security

The SDK implements Iberbanco's 3-layer security system:

1. **Agent Token**: Your unique API access token
2. **Timestamp**: Current Unix timestamp for request validation  
3. **Hash**: HMAC-SHA256 signature for request integrity

## 🧪 Testing

```bash
# Run all tests
composer test

# Run with coverage
composer test-coverage

# Static analysis
composer analyse
```

## 🌍 Supported Services

### ✅ **Complete Banking Platform**
- **16 Currencies** supported (USD, EUR, GBP, CHF, RUB, TRY, AED, CNH, AUD, CZK, PLN, CAD, USDT, HKD, SGD, JPY)
- **185+ Jurisdictions** for operations  
- **10+ Payment Networks** (SWIFT, SEPA, ACH, BACS, EFT, INTERAC, etc.)
- **Virtual Multi-Currency Accounts**
- **Debit Cards** (Virtual & Physical)
- **Crypto Transactions** (USDT support)
- **Export Services** with email delivery

## 🏦 About Iberbanco

**Iberbanco Ltd** is a Canadian MSB-registered financial services company providing comprehensive banking solutions.

- **Company**: Iberbanco Ltd  
- **Address**: 4 Robert Speck Parkway, Mississauga, ON L4Z 1S1, Canada  
- **Phone**: +1 (251) 277-8085 | +3 (716) 731-4388  
- **Email**: info@iberbancoltd.com  
- **MSB Registration**: M23371461  
- **Website**: [https://iberbancoltd.com/](https://iberbancoltd.com/)

## 📖 API Documentation

For detailed API documentation, visit: [https://sandbox.api.iberbanco.finance/doc](https://sandbox.api.iberbanco.finance/doc)

## 🆘 Support

- 📧 Email: info@iberbancoltd.com
- 📚 API Documentation: https://sandbox.api.iberbanco.finance/doc
- 🌐 Website: https://iberbancoltd.com/

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
