<?php

namespace Jornatf\LaravelMsgraphMailer;

use Exception;
use Illuminate\Support\Facades\Http;

class MsGraph
{
    /**
     * Base API Endpoints.
     *
     * @var string
     */
    protected $apiEndpoint = 'https://graph.miscrosoft.com/v1.0';

    /**
     * Authentication token.
     *
     * @var string
     */
    protected $accessToken;

    /**
     * Default headers.
     *
     * @var array
     */
    protected $headers = [
        'Content-Type' => 'application/json',
    ];

    /**
     * User mailbox.
     * 
     * @var string
     */
    private $mailbox;

    /**
     * Auth client_id.
     *
     * @var string
     */
    private $client_id;

    /**
     * Auth secret_id.
     *
     * @var string
     */
    private $secret_id;

    /**
     * Auth tenant_id
     *
     * @var string
     */
    private $tenant_id;

    /**
     * Response to the email request sent.
     *
     * @var array
     */
    private $response;

    /**
     * Mail subject.
     *
     * @var string
     */
    private $subject;

    /**
     * Mail body.
     *
     * @var string
     */
    private $body;

    /**
     * Recipient <To> for sending mail.
     *
     * @var array
     */
    private $toRecipients;

    /**
     * Recipient <Cc> for sending mail.
     *
     * @var array
     */
    private $ccRecipients;

    /**
     * Recipient <Bcc> for sending mail.
     *
     * @var array
     */
    private $bccRecipients;

    /**
     * Attachments.
     * 
     * @var array
     */
    private $attachments;

    /**
     * Constructs class.
     *
     * @return void
     */
    public function __construct()
    {
        $this->client_id = $this->getEnv('MSGRAPH_CLIENT_ID');

        $this->secret_id = $this->getEnv('MSGRAPH_SECRET_ID');

        $this->tenant_id = $this->getEnv('MSGRAPH_TENANT_ID');

        $this->accessToken = $this->getToken();
    }

    /**
     * Get env var.
     * 
     * @param  string  $key
     * @return string
     */
    protected function getEnv(string $key)
    {
        if (! env($key) || empty(env($key))) {
            throw new Exception("$key var is not define in .env file.");
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
            'client_id' => $this->client_id,
            'scope' => 'https://graph.miscrosoft.com/.default',
            'client_secret' => $this->secret_id,
            'grant_type' => 'client_credentials',
        ]);

        $response = Http::withBody($bodyDatas)
            ->withUrlParameters(['tenant_id' => $this->tenant_id])
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
     * @return \Jornatf\MsGraphMailer
     */
    public static function mail(string $mailbox)
    {
        $this->mailbox = $mailbox;

        return new MsGraph();
    }

    /**
     * Main recipients <To>.
     *
     * @param  array  $recipients
     * @return \Jornatf\MsGraphMailer
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
     * @return \Jornatf\MsGraphMailer
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
     * @return \Jornatf\MsGraphMailer
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
     * @return \Jornatf\MsGraphMailer
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
     * @return \Jornatf\MsGraphMailer
     */
    public function body(string $body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Attachments.
     *
     * @param  array  $attachments
     * @return \Jornatf\MsGraphMailer
     */
    public function attachments(array $attachments)
    {
        foreach ($attachments as $attachment) {
            $this->attachments[] = [
                '@odata.type' => '#microsoft.graph.fileAttachement',
                'name' => $attachment['name'],
                'contentType' => $attachment['contentType'],
                'contentBytes' => base64_encode($attachment['content']),
            ];
        }
    }

    /**
     * Sends email after validate required properties.
     *
     * @return \Jornatf\MsGraphMailer
     */
    public function send(string $mailbox)
    {
        foreach (['to', 'subject', 'body'] as $property) {
            if (! $this->{$property}) {
                throw new Exception("[$property] property is required.");
            }
        }

        $this->response = Http::withToken($this->accessToken)
            ->withHeaders($this->headers)
            ->withUrlParameters([
                'endpoint' => $this->apiEndpoint,
                'mailbox' => $mailbox,
            ])
            ->post('{+endpoint}/users/{mailbox}/sendMail', $this->getEmailDatas())
            ->json();

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
            } else {
                $address = $recipient;
            }

            $result[] = [
                'emailAddress' => ['name' => $name, 'address' => $address],
            ];
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

        return $datas;
    }
}
