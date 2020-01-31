<?php

/*
 * This file is part of the 2amigos/2fa-library project.
 *
 * (c) 2amigOS! <http://2amigos.us/>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Da\TwoFA\Validator;

use Da\TwoFA\Contracts\ValidatorInterface;
use Da\TwoFA\Exception\GoogleAuthenticatorCompatibilityException;
use Da\TwoFA\Exception\InvalidCharactersException;
use Da\TwoFA\Traits\FailReasonTrait;

class SecretKeyValidator implements ValidatorInterface
{
    use FailReasonTrait;

    /**
     * Enforce Google Authenticator compatibility.
     */
    protected $googleAuthenticatorCompatibility;

    /**
     * Encoder constructor.
     *
     * @param bool $enforceGoogleAuthenticatorCompatibility
     */
    public function __construct(bool $enforceGoogleAuthenticatorCompatibility = true)
    {
        $this->enforceGoogleAuthenticatorCompatibility($enforceGoogleAuthenticatorCompatibility);
    }

    /**
     * @param bool $enforce
     */
    public function enforceGoogleAuthenticatorCompatibility(bool $enforce): void
    {
        $this->googleAuthenticatorCompatibility = $enforce;
    }

    /**
     * @return bool
     */
    public function isGoogleAuthenticatorCompatibilityEnforced(): bool
    {
        return $this->googleAuthenticatorCompatibility;
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    public function validate($value): bool
    {
        try {
            $this->resetFailReason();
            $this->checkForValidCharacters($value);
            $this->checkGoogleAuthenticatorCompatibility($value);
        } catch (InvalidCharactersException $e) {
            $this->failReason = 'Secret key contains invalid characters.';
        } catch (GoogleAuthenticatorCompatibilityException $e) {
            $this->failReason = 'Google incompatible secret key.';
        }

        return null === $this->failReason;
    }

    /**
     * Check if the secret key is compatible with Google Authenticator.
     *
     * @param string $value
     *
     * @throws GoogleAuthenticatorCompatibilityException
     */
    protected function checkGoogleAuthenticatorCompatibility(string $value): void
    {
        if (
            $this->isGoogleAuthenticatorCompatibilityEnforced()
            && !(new GoogleAuthenticationCompatibilityValidator())->validate($value)
        ) {
            throw new GoogleAuthenticatorCompatibilityException('Not google compatible key');
        }
    }

    /**
     * Check if all secret key characters are valid.
     *
     * @param string $value
     *
     * @throws InvalidCharactersException
     */
    protected function checkForValidCharacters(string $value): void
    {
        if (!(new CharactersValidator())->validate($value)) {
            throw new InvalidCharactersException('Invalid secret value');
        }
    }
}
