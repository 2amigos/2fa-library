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

use Da\TwoFA\Contracts\StringGeneratorServiceInterface;

final class GoogleQrCodeUrlGeneratorService implements StringGeneratorServiceInterface
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
     * GoogleQrCodeUrlService constructor.
     *
     * @param string $totpSecreteKeyUri
     * @param int    $size
     */
    public function __construct($totpSecreteKeyUri, $size = 200)
    {
        $this->totpSecretKeyUri = $totpSecreteKeyUri;
        $this->size = $size;
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        return sprintf('https://chart.googleapis.com/%s?%s', rawurlencode('chart'), $this->getQueryParameters());
    }

    /**
     * @return string the constructed query parameters for google charts.
     */
    protected function getQueryParameters()
    {
        return sprintf(
            'chs=%sx%s&chld=M|0&cht=qr&chl=%s',
            $this->size,
            $this->size,
            urlencode($this->totpSecretKeyUri)
        );
    }
}
