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

trait FailReasonTrait
{
    /**
     * @var string|null the fail reason if validation didn't succeed.
     */
    private $failReason;

    /**
     * @return mixed
     */
    public function getFailReason()
    {
        return $this->failReason;
    }

    /**
     * Resets fail reason
     */
    public function resetFailReason()
    {
        $this->failReason = null;
    }
}
