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

// 3. Production Configuration
echo "🚀 Production Configuration...\n";
$productionClient = IberbancoClient::create([
    'sandbox' => false, // Production environment
    'username' => 'your_agent_username',
    'timeout' => 45,
    'verify_ssl' => true, // Always true for production
    'debug' => false
]);

echo "✅ Production client created\n";
echo "🔗 Endpoint: " . $productionClient->getConfig()->getBaseUrl() . "\n\n";

// 2. Environment-based configuration
echo "2. Environment Variable Configuration\n";
echo "====================================\n\n";

// Set environment variables (in real usage, these would be set in your environment)
putenv('IBERBANCO_SANDBOX=true');
putenv('IBERBANCO_USERNAME=your_agent_username');
putenv('IBERBANCO_PASSWORD=your_agent_password');
putenv('IBERBANCO_TIMEOUT=45');
putenv('IBERBANCO_VERIFY_SSL=true');
putenv('IBERBANCO_DEBUG=false');

$envClient = IberbancoClient::createFromEnvironment();
echo "✅ Environment client created with sandbox: " . ($envClient->getConfig()->isSandbox() ? 'true' : 'false') . "\n";
echo "✅ Environment client endpoint: " . $envClient->getConfig()->getBaseUrl() . "\n";

// Demonstrate authentication using environment variables
echo "🔐 Authentication using environment variables:\n";
echo "   Username: " . ($_ENV['IBERBANCO_USERNAME'] ?? 'not_set') . "\n";
echo "   Password: " . (isset($_ENV['IBERBANCO_PASSWORD']) ? '***hidden***' : 'not_set') . "\n";
echo "   Note: Call \$client->authenticate(\$_ENV['IBERBANCO_USERNAME'], \$_ENV['IBERBANCO_PASSWORD'])\n\n";

// 4. Dynamic configuration switching
echo "🔄 Dynamic Configuration Switching...\n";
$dynamicClient = IberbancoClient::create([
    'sandbox' => true,
    'username' => 'your_agent_username'
]);

echo "Current environment: " . ($dynamicClient->getConfig()->isSandbox() ? 'Sandbox' : 'Production') . "\n";

// Switch to production
$dynamicClient->updateConfig(['sandbox' => false]);
echo "Switched to: " . ($dynamicClient->getConfig()->isSandbox() ? 'Sandbox' : 'Production') . "\n\n";

// 5. Error handling for invalid configuration
echo "⚠️  Testing Error Handling...\n";
try {
    $invalidClient = IberbancoClient::create([
        'timeout' => -1, // Invalid timeout
        'username' => 'test'
    ]);
} catch (\InvalidArgumentException $e) {
    echo "❌ Caught expected error: " . $e->getMessage() . "\n";
}

echo "\n✅ Configuration validation working properly\n\n";

echo "📋 Summary:\n";
echo "✅ Use 'sandbox' boolean for environment switching\n";
echo "✅ Set sandbox=true for testing, false for production\n";
echo "✅ SDK automatically handles endpoint selection\n";

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
echo "✅ Enable debug mode during development\n";
echo "✅ Set appropriate timeouts based on your use case\n";
echo "✅ Enable SSL verification in production\n\n";

echo "🎉 Configuration examples completed!\n"; 