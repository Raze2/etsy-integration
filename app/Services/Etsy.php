<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use PhpParser\Node\Expr\FuncCall;

class Etsy {
    private $token;

    /**
     * Constructor
     * 
     * @return void
     */

    function  __construct(){
        if(!config('etsy.etsy_api_key')){
            throw new \Exception("Etsy API key not found");
        }

        $this->token = auth()->user()->etsy_access_token;
        
    }

    /**
     * Create access token from Refresh token or make a new connection
     * 
     * @return void
     */
    public function connect(){
        $client = new EtsyClient();

	    if(auth()->user()->etsy_refresh_token){

            $response = $client->getAccessTokenFromRefreshToken(auth()->user()->etsy_refresh_token);

            if($response && $response['access_token']){
                auth()->user()->update([
                    'etsy_access_token' => $response['access_token'],
                    'etsy_refresh_token' => $response['refresh_token'],
                ]);
                return redirect()->route('etsy.index');
            }

            auth()->user()->update([
                'etsy_access_token' => null,
                'etsy_refresh_token' => null,
            ]);
        }

        $scopes = ['email_r','listings_r','transactions_r','profile_r','billing_r','favorites_r','shops_r'];

        $nonce = $client->createNonce();
        [$verifier,$challenge] = $client->generateChallengeCode();

        auth()->user()->update([
            'etsy_nonce' => $nonce,
            'etsy_verifier' => $verifier
        ]);

        $url = $client->getAuthorizationUrl(route('etsy.callback'), $scopes, $nonce, $challenge);

        return redirect($url);
    }

    /* 
     * Check callback from Etsy
     * 
     * @param string $code, string $state
     * @return void
     */
    public function checkCallback($code, $state){
        if($state != auth()->user()->etsy_nonce){
            throw new \Exception("Invalid state");
        }
 
        try{
            $verifier = auth()->user()->etsy_verifier;
            $client = new EtsyClient();
            $response = $client->getAccessToken(route('etsy.callback'), $code, $verifier);

	    auth()->user()->update([
                'etsy_access_token' => $response['access_token'],
                'etsy_refresh_token' => $response['refresh_token'],
            ]);
        } catch(\Exception $e){
            auth()->user()->update([
                'etsy_access_token' => null,
                'etsy_refresh_token' => null,
            ]);
            throw $e;
        }
    }
    
    /** 
     * Get data from Etsy
     * 
     * @param string $url
     * @return array
     */
    public function get($url){
        if(!$this->token){
            throw new \Exception("Etsy access token not found");
        }

        $client = new EtsyClient();

        return $client->getDataFromEndpoint($url, $this->token);        
    }
}