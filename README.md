# Msgraph Mailer

[![Latest Version on Packagist](https://img.shields.io/packagist/v/jornatf/msgraph-mailer.svg?style=flat-square)](https://packagist.org/packages/jornatf/msgraph-mailer)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/jornatf/msgraph-mailer/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/jornatf/msgraph-mailer/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/jornatf/msgraph-mailer/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/jornatf/msgraph-mailer/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/jornatf/msgraph-mailer.svg?style=flat-square)](https://packagist.org/packages/jornatf/msgraph-mailer)

A Laravel Package to send emails with [Microsoft Graph API](https://learn.microsoft.com/en-us/graph/use-the-api).

> #### If you like this package you can [Buy me a Coffee](https://www.buymeacoffee.com/jornatf) ☕️

## Installation

### Via composer:

```bash
composer require jornatf/msgraph-mailer
```

### Prerequisites:

Add your [Microsoft Graph](https://learn.microsoft.com/en-us/graph/overview) credentials in the `.env` file:

```
MSGRAPH_CLIENT_ID=your_client_id
MSGRAPH_SECRET_ID=your_secret_id
MSGRAPH_TENANT_ID=your_tenant_id
```

## Usage

### Example:

> This example shows you how to use the basic methods required to send an email.

```php
<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

class PostController extends Controller
{
    public function sendMail()
    {
        try {
            $msgraph = MsGraphMailer::mail('your@mailbox.com')
                ->to(['John Doe:john.doe@example.com', 'other@example.com', $otherRecipients])
                ->subject('A simple email')
                ->body('<h1>Send a simple email to one recipient.</h1>')
                ->send();
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return "Email sended!";
    }
}
```

### Available methods:

```php
<?php

// First, instantiate response with your Msgraph mailbox (required):
$msgraph = MsGraphMailer::mail(string $mailbox);

// Add a main recipient(s), a subject and a content (required):
$msgraph->to(array $toRecipient);
$msgraph->subject(string $subject);
$msgraph->content(string $contentHTML);

// You can add Cc and/or bcc with the same way (optionally):
$msgraph->cc(array $ccRecipient);
$msgraph->bcc(array $bccRecipient);

// Optionally, you can add one or many attachments:
$msgraph->addAttachment(array $attachment = [
    'name' => $filename,
    'contentType' => $contentType,
    'content' => $contentBytes
]);
// and repeat if you have many attachments.

// Last, send your mail:
$msgraph->send();
```

## Testing

```bash
composer test
```

## Changelog

> Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

> Feel free to contribute to this project to improve with new features or fix bugs 👍

## Credits

-   [Jordan](https://github.com/jornatf)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT).

> Please see [License File](LICENSE.md) for more information.
