# Envoyer Sendgrid driver

Sendgrid adapter provides a `drewlabs/envoyer` implmentation that uses `sendgrid` HTTP based API internally for sending mail messages.

## Usage

```php
use Drewlabs\Envoyer\Drivers\Sendgrid\SendgridAdapter;
use Drewlabs\Envoyer\Mail;

$config = require __DIR__.'/config.php';

// Build email
$mail = Mail::new()->from($config['email'])
    ->to('...')
    ->subject('...')
    ->attach(new SplFileInfo(__DIR__.'/contents/bordereau.pdf'))
    ->content('<p>...</p>');

// Create mail adapter
$adapter = SendgridAdapter::new($config['apikey']);

// Send mail request
$result = $adapter->sendRequest($mail);
```
