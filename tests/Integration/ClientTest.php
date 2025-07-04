<?php

declare(strict_types=1);

namespace Iberbanco\SDK\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Iberbanco\SDK\IberbancoClient;
use Iberbanco\SDK\Config\Configuration;
use TestConfig;

class ClientTest extends TestCase
{
    private IberbancoClient $client;

    protected function setUp(): void
    {
        $config = new Configuration(TestConfig::getConfig());
        $mockHttpClient = TestConfig::getMockHttpClient();
        
        $this->client = new IberbancoClient($config, $mockHttpClient);
    }

    public function testClientCreation(): void
    {
        $this->assertInstanceOf(IberbancoClient::class, $this->client);
        $this->assertFalse($this->client->isAuthenticated());
    }

    public function testCreateFromArray(): void
    {
        $client = IberbancoClient::create(TestConfig::getConfig());
        $this->assertInstanceOf(IberbancoClient::class, $client);
    }

    public function testServiceAccess(): void
    {
        $this->assertInstanceOf(\Iberbanco\SDK\Services\AuthService::class, $this->client->auth());
        $this->assertInstanceOf(\Iberbanco\SDK\Services\UserService::class, $this->client->users());
        $this->assertInstanceOf(\Iberbanco\SDK\Services\AccountService::class, $this->client->accounts());
        $this->assertInstanceOf(\Iberbanco\SDK\Services\TransactionService::class, $this->client->transactions());
        $this->assertInstanceOf(\Iberbanco\SDK\Services\CryptoTransactionService::class, $this->client->cryptoTransactions());
        $this->assertInstanceOf(\Iberbanco\SDK\Services\CardService::class, $this->client->cards());
        $this->assertInstanceOf(\Iberbanco\SDK\Services\ExportService::class, $this->client->export());
        $this->assertInstanceOf(\Iberbanco\SDK\Services\ExchangeService::class, $this->client->exchange());
    }

    public function testTokenManagement(): void
    {
        $token = 'test_token_123';
        
        $this->assertNull($this->client->getAuthToken());
        $this->assertFalse($this->client->isAuthenticated());
        
        $this->client->setAuthToken($token);
        
        $this->assertEquals($token, $this->client->getAuthToken());
        $this->assertTrue($this->client->isAuthenticated());
    }

    public function testConfigurationUpdate(): void
    {
        $this->assertTrue($this->client->getConfig()->isSandbox());
        
        $this->client->updateConfig(['sandbox' => false]);
        
        $this->assertFalse($this->client->getConfig()->isSandbox());
        $this->assertEquals('http://production.api.iberbancoltd.com/api/v2', $this->client->getConfig()->getBaseUrl());
    }

    public function testDebugMode(): void
    {
        $this->assertFalse($this->client->getConfig()->isDebug());
        
        $this->client->enableDebug();
        $this->assertTrue($this->client->getConfig()->isDebug());
        
        $this->client->disableDebug();
        $this->assertFalse($this->client->getConfig()->isDebug());
    }

    public function testSdkInfo(): void
    {
        $info = IberbancoClient::getInfo();
        
        $this->assertIsArray($info);
        $this->assertArrayHasKey('name', $info);
        $this->assertArrayHasKey('version', $info);
        $this->assertArrayHasKey('description', $info);
        $this->assertEquals('Iberbanco PHP SDK', $info['name']);
        $this->assertEquals('1.0.0', $info['version']);
    }

    public function testAuthenticationWorkflow(): void
    {
        $mockHttpClient = $this->client->getHttpClient();
        
        $mockHttpClient->setResponse('POST', 'auth', [
            'status' => 'success',
            'message' => 'Authentication successful',
            'data' => [
                'token' => 'mock_token_12345',
                'agent' => [
                    'username' => 'test_agent',
                    'permissions' => ['users:read', 'accounts:read']
                ]
            ]
        ]);
        
        $response = $this->client->authenticate('test_agent', 'test_password');
        
        $this->assertEquals('success', $response['status']);
        $this->assertEquals('mock_token_12345', $this->client->getAuthToken());
        $this->assertTrue($this->client->isAuthenticated());
        
        $requests = $mockHttpClient->getRequests();
        $this->assertCount(1, $requests);
        $this->assertEquals('POST', $requests[0]['method']);
        $this->assertEquals('auth', $requests[0]['uri']);
        $this->assertEquals('test_agent', $requests[0]['data']['username']);
        $this->assertEquals('test_password', $requests[0]['data']['password']);
    }

    public function testServiceSingleton(): void
    {
        // Services should be singletons - same instance returned each time
        $authService1 = $this->client->auth();
        $authService2 = $this->client->auth();
        
        $this->assertSame($authService1, $authService2);
        
        $userService1 = $this->client->users();
        $userService2 = $this->client->users();
        
        $this->assertSame($userService1, $userService2);
    }
} 