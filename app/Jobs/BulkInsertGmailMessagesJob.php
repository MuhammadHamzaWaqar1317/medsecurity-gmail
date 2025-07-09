<?php

namespace App\Jobs;

use App\Models\GmailAccountMails;
use App\Models\GmailAccounts;
use App\Traits\GoogleClientConfig;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Bus\Batchable;
use Carbon\Carbon;
use Google_Service_Gmail;
use Google_Http_Batch;
use Illuminate\Support\Str;

class BulkInsertGmailMessagesJob implements ShouldQueue
{
    use Queueable, Batchable, GoogleClientConfig;

    /**
     * Create a new job instance.
     */
    public $token;
    public $messageIds;
    public $googleClientAuthConfig;
    public $gmailAccountId;
    public $gmailBaseURI;
    public $gmailBatchEndpoint;
    public function __construct($messageIds, $gmailAccountId)
    {
        //
        $this->token = GmailAccounts::where('uuid', $gmailAccountId)->value('access_token');
        $this->messageIds = $messageIds;
        $this->gmailAccountId = $gmailAccountId;
        $this->gmailBaseURI = env('GMAIL_API_ENDPOINT');
        $this->gmailBatchEndpoint = env('GMAIL_BATCH_ENDPOINT');
        $this->googleClientAuthConfig = [
            'client_id' => env('GOOGLE_CLIENT_ID'),
            'client_secret' => env('GOOGLE_CLIENT_SECRET'),
            'project_id' => env('GOOGLE_PROJECT_ID'),
            'auth_uri' => env('GOOGLE_AUTH_URI'),
            'token_uri' => env('GOOGLE_TOKEN_URI'),
            'auth_provider_x509_cert_url' => env('GOOGLE_CERT_URL'),
            'redirect_uris' => [env('GOOGLE_MAIL_REDIRECT_URI')],
            'javascript_origins' => explode(',', env('GOOGLE_JS_ORIGINS'))
        ];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
        $client = $this->authTokenGoogleClient($this->token, $this->googleClientAuthConfig);
        $client->setUseBatch(true);

        $service = new Google_Service_Gmail($client);
        $batch = new Google_Http_Batch($client, false, "$this->gmailBatchEndpoint");


        foreach ($this->messageIds as $messageId) {
            $request = $service->users_messages->get('me', $messageId, [
                'format' => 'metadata',
                'metadataHeaders' => ['From', 'Subject', 'Date'],
                'fields' => 'id,payload/headers,sizeEstimate,labelIds,snippet'
            ]);
            $batch->add($request, $messageId);
        }

        $results = $batch->execute();

        $bulkInsert = array_map(function ($mail) {
            $headers = collect($mail->getPayload()->getHeaders());
            $received_at = $headers->firstWhere('name', 'Date') ?? $headers->firstWhere('name', 'date');

            return [
                'uuid' => (string) Str::uuid(),
                'gmail_account_id' => $this->gmailAccountId,
                'mail_id' => $mail->id,
                'sender' => $headers->firstWhere('name', 'From')->value,
                'subject' => $headers->firstWhere('name', 'Subject')->value ?? null,
                'description' => $mail->snippet ?? null,
                'received_at' => Carbon::parse($received_at->value)->toDateTimeString(),
                'sizeEstimate' => $mail->sizeEstimate,
                'label_ids' => json_encode($mail->labelIds)
            ];
        }, $results);
        GmailAccountMails::upsert($bulkInsert, ['mail_id']);
    }
}
