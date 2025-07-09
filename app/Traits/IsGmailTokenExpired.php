<?php

namespace App\Traits;

trait IsGmailTokenExpired
{
    //
    public function isGmailTokenExpired($gmailAccountId)
    {
        $gmailAccount = GmailAccounts::where('uuid', $gmailAccountId)->first()->toArray();
        $googleClient = $this->fetchGoogleClient();
        $googleClient->setAccessToken($gmailAccount['access_token']);
        if ($googleClient->isAccessTokenExpired()) {
            $newAccessToken = $googleClient->fetchAccessTokenWithRefreshToken($gmailAccount['refresh_token']);
            $updateToken = GmailAccounts::where('uuid', $gmailAccountId)->update([
                'access_token' => $newAccessToken['access_token'],
                'refresh_token' => $newAccessToken['refresh_token']
            ]);
        }
    }
}
