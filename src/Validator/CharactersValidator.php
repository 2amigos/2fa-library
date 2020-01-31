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

class CharactersValidator implements ValidatorInterface
{
    /**
     * @inheritdoc
     */
    public function validate($value): bool
    {
        return (bool)preg_match('/^[ABCDEFGHIJKLMNOPQRSTUVWXYZ234567]+$/', $value);
    }
}
