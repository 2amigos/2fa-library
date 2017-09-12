<?php

/*
 * This file is part of the 2amigos/2fa-library project.
 *
 * (c) 2amigOS! <http://2amigos.us/>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Da\TwoFA;

use Da\TwoFA\Contracts\TotpEncoderInterface;
use Da\TwoFA\Traits\SecretValidationTrait;
use Da\TwoFA\Validator\SecretKeyValidator;
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
     */
    public function generateBase32RandomKey($length = 16, $prefix = '')
    {
        $secret = $prefix ? $this->toBase32($prefix) : '';
        $secret = $this->strPadBase32($secret, $length);

        $this->validateSecret($secret);

        return $secret;
    }

    /**
     * @inheritdoc
     */
    public function toBase32($value)
    {
        $encoded = Base32::encodeUpper($value);

        return str_replace('=', '', $encoded);
    }

    /**
     * @inheritdoc
     */
    public function fromBase32($value)
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
     * @return int
     */
    protected function getRandomNumber($from = 0, $to = 31)
    {
        return random_int($from, $to);
    }

    /**
     * Pad string with random base 32 chars.
     *
     * @param $string
     * @param $length
     *
     * @return string
     */
    private function strPadBase32($string, $length)
    {
        for ($i = 0; $i < $length; $i++) {
            $string .= substr('234567QWERTYUIOPASDFGHJKLZXCVBNM', $this->getRandomNumber(), 1);
        }

        return $string;
    }
}
