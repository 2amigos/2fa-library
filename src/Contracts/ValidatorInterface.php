<?php

/*
 * This file is part of the 2amigos/2fa-library project.
 *
 * (c) 2amigOS! <http://2amigos.us/>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Da\TwoFA\Contracts;

interface ValidatorInterface
{
    /**
     * Validates given value and returns true if valid, false otherwise.
     *
     * @param mixed $value
     *
     * @return bool
     */
    public function validate($value);
}
