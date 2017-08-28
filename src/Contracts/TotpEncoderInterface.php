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

use Da\TwoFA\Exception\InvalidCharactersException;

interface TotpEncoderInterface
{
    /**
     * Generate a digit secret key in base32 format.
     *
     * @param int    $length
     * @param string $prefix
     *
     * @return string
     */
    public function generateBase32RandomKey($length = 16, $prefix = '');

    /**
     * Encode a string to Base32.
     *
     * @param string $value
     *
     * @return string
     */
    public function toBase32($value);

    /**
     * Decodes a base32 string into a binary string.
     *
     * @param string $value
     *
     * @throws InvalidCharactersException
     *
     * @return string
     */
    public function fromBase32($value);
}
