<?php

namespace App\Traits;

use Google_Client;
use Google_Service_Gmail;
use Google_Service_Oauth2;

trait GoogleClientConfig
{
    //
    public function fetchGoogleClient($clientAuthConfig = null)
    {
        $client = new Google_Client();
        $client->setHttpClient(new \GuzzleHttp\Client([
            'verify' => false
        ]));
        $clientAuthConfig = $clientAuthConfig ?? [
            'client_id' => env('GOOGLE_CLIENT_ID'),
            'client_secret' => env('GOOGLE_CLIENT_SECRET'),
            'project_id' => env('GOOGLE_PROJECT_ID'),
            'auth_uri' => env('GOOGLE_AUTH_URI'),
            'token_uri' => env('GOOGLE_TOKEN_URI'),
            'auth_provider_x509_cert_url' => env('GOOGLE_CERT_URL'),
            'redirect_uris' => [env('GOOGLE_MAIL_REDIRECT_URI')],
            'javascript_origins' => explode(',', env('GOOGLE_JS_ORIGINS'))
        ];
        $client->setAuthConfig($clientAuthConfig);
        $client->setRedirectUri(route('GmailAuthController.getAuthToken'));
        $client->addScope([
            Google_Service_Gmail::MAIL_GOOGLE_COM,
            Google_Service_Gmail::GMAIL_COMPOSE,
            Google_Service_Gmail::GMAIL_INSERT,
            Google_Service_Gmail::GMAIL_LABELS,
            Google_Service_Gmail::GMAIL_MODIFY,
            Google_Service_Gmail::GMAIL_READONLY,
            Google_Service_Gmail::GMAIL_SETTINGS_BASIC,
            Google_Service_Gmail::GMAIL_SETTINGS_SHARING,
            Google_Service_Oauth2::USERINFO_EMAIL,
            Google_Service_Oauth2::USERINFO_PROFILE,
        ]);
        $client->setAccessType('offline');
        $client->setPrompt('consent');

        return $client;
    }

    public function authTokenGoogleClient($token, $clientAuthConfig = null)
    {
        $client = $this->fetchGoogleClient($clientAuthConfig);
        $client->setAccessToken($token);
        return $client;
    }
}
