<?php

namespace Jornatf\MsGraphMailer;

use Exception;
use Illuminate\Support\Facades\Http;

class MsGraphMailer
{
    private string $apiEndpoint = 'https://graph.microsoft.com/v1.0';

    private array $headers = [
        'Content-Type' => 'application/json',
    ];

    private string $mailbox;

    private array $response;

    private string $subject;

    private string $body;

    private array $toRecipients = [];

    private array $ccRecipients = [];

    private array $bccRecipients = [];

    private array $attachments = [];

    /**
     * Get env var.
     * 
     * @param  string  $key
     * @return string
     */
    protected function getEnv(string $key)
    {
        if (! env($key) || env($key) == '') {
            throw new Exception("$key is not define in .env file.");
        }

        return env($key);
    }

    /**
     * Returns Authorization Token.
     *
     * @return string
     */
    protected function getToken()
    {
        $bodyDatas = http_build_query([
            'client_id' => $this->getEnv('MSGRAPH_CLIENT_ID'),
            'scope' => 'https://graph.microsoft.com/.default',
            'client_secret' => $this->getEnv('MSGRAPH_SECRET_ID'),
            'grant_type' => 'client_credentials',
        ]);

        $response = Http::withBody($bodyDatas)
            ->withUrlParameters(['tenant_id' => $this->getEnv('MSGRAPH_TENANT_ID')])
            ->post('https://login.microsoftonline.com/{tenant_id}/oauth2/v2.0/token')->json();
        
        if ($response && $response['error']) {
            throw new Exception($response['error_description'] ?? 'No response returned.');
        }

        if (! $response || ! $response['access_token']) {
            throw new Exception('No token defined.');
        }

        return $response['access_token'];
    }

    /**
     * Returns the instance of the class.
     *
     * @param  string  $mailbox
     * @return MsGraphMailer
     */
    public function mail(string $mailbox)
    {
        $this->mailbox = $mailbox;

        return $this;
    }

    /**
     * Main recipients <To>.
     *
     * @param  array  $recipients
     * @return MsGraphMailer
     */
    public function to(array $recipients)
    {
        $this->toRecipients = $this->formatRecipients($recipients);

        return $this;
    }

    /**
     * Recipients <Cc>.
     *
     * @param  array  $recipients
     * @return MsGraphMailer
     */
    public function cc(array $recipients)
    {
        $this->ccRecipients = $this->formatRecipients($recipients);

        return $this;
    }

    /**
     * Recipients <Bcc>.
     *
     * @param  array  $recipients
     * @return MsGraphMailer
     */
    public function bcc(array $recipients)
    {
        $this->bccRecipients = $this->formatRecipients($recipients);

        return $this;
    }

    /**
     * Subject.
     *
     * @param  string  $subject
     * @return MsGraphMailer
     */
    public function subject(string $subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Body.
     *
     * @param  string  $body
     * @return MsGraphMailer
     */
    public function body(string $body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Attachments.
     *
     * @param  array  $attachment
     * @return MsGraphMailer
     */
    public function addAttachment(array $attachment)
    {
        $this->attachments[] = [
            '@odata.type' => '#microsoft.graph.fileAttachement',
            'name' => $attachment['name'],
            'contentType' => $attachment['contentType'],
            'contentBytes' => base64_encode($attachment['content']),
        ];

        return $this;
    }

    /**
     * Sends email after validate required properties.
     *
     * @return MsGraphMailer
     */
    public function send()
    {
        foreach (['toRecipients', 'subject', 'body'] as $property) {
            if (! $this->{$property}) {
                throw new Exception("[$property] property is required.");
            }
        }

        try {
            $http = Http::withToken($this->getToken())
                ->withHeaders($this->headers)
                ->withUrlParameters([
                    'endpoint' => $this->apiEndpoint,
                    'mailbox' => $this->mailbox,
                ])
                ->post('{+endpoint}/users/{mailbox}/sendMail', $this->getEmailDatas())
                ->json();

            $this->response['success'] = true;
        } catch (Exception $e) {
            $this->response['success'] = false;
            $this->response['message'] = $e->getMessage();
        }

        return $this;
    }

    /**
     * Formate recipients array.
     *
     * @param  array  $recipients
     * @return array
     */
    protected function formatRecipients(array $recipients)
    {
        $result = [];

        foreach ($recipients as $recipient) {
            if (str_contains($recipient, ':')) {
                [$name, $address] = explode(':', $recipient);
                $result[] = [
                    'emailAddress' => ['name' => $name, 'address' => $address],
                ];
            } else {         
                $result[] = [
                    'emailAddress' => ['address' => $recipient],
                ];
            }
        }

        return $result;
    }

    /**
     * Returns request datas to send email.
     *
     * @return array
     */
    protected function getEmailDatas()
    {
        $datas = [
            'subject' => $this->subject,
            'body' => [
                'contentType' => 'HTML',
                'content' => $this->body,
            ],
            'toRecipients' => $this->toRecipients
        ];

        foreach (['ccRecipients', 'bccRecipients', 'attachments'] as $key) {
            if ($this->{$key}) {
                $datas['key'] = $this->{$key};
            }
        }

        return ['message' => $datas];
    }
}
