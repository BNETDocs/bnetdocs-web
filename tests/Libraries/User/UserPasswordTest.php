<?php

namespace BNETDocs\Tests\Libraries\User;

use \BNETDocs\Libraries\Core\Config;
use \BNETDocs\Libraries\User\User;
use \PHPUnit\Framework\TestCase;
use \ReflectionProperty;

class UserPasswordTest extends TestCase
{
    private const PASSWORD = 'correct horse battery staple';
    private const PEPPER = 'test-pepper';

    private ReflectionProperty $json_config;
    private mixed $saved_json_config;
    private User $user;

    protected function setUp(): void
    {
        // Stub the JSON config so Config::get() never touches the filesystem or database.
        $this->json_config = new ReflectionProperty(Config::class, 'json_config');
        $this->saved_json_config = $this->json_config->getValue();
        $this->setBcryptCost(12);

        // null id → allocate() returns true early; no database access.
        $this->user = new User(null);
    }

    protected function tearDown(): void
    {
        $this->json_config->setValue(null, $this->saved_json_config);
    }

    private function setBcryptCost(int $cost): void
    {
        $this->json_config->setValue(null, ['bnetdocs' => [
            'user_password_bcrypt_cost' => $cost,
            'user_password_pepper' => self::PEPPER,
        ]]);
    }

    public function testCheckPasswordAcceptsCurrentCost(): void
    {
        $this->user->setPassword(self::PASSWORD);
        $this->assertTrue($this->user->checkPassword(self::PASSWORD));
        $this->assertFalse($this->user->needsPasswordRehash());
    }

    public function testCheckPasswordRejectsWrongPassword(): void
    {
        $this->user->setPassword(self::PASSWORD);
        $this->assertFalse($this->user->checkPassword('wrong password'));
    }

    // Regression: checkPassword() returned false for any hash that needed a rehash, so raising
    // the configured bcrypt cost (or PHP's default cost changing) rejected every correct password.
    public function testCheckPasswordAcceptsOutdatedCost(): void
    {
        $this->user->setPasswordHash(\password_hash(self::PASSWORD, \PASSWORD_BCRYPT, ['cost' => 10]));
        $this->assertTrue($this->user->checkPassword(self::PASSWORD));
        $this->assertTrue($this->user->needsPasswordRehash());
    }

    public function testCheckPasswordAcceptsLegacySha256(): void
    {
        $salt = 'abc123';
        $this->user->setPasswordHash(\strtoupper(\hash('sha256', self::PASSWORD . $salt . self::PEPPER)));
        $this->user->setPasswordSalt($salt);
        $this->assertTrue($this->user->checkPassword(self::PASSWORD));
        $this->assertTrue($this->user->needsPasswordRehash());
    }

    public function testNoPasswordNeverMatchesOrNeedsRehash(): void
    {
        $this->assertFalse($this->user->checkPassword(self::PASSWORD));
        $this->assertFalse($this->user->needsPasswordRehash());
    }

    public function testSetPasswordUsesConfiguredCost(): void
    {
        $this->setBcryptCost(11);
        $this->user->setPassword(self::PASSWORD);
        $this->assertSame(11, \password_get_info($this->user->getPasswordHash())['options']['cost']);
    }
}
