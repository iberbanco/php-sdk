<?php

declare(strict_types=1);

namespace Iberbanco\SDK\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Iberbanco\SDK\Auth\Authentication;
use Iberbanco\SDK\Exceptions\AuthenticationException;

class AuthenticationTest extends TestCase
{
    private Authentication $auth;

    protected function setUp(): void
    {
        $this->auth = new Authentication('test_user');
    }

    public function testSetAndGetToken(): void
    {
        $token = 'test_token_123';
        $this->auth->setToken($token);
        
        $this->assertEquals($token, $this->auth->getToken());
    }

    public function testGenerateAuthHeaders(): void
    {
        $token = 'test_token_123';
        $timestamp = time();
        $this->auth->setToken($token);
        
        $headers = $this->auth->generateAuthHeaders($token, $timestamp);
        
        $this->assertArrayHasKey('Authorization', $headers);
        $this->assertArrayHasKey('X-Timestamp', $headers);
        $this->assertArrayHasKey('X-Hash', $headers);
        
        $this->assertEquals("Bearer {$token}", $headers['Authorization']);
        $this->assertEquals($timestamp, $headers['X-Timestamp']);
    }

    public function testGenerateAuthHeadersWithoutToken(): void
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Authentication token is required');
        
        $this->auth->generateAuthHeaders();
    }

    public function testGenerateHash(): void
    {
        $token = 'test_token';
        $timestamp = 1234567890;
        $username = 'test_user';
        
        $hash1 = $this->auth->generateHash($token, $timestamp, $username);
        $hash2 = $this->auth->generateHash($token, $timestamp, $username);
        
        // Should generate consistent hashes
        $this->assertEquals($hash1, $hash2);
        
        // Should be a valid hash
        $this->assertIsString($hash1);
        $this->assertEquals(64, strlen($hash1)); // SHA256 hash length
    }

    public function testVerifyHash(): void
    {
        $token = 'test_token';
        $timestamp = 1234567890;
        $username = 'test_user';
        
        $hash = $this->auth->generateHash($token, $timestamp, $username);
        
        $this->assertTrue($this->auth->verifyHash($token, $timestamp, $username, $hash));
        $this->assertFalse($this->auth->verifyHash($token, $timestamp, $username, 'invalid_hash'));
        $this->assertFalse($this->auth->verifyHash('wrong_token', $timestamp, $username, $hash));
    }

    public function testValidateTimestamp(): void
    {
        $now = time();
        
        $this->assertTrue($this->auth->validateTimestamp($now));
        
        $this->assertTrue($this->auth->validateTimestamp($now - 60, 300)); // 1 minute ago, 5 minute tolerance
        
        $this->assertFalse($this->auth->validateTimestamp($now - 400, 300)); // 6.67 minutes ago, 5 minute tolerance
        
        $this->assertFalse($this->auth->validateTimestamp($now + 400, 300));
    }

    public function testSetUsername(): void
    {
        $newUsername = 'new_user';
        $this->auth->setUsername($newUsername);
        
        $token = 'test_token';
        $timestamp = time();
        
        $hash1 = $this->auth->generateHash($token, $timestamp, 'test_user');
        $hash2 = $this->auth->generateHash($token, $timestamp, $newUsername);
        
        $this->assertNotEquals($hash1, $hash2);
    }

    public function testHashConsistency(): void
    {
        $token = 'consistent_token';
        $timestamp = 1609459200; 
        $username = 'consistent_user';
        
        $hash = $this->auth->generateHash($token, $timestamp, $username);
        
        for ($i = 0; $i < 5; $i++) {
            $this->assertEquals($hash, $this->auth->generateHash($token, $timestamp, $username));
        }
    }
} 