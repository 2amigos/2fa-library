<?php

/*
 * This file is part of the 2amigos/2fa-library project.
 *
 * (c) 2amigOS! <http://2amigos.us/>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Da\TwoFA\Support;

use Da\TwoFA\Contracts\TotpEncoderInterface;
use Da\TwoFA\Exception\InvalidSecretKeyException;
use Da\TwoFA\Traits\SecretValidationTrait;
use Da\TwoFA\Validator\SecretKeyValidator;
use Exception;
use ParagonIE\ConstantTime\Base32;

class Encoder implements TotpEncoderInterface
{
    use SecretValidationTrait;

    /**
     * Encoder constructor.
     *
     * @param SecretKeyValidator|null $secretKeyValidator
     */
    public function __construct(SecretKeyValidator $secretKeyValidator = null)
    {
        $this->secretKeyValidator = $secretKeyValidator ?: new SecretKeyValidator();
    }

    /**
     * @inheritdoc
     * @throws InvalidSecretKeyException
     */
    public function generateBase32RandomKey(int $length = 16, string $prefix = ''): string
    {
        $secret = $prefix ? $this->toBase32($prefix) : '';
        $secret = $this->strPadBase32($secret, $length);

        $this->validateSecret($secret);

        return $secret;
    }

    /**
     * @inheritdoc
     */
    public function toBase32(string $value): string
    {
        $encoded = Base32::encodeUpper($value);

        return str_replace('=', '', $encoded);
    }

    /**
     * @inheritdoc
     * @throws InvalidSecretKeyException
     */
    public function fromBase32(string $value): string
    {
        $value = strtoupper($value);

        $this->validateSecret($value);

        return Base32::decodeUpper($value);
    }

    /**
     * Get a random number.
     *
     * @param $from
     * @param $to
     *
     * @throws Exception
     * @return int
     */
    protected function getRandomNumber(int $from = 0, int $to = 31): int
    {
        return random_int($from, $to);
    }

    /**
     * Pad string with random base 32 chars.
     *
     * @param $string
     * @param $length
     *
     * @throws Exception
     * @return string
     */
    private function strPadBase32(string $string, int $length): string
    {
        for ($i = 0; $i < $length; $i++) {
            $string .= substr('234567QWERTYUIOPASDFGHJKLZXCVBNM', $this->getRandomNumber(), 1);
        }

        return $string;
    }
}
