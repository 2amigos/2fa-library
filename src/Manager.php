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
use Da\TwoFA\Exception\InvalidSecretKeyException;
use Da\TwoFA\Traits\OathTrait;
use Da\TwoFA\Traits\SecretValidationTrait;
use Da\TwoFA\Validator\OneTimePasswordValidator;
use Da\TwoFA\Validator\SecretKeyValidator;

class Manager
{
    use SecretValidationTrait;
    use OathTrait;

    /**
     * @var int the period parameter that defines the time (in seconds) the OTP token will be valid. Default is 30 for
     *          Google Authenticator.
     */
    protected $counter = 30;
    /**
     * @var int the times in 30 second cycles to avoid slight out of sync code verification. Setting this to 1 cycle
     *          will be valid for 60 seconds.
     */
    protected $cycles = 1;
    /**
     * @var TotpEncoderInterface
     */
    protected $encoder;

    /**
     * Auth constructor.
     *
     * @param SecretKeyValidator|null   $secretKeyValidator
     * @param TotpEncoderInterface|null $encoder
     */
    public function __construct(SecretKeyValidator $secretKeyValidator = null, TotpEncoderInterface $encoder = null)
    {
        $this->secretKeyValidator = $secretKeyValidator ?: new SecretKeyValidator();
        $this->encoder = $encoder ?: new Encoder();
    }

    /**
     * @return Manager
     */
    public function disableGoogleAuthenticatorCompatibility()
    {
        $cloned = clone $this;
        $cloned->secretKeyValidator = new SecretKeyValidator(false);
        $cloned->encoder = new Encoder($cloned->secretKeyValidator);

        return $cloned;
    }

    /**
     * @return $this|Manager
     */
    public function enableGoogleAuthenticatorCompatibility()
    {
        if (!$this->secretKeyValidator->isGoogleAuthenticatorCompatibilityEnforced()) {
            $cloned = clone $this;
            $cloned->secretKeyValidator = new SecretKeyValidator();
            $cloned->encoder = new Encoder($cloned->secretKeyValidator);

            return $cloned;
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getTokenLength()
    {
        return $this->tokenLength;
    }

    /**
     * @param int $tokenLength
     *
     * @return Manager
     */
    public function setTokenLength($tokenLength)
    {
        $cloned = clone $this;
        $cloned->tokenLength = $tokenLength;

        return $cloned;
    }

    /**
     * Wrapper function to Encoder::generateBase32RandomKey method.
     *
     * @param int    $length
     * @param string $prefix
     *
     * @return mixed|string
     */
    public function generateSecretKey($length = 16, $prefix = '')
    {
        return $this->encoder->generateBase32RandomKey($length, $prefix);
    }

    /**
     * @param int $value
     *
     * @return Manager
     */
    public function setCycles($value)
    {
        $cloned = clone $this;
        $cloned->cycles = $value;

        return $cloned;
    }

    /**
     * @return int
     */
    public function getCycles()
    {
        return (int)$this->cycles;
    }

    /**
     * @param $value
     *
     * @return Manager
     */
    public function setCounter($value)
    {
        $cloned = clone $this;
        $cloned->counter = (int)$value;

        return $cloned;
    }

    /**
     * @return int
     */
    public function getCounter()
    {
        return (int)$this->counter;
    }

    /**
     * Returns the current Unix Timestamp divided by the $counter period.
     *
     * @return int
     **/
    public function getTimestamp()
    {
        return (int)floor(microtime(true) / $this->getCounter());
    }

    /**
     * Get the current one time password for a base32 encoded secret.
     *
     * @param string $secret
     *
     * @return string
     */
    public function getCurrentOneTimePassword($secret)
    {
        $timestamp = $this->getTimestamp();
        $secret = $this->encoder->fromBase32($secret);

        return $this->oathHotp($secret, $timestamp);
    }

    /**
     * Verifies user's key vs current timestamp.
     *
     * @param string $key          the user's input key
     * @param string $secret       the secret used to
     * @param null   $previousTime
     * @param null   $time
     *
     * @throws InvalidSecretKeyException
     * @return bool|int
     */
    public function verify($key, $secret, $previousTime = null, $time = null)
    {
        $time = $time ? (int)$time : $this->getTimestamp();
        $cycles = $this->getCycles();
        $startTime = null === $previousTime
            ? $time - $cycles
            : max($time - $cycles, $previousTime + 1);

        $seed = $this->encoder->fromBase32($secret);

        if (strlen($seed) < 8) {
            throw new InvalidSecretKeyException('Secret key is too short');
        }

        return $this->validateOneTimePassword($key, $seed, $startTime, $time, $previousTime);
    }

    /**
     * Validates the key (OTP) and returns true if valid, false otherwise.
     *
     * @param string   $key
     * @param string   $seed
     * @param int      $startTime
     * @param int      $time
     * @param int|null $previousTime
     *
     * @return bool
     */
    protected function validateOneTimePassword($key, $seed, $startTime, $time, $previousTime = null)
    {
        return (new OneTimePasswordValidator(
            $seed,
            $this->getCycles(),
            $this->getTokenLength(),
            $startTime,
            $time,
            $previousTime
        ))
            ->validate($key);
    }
}
