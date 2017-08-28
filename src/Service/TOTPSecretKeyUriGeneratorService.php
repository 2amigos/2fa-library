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

/**
 * Class SecretKeyUriService
 *
 * @author Antonio Ramirez <hola@2amigos.us>
 * @package Da\TwoFA\Service
 *
 * @see https://github.com/google/google-authenticator/wiki/Key-Uri-Format
 */
final class TOTPSecretKeyUriGeneratorService implements StringGeneratorServiceInterface
{
    /**
     * @var string part of the compound label. Company (or site) issuing the TOTP. Will be used as the issuer too.
     * @see https://github.com/google/google-authenticator/wiki/Key-Uri-Format#issuer
     */
    private $company;
    /**
     * @var string part of the compound label. Holder could be a user's secret owner.
     */
    private $holder;
    /**
     * @var string an arbitrary key value encoded in Base32 according to RFC 3548
     * @see http://tools.ietf.org/html/rfc3548
     */
    private $secret;

    /**
     * GoogleQrCodeUrlService constructor.
     *
     * @param $company
     * @param $holder
     * @param $secret
     */
    public function __construct($company, $holder, $secret)
    {
        $this->company = $company;
        $this->holder = $holder;
        $this->secret = $secret;
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        return sprintf(
            'otpauth://totp/%s:%s?secret=%s&issuer=%s',
            rawurlencode($this->company),
            rawurlencode($this->holder),
            $this->secret,
            rawurlencode($this->company)
        );
    }
}
