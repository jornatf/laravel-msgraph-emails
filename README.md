# MsGraph Mailer 📨

![](https://img.shields.io/github/v/tag/jornatf/msgraph-mailer?style=flat-square) ![](https://img.shields.io/github/license/jornatf/msgraph-mailer?style=flat-square)

A Laravel Package to send emails with [Microsoft Graph API](https://learn.microsoft.com/en-us/graph/use-the-api).

## Installation

Require the `jornatf/msgraph-mailer` package in your `composer.json` and update your dependencies:

```
composer require jornatf/msgraph-mailer
```

After, add this line in `config/app.php` file:

```php
...
'providers' => [
    ...
    Jornatf\Providers\MsGraphMailerProvider::class, // line to add
],
```

## Global Usage

### Prerequisites:

Before using this library you must have your credentials to access the Microsoft Graph API (_client_id_, _secret_id_ and _tenant_id_) and place these in your `.env` file:

```
MSGRAPH_CLIENT_ID={client_id}
MSGRAPH_SECRET_ID={secret_id}
MSGRAPH_TENANT_ID={tenant_id}
```

### Send a simple email:

You can send to one or many recipients.

If recipient one have no name, you've just type his address or else separate name and address by ":".

```php
$email = MsGraphMailer::mail()
    ->to(['John Doe:john.doe@example.com', 'other@example.com', ...$otherRecipients])
    ->subject('A simple email')
    ->body('<h1>Send a simple email to one recipient.</h1>')
    ->send();
```

You can put `Cc` and `Bcc` recipients like `To` recipients:

```php
$email = MsGraphMailer::mail()
    ->to($toRecipients)
    ->cc($ccRecipients)
    ->bcc($bccRecipients)
    ->subject('A simple email')
    ->body('<h1>Send a simple email to one recipient.</h1>')
    ->send();
```

### Attach file(s)

If you want send one or many attachments proceed like below:

```php
$email = MsGraphMailer::mail()
    ->to(['John Doe:john.doe@example.com'])
    ->subject('A simple email')
    ->body('<h1>Send a simple email to one recipient.</h1>')
    ->attachments([
        [
            'name' => $filename,
            'contentType' => $contentType,  // e.g: "application/pdf"
            'content' => $contentBytes      // encoded file
        ],
        ...$otherAttachments
    ])
    ->send();
```

## Methods

| Method                            | Description                                        | Required |
| --------------------------------- | -------------------------------------------------- | -------- |
| `mail()`                          | Instantiate class                                  | ✅       |
| `to(array $toRecipients)`         | Add recipient **_To_**                             | ✅       |
| `cc(array $ccRecipients)`         | Add recipient **_Cc_**                             | ❌       |
| `cc(array $bccRecipients)`        | Add recipient **_Bcc_**                            | ❌       |
| `subject(string $subject)`        | Add mail subject                                   | ✅       |
| `body(string $content)`           | Add mail body (plain text or HTML format accepted) | ✅       |
| `attachments(array $attachments)` | Add attachments                                    | ❌       |
| `send()`                          | Send mail                                          | ✅       |
