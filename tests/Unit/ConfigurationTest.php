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
        
        $this->assertTrue($config->isSandbox());
        $this->assertEquals('https://sandbox.api.iberbanco.finance/api/v2', $config->getBaseUrl());
        $this->assertEquals(30, $config->getTimeout());
        $this->assertTrue($config->getVerifySSL());
        $this->assertFalse($config->isDebug());
    }

    public function testProductionConfiguration(): void
    {
        $config = new Configuration(['sandbox' => false]);
        
        $this->assertFalse($config->isSandbox());
        $this->assertEquals('http://production.api.iberbancoltd.com/api/v2', $config->getBaseUrl());
    }

    public function testCustomConfiguration(): void
    {
        $config = new Configuration([
            'sandbox' => true,
            'username' => 'test_user',
            'timeout' => 60,
            'verify_ssl' => false,
            'debug' => true
        ]);

        $this->assertEquals('test_user', $config->getUsername());
        $this->assertEquals(60, $config->getTimeout());
        $this->assertFalse($config->getVerifySSL());
        $this->assertTrue($config->isDebug());
    }

    public function testSandboxSwitching(): void
    {
        $config = new Configuration(['sandbox' => true]);
        $this->assertTrue($config->isSandbox());
        $this->assertEquals('https://sandbox.api.iberbanco.finance/api/v2', $config->getBaseUrl());

        $config->setSandbox(false);
        $this->assertFalse($config->isSandbox());
        $this->assertEquals('http://production.api.iberbancoltd.com/api/v2', $config->getBaseUrl());
    }

    public function testValidation(): void
    {
        $config = new Configuration(['sandbox' => true, 'username' => 'test']);
        $this->assertNull($config->validate()); // Should not throw

        $this->expectException(\InvalidArgumentException::class);
        $invalidConfig = new Configuration(['timeout' => 0]);
        $invalidConfig->validate();
    }

    public function testFromEnvironment(): void
    {
        // Set environment variables
        $_ENV['IBERBANCO_SANDBOX'] = 'false';
        $_ENV['IBERBANCO_USERNAME'] = 'env_user';
        $_ENV['IBERBANCO_TIMEOUT'] = '45';

        $config = Configuration::fromEnvironment();

        $this->assertFalse($config->isSandbox());
        $this->assertEquals('env_user', $config->getUsername());
        $this->assertEquals(45, $config->getTimeout());

        // Clean up
        unset($_ENV['IBERBANCO_SANDBOX']);
        unset($_ENV['IBERBANCO_USERNAME']);
        unset($_ENV['IBERBANCO_TIMEOUT']);
    }

    public function testToArray(): void
    {
        $config = new Configuration([
            'sandbox' => false,
            'username' => 'test_user',
            'timeout' => 60
        ]);

        $array = $config->toArray();

        $this->assertFalse($array['sandbox']);
        $this->assertEquals('test_user', $array['username']);
        $this->assertEquals(60, $array['timeout']);
    }
} 