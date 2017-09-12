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

trait OathTrait
{
    /**
     * @var int the length of the time based one time password token generated. Defaults to 6.
     */
    protected $tokenLength = 6;

    /**
     * Takes the secret key and the timestamp and returns the one time password.
     *
     * @param string $seed    the secret key in binary form
     * @param string $counter the time as returned by getTimestamp
     *
     * @throws InvalidSecretKeyException
     * @return string
     */
    protected function oathHotp($seed, $counter)
    {
        // Counter must be 64-bit int
        $bin_counter = pack('N*', 0, $counter);
        $hash = hash_hmac('sha1', $bin_counter, $seed, true);

        return str_pad($this->oathTruncate($hash), $this->tokenLength, '0', STR_PAD_LEFT);
    }

    /**
     * Extracts the OTP from the SHA1 hash.
     *
     * @param string $hash
     *
     * @return int
     **/
    protected function oathTruncate($hash)
    {
        $offset = ord($hash[19]) & 0xf;
        $temp = unpack('N', substr($hash, $offset, 4));

        return substr($temp[1] & 0x7fffffff, -$this->tokenLength);
    }
}
