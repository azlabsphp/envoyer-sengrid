<?php

declare(strict_types=1);

/*
 * This file is part of the drewlabs namespace.
 *
 * (c) Sidoine Azandrew <azandrewdevelopper@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Drewlabs\Envoyer\Contracts\NotificationResult;
use Drewlabs\Envoyer\Drivers\Sendgrid\SendgridAdapter;
use Drewlabs\Envoyer\Mail;
use PHPUnit\Framework\TestCase;

class SendgridAdapterTest extends TestCase
{
    public function test_sendgrid_adapter_send_request()
    {
        $config = require __DIR__.'/contents/config.php';

        // Build email
        $mail = Mail::new()->from($config['email'])
            ->to('asmyns.platonnas29@gmail.com')
            ->subject('BORDERAU DE VIREMENTS')
            ->attach(new SplFileInfo(__DIR__.'/contents/bordereau.pdf'))
            ->content('<p>Voici joint le fichier du bordereau de virement</p>');

        // Create mail adapter
        $adapter = SendgridAdapter::new($config['apikey']);

        // Send mail request
        $result = $adapter->sendRequest($mail);

        $this->assertInstanceOf(NotificationResult::class, $result);
    }
}
