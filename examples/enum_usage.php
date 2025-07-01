<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Iberbanco\SDK\IberbancoClient;
use Iberbanco\SDK\Enums\Currency;
use Iberbanco\SDK\Enums\AccountStatus;
use Iberbanco\SDK\Enums\TransactionStatus;
use Iberbanco\SDK\Enums\TransactionType;
use Iberbanco\SDK\Enums\CardStatus;
use Iberbanco\SDK\Enums\ClientType;

echo "=== Iberbanco PHP SDK - Enum Usage Examples ===\n\n";

echo "🏦 Currency Examples:\n";
echo "USD ID: " . Currency::USD . " (Code: " . Currency::VALUES[Currency::USD] . ")\n";
echo "EUR ID: " . Currency::EUR . " (Code: " . Currency::VALUES[Currency::EUR] . ")\n";
echo "USD ISO Code: " . Currency::getIsoCode('USD') . "\n";
echo "Is USD supported? " . (Currency::isSupported('USD') ? 'Yes' : 'No') . "\n";
echo "All supported currencies: " . implode(', ', Currency::getAllCodes()) . "\n\n";

echo "📊 Account Status Examples:\n";
echo "Active status ID: " . AccountStatus::ACTIVE . "\n";
echo "Status name for ID 2: " . AccountStatus::getStatusName(AccountStatus::ACTIVE) . "\n";
echo "Is status 2 valid? " . (AccountStatus::isValid(AccountStatus::ACTIVE) ? 'Yes' : 'No') . "\n\n";

echo "💳 Transaction Examples:\n";
echo "SWIFT transaction type: " . TransactionType::INTERNATIONAL_TRANSACTION_SWIFT . "\n";
echo "Type name: " . TransactionType::getTypeName(TransactionType::INTERNATIONAL_TRANSACTION_SWIFT) . "\n";
echo "Requires international fields? " . (TransactionType::requiresInternationalFields(TransactionType::INTERNATIONAL_TRANSACTION_SWIFT) ? 'Yes' : 'No') . "\n\n";

echo "Transaction Status Examples:\n";
echo "New transaction status: " . TransactionStatus::STATUS_NEW . "\n";
echo "Is status pending? " . (TransactionStatus::isPending(TransactionStatus::STATUS_NEW) ? 'Yes' : 'No') . "\n";
echo "Is status final? " . (TransactionStatus::isFinal(TransactionStatus::STATUS_APPROVED) ? 'Yes' : 'No') . "\n\n";

echo "💳 Card Status Examples:\n";
echo "Active card status: " . CardStatus::STATUS_ACTIVATED . "\n";
echo "Is card active? " . (CardStatus::isActive(CardStatus::STATUS_ACTIVATED) ? 'Yes' : 'No') . "\n";
echo "Is card blocked? " . (CardStatus::isBlocked(CardStatus::STATUS_LOST) ? 'Yes' : 'No') . "\n\n";

echo "👤 Client Type Examples:\n";
echo "Personal client type: " . ClientType::PERSONAL_TYPE . " (" . ClientType::PERSONAL_TYPE_VALUE . ")\n";
echo "Business client type: " . ClientType::BUSINESS_TYPE . " (" . ClientType::BUSINESS_TYPE_VALUE . ")\n";
echo "Is personal client? " . (ClientType::isPersonal(ClientType::PERSONAL_TYPE) ? 'Yes' : 'No') . "\n\n";

echo "🔧 Practical Usage Examples:\n";
echo "Creating account with USD currency:\n";
$accountData = [
    'user_number' => 'USER123456',
    'currency' => Currency::USD,  // Use enum instead of magic number
    'reference' => 'Primary USD Account'
];
echo "Account data: " . json_encode($accountData, JSON_PRETTY_PRINT) . "\n\n";

echo "Filtering transactions by status:\n";
$transactionFilters = [
    'status' => TransactionStatus::STATUS_APPROVED,  // Use enum instead of magic number
    'type' => TransactionType::SEPA_TRANSACTION,     // Use enum instead of magic number
    'currency' => Currency::getIdByCode('EUR')       // Get currency ID by code
];
echo "Filter data: " . json_encode($transactionFilters, JSON_PRETTY_PRINT) . "\n\n";

echo "Card creation example:\n";
$cardData = [
    'user_number' => 'USER123456',
    'currency' => Currency::EUR,
    'client_type' => ClientType::PERSONAL_TYPE,
    'expected_status' => CardStatus::STATUS_ACTIVATED
];
echo "Card data: " . json_encode($cardData, JSON_PRETTY_PRINT) . "\n\n";

echo "✅ Benefits of using enums:\n";
echo "- No more magic numbers (840, 978, etc.)\n";
echo "- Type safety and IDE autocomplete\n";
echo "- Self-documenting code\n";
echo "- Easier to maintain and understand\n";
echo "- Consistent with your API structure\n";

echo "\nReady to use in production! 🚀\n"; 