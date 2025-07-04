<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Iberbanco\SDK\IberbancoClient;
use Iberbanco\SDK\Config\Configuration;

echo "=== Iberbanco SDK - Configuration Examples ===\n\n";

// 1. NEW: Using sandbox boolean (Recommended)
echo "1. New Sandbox Boolean Configuration (Recommended)\n";
echo "=================================================\n\n";

// Sandbox configuration
$sandboxConfig = [
    'sandbox' => true, // Automatically uses sandbox endpoint
    'username' => 'your_agent_username',
    'timeout' => 30,
    'verify_ssl' => true,
    'debug' => false
];

$sandboxClient = IberbancoClient::create($sandboxConfig);
echo "✅ Sandbox client created with endpoint: " . $sandboxClient->getConfig()->getBaseUrl() . "\n";

// Production configuration
$productionConfig = [
    'sandbox' => false, // Automatically uses production endpoint
    'username' => 'your_agent_username',
    'timeout' => 30,
    'verify_ssl' => true,
    'debug' => false
];

$productionClient = IberbancoClient::create($productionConfig);
echo "✅ Production client created with endpoint: " . $productionClient->getConfig()->getBaseUrl() . "\n\n";

// 2. Environment-based configuration
echo "2. Environment Variable Configuration\n";
echo "====================================\n\n";

// Set environment variables (in real usage, these would be set in your environment)
putenv('IBERBANCO_SANDBOX=true');
putenv('IBERBANCO_USERNAME=your_agent_username');
putenv('IBERBANCO_TIMEOUT=45');
putenv('IBERBANCO_VERIFY_SSL=true');
putenv('IBERBANCO_DEBUG=false');

$envClient = IberbancoClient::createFromEnvironment();
echo "✅ Environment client created with sandbox: " . ($envClient->getConfig()->isSandbox() ? 'true' : 'false') . "\n";
echo "✅ Environment client endpoint: " . $envClient->getConfig()->getBaseUrl() . "\n\n";

// 3. LEGACY: Manual base URL (Still supported for backward compatibility)
echo "3. Legacy Base URL Configuration (Backward Compatible)\n";
echo "=====================================================\n\n";

$legacyConfig = [
    'base_url' => 'https://sandbox.api.iberbanco.finance/api/v2', // Manual endpoint
    'username' => 'your_agent_username',
    'timeout' => 30,
    'verify_ssl' => true
];

$legacyClient = IberbancoClient::create($legacyConfig);
echo "✅ Legacy client created with custom endpoint: " . $legacyClient->getConfig()->getBaseUrl() . "\n";
echo "⚠️  Note: When base_url is provided, it overrides the sandbox setting\n\n";

// 4. Dynamic configuration switching
echo "4. Dynamic Configuration Switching\n";
echo "==================================\n\n";

$config = new Configuration(['sandbox' => true, 'username' => 'test_user']);
echo "✅ Initial configuration - Sandbox: " . ($config->isSandbox() ? 'true' : 'false') . "\n";
echo "✅ Initial endpoint: " . $config->getBaseUrl() . "\n";

// Switch to production
$config->setSandbox(false);
echo "✅ After switching - Sandbox: " . ($config->isSandbox() ? 'true' : 'false') . "\n";
echo "✅ New endpoint: " . $config->getBaseUrl() . "\n\n";

// 5. Configuration validation
echo "5. Configuration Validation\n";
echo "===========================\n\n";

try {
    $validConfig = new Configuration([
        'sandbox' => true,
        'username' => 'valid_user',
        'timeout' => 30
    ]);
    $validConfig->validate();
    echo "✅ Configuration validation passed\n";
} catch (\InvalidArgumentException $e) {
    echo "❌ Configuration validation failed: " . $e->getMessage() . "\n";
}

try {
    $invalidConfig = new Configuration([
        'base_url' => 'invalid-url',
        'username' => 'test',
        'timeout' => -5
    ]);
    $invalidConfig->validate();
    echo "✅ This should not appear\n";
} catch (\InvalidArgumentException $e) {
    echo "✅ Invalid configuration caught: " . $e->getMessage() . "\n";
}

echo "\n";

// 6. Configuration constants
echo "6. Available Endpoint Constants\n";
echo "===============================\n\n";

echo "Sandbox URL: " . Configuration::SANDBOX_URL . "\n";
echo "Production URL: " . Configuration::PRODUCTION_URL . "\n\n";

// 7. Best practices summary
echo "7. Configuration Best Practices\n";
echo "===============================\n\n";

echo "✅ Use 'sandbox' boolean for environment switching (recommended)\n";
echo "✅ Use environment variables for different deployment environments\n";
echo "✅ Always validate your configuration before use\n";
echo "✅ Use 'base_url' only for custom endpoints or testing\n";
echo "✅ Enable debug mode during development\n";
echo "✅ Set appropriate timeouts based on your use case\n";
echo "✅ Enable SSL verification in production\n\n";

echo "🎉 Configuration examples completed!\n"; 