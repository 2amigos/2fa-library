2fa library
===========

[![Latest Version](https://img.shields.io/github/tag/2amigos/2fa-library.svg?style=flat-square&label=release)](https://github.com/2amigos/2fa-library/tags)
[![Software License](https://img.shields.io/badge/license-BSD-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/2amigos/2fa-library/master.svg?style=flat-square)](https://travis-ci.org/2amigos/2fa-library)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/2amigos/2fa-library.svg?style=flat-square)](https://scrutinizer-ci.com/g/2amigos/2fa-library/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/2amigos/2fa-library.svg?style=flat-square)](https://scrutinizer-ci.com/g/2amigos/2fa-library)
[![Total Downloads](https://img.shields.io/packagist/dt/2amigos/2fa-library.svg)](https://packagist.org/packages/2amigos/2fa-library) 


This library allows developers to implement Time Based One Time Passwords (TOTP) for the PHP implementation of the 
2factor Authentication (2FA), supporting both the HMAC-based one-time password (HOTP) and the time-based one-time 
passwords (TOTP).

## Getting Started

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require 2amigos/2fa-library:~1.0
```
or add

```json
    "2amigos/2fa-library": "~1.0"
```

## Usage 

### How to generate a secret key

The secret key requires to be saved to later check with the input key from the user to implement the two-factor 
authentication (2FA).

This is how you create a secret key:

```php
use Da\TwoFA\Manager;

$secret = (new Manager())->generateSecretKey();
```

### How to use two-factor authentication 

The first thing you need to do is to generate the secret key for your user before is saved. 

```php 
use Da\TwoFA\Manager;

$manager = new Manager();

$user->twofa_secret = $manager->generateSecretKey();

```

Then, when user chooses to include two-factor authentication as login, you should present a QR Code to the user to scan 
using one of the two-factor authentication apps. There are couple of ways to display it. For example, with data-uri:  

```php
<?php 
use Da\TwoFA\Service\TOTPSecretKeyUriGeneratorService;  
use Da\TwoFA\Service\QrCodeDataUriGeneratorService;

// first we need to create our time-based one time password secret uri
$totpUri = (new TOTPSecretKeyUriGeneratorService('your-company', $user->email, $user->twofa_secret))->run();
$uri = (new QrCodeDataUriGeneratorService($totpUri))->run();
?>


<!-- display it -->
<img src="<?= $uri ?>" alt="" />

```

With google uri: 

```php 
<?php 
use Da\TwoFA\Service\TOTPSecretKeyUriGeneratorService;
use Da\TwoFA\Service\GoogleQrCodeUrlGeneratorService;

// first we need to create our time-based one time password secret uri
$totpUri = (new TOTPSecretKeyUriGeneratorService('your-company', $user->email, $user->twofa_secret))->run();

$googleUri = (new GoogleQrCodeUrlGeneratorService($totpUri))->run();
?>
<!-- display it -->
<img src="<?= $googleUri ?>" alt="" />
```

Or by pointing the `src` attribute of the `img` tag to a script (or action if you use a framework) that will create the 
Qr Code.

When the Qr Code is presented, the user should scan it with their favorite 2FA Authenticator mobile app. Then, to 
verify, after user has logged in with their regular credentials you should present a new screen where user can enter the 
code given by its 2FA Authenticator mobile app: 

```php 
use Da\TwoFA\Manager;

$manager = new Manager();

$valid = $manager->verify($_POST['key'], $user->twofa_secret);

```

### Preventing out of sync clocks 

In order to avoid sync issues between servers, we have the `$cycles` attribute. By default, each cycle of key generation 
is about 30 seconds. We can change that value by modifying the `$counter` attribute. If `$cycles` is a positive number it 
will increase verification times, past and future, from current time. That is, if `$cycles` is set to `1` (value by 
default), it will increase 1 more time the seconds set in `$counter`. If `$counter` is set to `30` then it will be `60`. 
Set this attribute to `0` if you don't care about this sync issue. 

```php 
use Da\TwoFA\Manager;

$manager = new Manager();

$valid = $manager
    ->setCycles(2) // 120 seconds (60 seconds past and future respectively) 
    ->verify($_POST['key'], $user->twofa_secret);

```

Remember that you can also modify the `$counter`, which defaults to `30` seconds, by the `Manager::setCounter` method. 

### Preventing insertion of a previous code

To prevent the usage of a key code more than once (in case someone has seen your code) you can include the latest time 
he was validated. If `null` is provided, the method `verify` will return a boolean value, but if we pass a `timestamp` 
value, it will return a `timestamp` value that we can safe for future verifications. 


```php 
use Da\TwoFA\Manager;

$manager = new Manager();

$previousTs = $user->twofa_timestamp ? : $manager->getTimestamp();

$timestamp = $manager->verify($_POST['key'], $user->twofa_secret, $previousTs);

if($timestamp) { 
    // ... success, update user timestamp
    $user->twofa_timestamp = $timestamp; 
    $user->save();
} else {
    // ... error
}

```

### Bigger keys & prefixing secret keys

You can harden the collision probabilities of the random string by incrementing the length of the secret keys: 

```php 
use Da\TwoFA\Manager;

$manager = new Manager();

$user->twofa_secret = $manager->generateSecretKey(32); 

```

By prefixing keys, we need to remember that all keys must have length in power of 2. So, if the key is 16 bytes long, 
your prefix string must be also 16 bytes long. But as the prefixes will be converted to `Base32`, the maximum length of 
that prefix is 10 bytes. Therefore, the sizes of your prefixes would be: `1, 2, 5, 10, 20, 40, 80 ...`, and it can be 
used like: 

```php
 
use Da\TwoFA\Manager;

$manager = new Manager();

$prefix = strpad($id, 10, 'X');

$secretKey = $manager->generateSecretKey(16, $prefix);

```

### Google Authenticator compatibility 

To be compatible with Google Authenticator, your (converted to base 32) secret key length must be at least 8 chars and 
be a power of 2: 8, 16, 32, 64... This is why the library forces you to create the  secret key throughout its 
`Manager::generateSecretKey()` or `Encoder::generateBase32RandomKey()` methods. You should only be concerned about the 
prefix as stated previously.

By default, the library enforces google compatibility, but if Google Authenticator is not a target, you can disable it 
like this: 

```php
<?php 
use Da\TwoFA\Manager;
use Da\TwoFA\Validator\SecretKeyValidator;

$validator = new SecretKeyValidator(false);
$manager = new Manager(false);
?>
```

or: 

```php
<?php 
use Da\TwoFA\Manager;

$manager = (new Manager())->disableGoogleAuthenticatorCompatibility();
?>
```

### IMPORTANT: Server Time

It's very important to keep the server time in sync with an NTP server. 

### Links 

- [How to sync your linux server time with network time servers (NTP)](https://www.howtogeek.com/tips/how-to-sync-your-linux-server-time-with-network-time-servers-ntp/)
- [Use NTP to sync time](https://support.rackspace.com/how-to/using-ntp-to-sync-time/)
- [Sync linux server time with network time protocol (NTP) servers](http://blog.admindiary.com/sync-linux-server-ntp-servers/)
- [Configure Window Server 2008/2012 to sync with Internet time servers](https://nefaria.com/2013/03/configure-windows-server-20082012-to-sync-with-internet-time-servers/)

## Credits 

This library is highly inspired by the excellent work of Antonio Ribeiro. This library is a different approach of what 
he did, and therefore deserves all the credit of this one. The reason to create our very own version, was no other that 
to have full control of updates and fixes and provide a different version of what he did, as we are going to implement 
it on our other library [yii-usuario](https://www.github.com/2amigos/yii2-usuario).

If you wish to check his library, please visit: 

-  [https://github.com/antonioribeiro/google2fa]()https://github.com/antonioribeiro/google2fa) 





© [2amigos](http://www.2amigos.us/) 2013-2017
