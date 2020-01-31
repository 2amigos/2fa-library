<?php

/*
 * This file is part of the 2amigos/2fa-library project.
 *
 * (c) 2amigOS! <http://2amigos.us/>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Da\TwoFA\Service;

use Da\QrCode\Contracts\ErrorCorrectionLevelInterface;
use Da\QrCode\QrCode;
use Da\TwoFA\Contracts\StringGeneratorServiceInterface;

class QrCodeDataUriGeneratorService implements StringGeneratorServiceInterface
{
    /**
     * @var string a totp secret key uri generated string.
     */
    private $totpSecretKeyUri;
    /**
     * @var int the size of the qr code. Recommended size is 200 for readability.
     */
    private $size;

    /**
     * QrCodeDataUriGeneratorService constructor.
     *
     * @param string $totpSecreteKeyUri
     * @param int    $size
     */
    public function __construct(string $totpSecreteKeyUri, int $size = 200)
    {
        $this->totpSecretKeyUri = $totpSecreteKeyUri;
        $this->size = $size;
    }

    /**
     * @inheritdoc
     */
    public function run(): string
    {
        return (new QrCode($this->totpSecretKeyUri, ErrorCorrectionLevelInterface::MEDIUM))
            ->setSize((int)$this->size)
            ->writeDataUri();
    }
}
