<?php
namespace Da\TwoFA\Tests;

use Da\TwoFA\Support\Encoder;
use Da\TwoFA\Validator\SecretKeyValidator;
use PHPUnit\Framework\TestCase;

class EncoderTest extends TestCase
{
    /**
     * @var Encoder
     */
    protected $encoder;

    protected function setUp()
    {
        $this->encoder = new Encoder();
    }


    public function testDecodesBase32Strings()
    {
        $secret = 'ADUMJO5634NPDEKW';

        $result = chr(0)
            . chr(232)
            . chr(196)
            . chr(187)
            . chr(190)
            . chr(223)
            . chr(26)
            . chr(241)
            . chr(145)
            . chr(86);

        $this->assertEquals($result, $this->encoder->fromBase32($secret));
    }

    public function testConvertsInvalidCharsToBase32()
    {
        $converted = $this->encoder->generateBase32RandomKey(
            16,
            '1234' . chr(250) . chr(251) . chr(252) . chr(253) . chr(254) . chr(255)
        );

        $valid = preg_replace('/[^ABCDEFGHIJKLMNOPQRSTUVWXYZ234567]/', '', $converted);

        $this->assertEquals($valid, $converted);

    }

    public function testConvertsToBase32()
    {
        $this->assertEquals('GJQW22LHN5ZQ', $this->encoder->toBase32('2amigos'));
    }

    public function testDecodesBase32()
    {
        $encoder = new Encoder(new SecretKeyValidator(false));
        $this->assertEquals('2amigos', $encoder->fromBase32('GJQW22LHN5ZQ'));
    }

    public function testExceptionWhenIncompatibleGoogleKeyIsGivenToDecodeBase32String()
    {
        $this->expectException(\Da\TwoFA\Exception\InvalidSecretKeyException::class);
        $this->expectExceptionMessage('Google incompatible secret key.');

        $this->assertEquals('2amigos', $this->encoder->fromBase32('GJQW22LHN5ZQ'));

    }
}
