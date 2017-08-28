<?php

/*
 * This file is part of the 2amigos/2fa-library project.
 *
 * (c) 2amigOS! <http://2amigos.us/>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Da\TwoFA\Traits;

use Da\TwoFA\Exception\InvalidSecretKeyException;
use Da\TwoFA\Validator\SecretKeyValidator;

trait SecretValidationTrait
{
    /**
     * @param string $value
     *
     * @throws InvalidSecretKeyException
     */
    public function validateSecret($value)
    {
        if ($this->secretKeyValidator instanceof SecretKeyValidator && !$this->secretKeyValidator->validate($value)) {
            throw new InvalidSecretKeyException($this->secretKeyValidator->getFailReason());
        }
    }
}
