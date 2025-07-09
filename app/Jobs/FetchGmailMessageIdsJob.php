<?php

namespace App\Jobs;

use App\Models\GmailBatchFetchProgress;
use App\Traits\GoogleClientConfig;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Google_Service_Gmail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Bus;
use App\Jobs\BulkInsertGmailMessagesJob;
use App\Models\GmailAccounts;

class FetchGmailMessageIdsJob implements ShouldQueue
{
    use Queueable, GoogleClientConfig;

    /**
     * Create a new job instance.
     */
    public $token;
    public $googleClientAuthConfig;
    public $gmailAccountId;
    public function __construct($gmailAccountId)
    {
        //
        $this->token = GmailAccounts::where('uuid', $gmailAccountId)->value('access_token');
        $this->gmailAccountId = $gmailAccountId;
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
        $nextPageToken = null;
        $jobs = [];

        $service = new Google_Service_Gmail($this->authTokenGoogleClient($this->token, $this->googleClientAuthConfig));

        do {
            $messages = $service->users_messages->listUsersMessages('me', ['maxResults' => 500, 'pageToken' => $nextPageToken,  'q' => 'after:2025/04/25 ']);
            $nextPageToken = $messages?->nextPageToken ?? 'Completed';

            if ($messages->getMessages()) {
                $chunkMessage = collect($messages)->chunk(15);
                $chunkMessage->each(function ($chunk) use (&$jobs) {
                    $messageIds = $chunk->map(fn($message) => $message->id)->all();
                    $jobs[] = new BulkInsertGmailMessagesJob($messageIds, $this->gmailAccountId);
                });
            }
        } while ($nextPageToken != 'Completed');
        $batchProgress_uuid = (string) Str::uuid();
        $batch = Bus::batch($jobs)->finally(function () use (&$batchProgress_uuid) {
            GmailBatchFetchProgress::where('uuid', $batchProgress_uuid)->delete();
        })->dispatch();
        GmailBatchFetchProgress::create([
            'uuid' => $batchProgress_uuid,
            'gmail_account_id' => $this->gmailAccountId,
            'batch_id' => $batch->id
        ]);
    }
}
