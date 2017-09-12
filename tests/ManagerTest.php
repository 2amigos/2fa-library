<?php

use Da\TwoFA\Exception\InvalidSecretKeyException;
use Da\TwoFA\Manager;

class ManagerTest extends \Codeception\Test\Unit
{
    /**
     * @var Manager
     */
    protected $manager;

    protected function _before()
    {
        $this->manager = new Manager();
    }

    protected function _after()
    {
    }

    public function testIsInitialized()
    {
        $this->assertInstanceOf(Manager::class, $this->manager);
    }

    public function testGeneratesAValidSecretKey()
    {
        $this->assertEquals(16, strlen($this->manager->generateSecretKey()));

        $this->assertEquals(32, strlen($this->manager->generateSecretKey(32)));

        $this->assertStringStartsWith('MFXHI', $this->manager->generateSecretKey(59, 'ant'));

        $this->assertStringStartsWith('MFXHI', $this->manager->generateSecretKey(59, 'ant'));

        $this->assertEquals(
            $key = $this->manager->generateSecretKey(),
            preg_replace('/[^ABCDEFGHIJKLMNOPQRSTUVWXYZ234567]/', '', $key)
        );
    }

    public function testGeneratesAGoogleCompatibleSecretKey()
    {
        $this->manager->disableGoogleAuthenticatorCompatibility()->generateSecretKey(17);

        $this->expectException(InvalidSecretKeyException::class);

        $this->manager->enableGoogleAuthenticatorCompatibility()->generateSecretKey(17);
    }

    public function testCreatesAOneTimePassword()
    {
        $secret = 'ADUMJO5634NPDEKW';

        $this->assertEquals(6, strlen($this->manager->getCurrentOneTimePassword($secret)));
    }

    public function testVerifiesKeys()
    {
        $secret = 'ADUMJO5634NPDEKW';
        $manager = $this->manager->setCycles(2);

        $this->assertTrue($manager->verify('558854', $secret, null, 26213400)); // 26213398
        $this->assertTrue($manager->verify('981084', $secret, null, 26213400)); // 26213399
        $this->assertTrue($manager->verify('512396', $secret, null, 26213400)); // 26213400
        $this->assertTrue($manager->verify('410272', $secret, null, 26213400)); // 26213401
        $this->assertTrue($manager->verify('239815', $secret, null, 26213400)); // 26213402

        $this->assertFalse($manager->verify('313366', $secret, null, 26213400)); // 26213403
        $this->assertFalse($manager->verify('093183', $secret, null, 26213400)); // 26213397
    }

    public function testItVerifiesNewerKeys()
    {
        $secret = 'ADUMJO5634NPDEKW';
        $manager = $this->manager->setCycles(2);

        $this->assertFalse($manager->verify('512396', $secret, 26213401, 26213400));
        $this->assertFalse($manager->verify('410272', $secret, 26213401, 26213400));
        $this->assertEquals(26213402, $manager->verify('239815', $secret, 26213401, 26213400));
        $this->assertFalse($manager->verify('313366', $secret, 26213401, 26213400));

        $this->assertEquals(true, $manager->verify('512396', $secret, null, 26213400));
        $this->assertEquals(true, $manager->verify('410272', $secret, null, 26213400));
        $this->assertEquals(true, $manager->verify('239815', $secret, null, 26213400));
        $this->assertFalse($manager->verify('313366', $secret, null, 26213400));
    }

}
