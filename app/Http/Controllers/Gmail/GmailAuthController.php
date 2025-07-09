<?php

namespace App\Http\Controllers\Gmail;


use Exception;
use App\Http\Controllers\Controller;
use App\Jobs\FetchGmailMessageIdsJob;
use App\Models\GmailAccounts;
use App\Traits\GoogleClientConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Google_Service_Oauth2;

class GmailAuthController extends Controller
{
    //
    use GoogleClientConfig;

    public function allowAccess()
    {

        return redirect($this->fetchGoogleClient()->createAuthUrl());
    }

    public function getAuthToken(Request $req)
    {
        try {
            $token = $this->fetchGoogleClient()->fetchAccessTokenWithAuthCode($req->code);
            $oauth2Service = new Google_Service_Oauth2($this->authTokenGoogleClient($token['access_token']));
            $userInfo = $oauth2Service->userinfo->get();
            $gmailAccountId = (string) Str::uuid();
            $gmailAccount = GmailAccounts::create([
                'uuid' => $gmailAccountId,
                'gmail_account' => $userInfo->email,
                'access_token' => $token['access_token'],
                'refresh_token' => $token['refresh_token'],
                'expires_in' => $token['expires_in'],
                'scope' => $token['scope']
            ]);
            FetchGmailMessageIdsJob::dispatch($gmailAccountId);
            return to_route('home');
        } catch (\Exception $e) {
            session()->flash('account_already_linked', "Gmail Account  is already Linked");
            return to_route('home');
        }
    }
}
