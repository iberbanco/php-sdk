<?php

declare(strict_types=1);

namespace Iberbanco\SDK\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Iberbanco\SDK\Config\Configuration;

class ConfigurationTest extends TestCase
{
    public function testDefaultConfiguration(): void
    {
        $config = new Configuration();
        
        $this->assertEquals('https://sandbox.api.iberbanco.finance/v2', $config->getBaseUrl());
        $this->assertEquals('', $config->getUsername());
        $this->assertEquals(30, $config->getTimeout());
        $this->assertTrue($config->getVerifySSL());
        $this->assertFalse($config->isDebug());
    }

    public function testCustomConfiguration(): void
    {
        $configData = [
            'base_url' => 'https://api.iberbanco.finance/v2',
            'username' => 'test_user',
            'timeout' => 60,
            'verify_ssl' => false,
            'debug' => true,
            'headers' => ['User-Agent' => 'TestAgent/1.0']
        ];

        $config = new Configuration($configData);
        
        $this->assertEquals('https://api.iberbanco.finance/v2', $config->getBaseUrl());
        $this->assertEquals('test_user', $config->getUsername());
        $this->assertEquals(60, $config->getTimeout());
        $this->assertFalse($config->getVerifySSL());
        $this->assertTrue($config->isDebug());
        $this->assertArrayHasKey('User-Agent', $config->getDefaultHeaders());
    }

    public function testSetters(): void
    {
        $config = new Configuration();
        
        $config->setBaseUrl('https://custom.api.com/v2');
        $this->assertEquals('https://custom.api.com/v2', $config->getBaseUrl());
        
        $config->setUsername('new_user');
        $this->assertEquals('new_user', $config->getUsername());
        
        $config->setTimeout(45);
        $this->assertEquals(45, $config->getTimeout());
        
        $config->setVerifySSL(false);
        $this->assertFalse($config->getVerifySSL());
        
        $config->setDebug(true);
        $this->assertTrue($config->isDebug());
        
        $config->setDefaultHeaders(['Custom' => 'Header']);
        $this->assertEquals(['Custom' => 'Header'], $config->getDefaultHeaders());
    }

    public function testValidation(): void
    {
        $config = new Configuration([
            'username' => 'test_user'
        ]);
        
        $this->expectNotToPerformAssertions();
        $config->validate();
    }

    public function testValidationFailsWithEmptyUsername(): void
    {
        $config = new Configuration([
            'username' => ''
        ]);
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Username is required');
        $config->validate();
    }

    public function testFromEnvironment(): void
    {
        // Mock environment variables
        $originalUsername = $_ENV['IBERBANCO_USERNAME'] ?? null;
        $originalBaseUrl = $_ENV['IBERBANCO_BASE_URL'] ?? null;
        
        $_ENV['IBERBANCO_USERNAME'] = 'env_user';
        $_ENV['IBERBANCO_BASE_URL'] = 'https://env.api.com/v2';
        
        $config = Configuration::fromEnvironment();
        
        $this->assertEquals('env_user', $config->getUsername());
        $this->assertEquals('https://env.api.com/v2', $config->getBaseUrl());
        
        // Restore original values
        if ($originalUsername !== null) {
            $_ENV['IBERBANCO_USERNAME'] = $originalUsername;
        } else {
            unset($_ENV['IBERBANCO_USERNAME']);
        }
        
        if ($originalBaseUrl !== null) {
            $_ENV['IBERBANCO_BASE_URL'] = $originalBaseUrl;
        } else {
            unset($_ENV['IBERBANCO_BASE_URL']);
        }
    }
} 