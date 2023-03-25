<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class EtsyClient {
    const CONNECT_URL = "https://www.etsy.com/oauth/connect";
    const TOKEN_URL = "https://api.etsy.com/v3/public/oauth/token";
    const API_URL = "https://api.etsy.com/v3/application/";

    private $api_key;
    
    /**
     * Constructor
     * 
     * @return void
     */
    function  __construct(){
        if(!config('etsy.etsy_api_key')){
            throw new \Exception("Etsy API key not found");
        }

        $this->api_key = config('etsy.etsy_api_key'); 
    }

    /**
     * generate redirect url for etsy connection
     * 
     * @param string $redirect_uri, array $scopes, string $nonce, string $challenge
     * @return string
     */
    public function getAuthorizationUrl($redirect_uri, $scopes = [], $nonce = null, $challenge = null){

        $scopes = implode(
            " ",
            $scopes
        );

        $params = [
            "response_type" => "code",
            "redirect_uri" => $redirect_uri,
            "scope" => $scopes,
            "client_id" => $this->api_key,
            "state" => $nonce,
            "code_challenge" => $challenge,
            "code_challenge_method" => "S256"
        ];

        return self::CONNECT_URL."?".http_build_query($params,null, null, PHP_QUERY_RFC3986);
    }   


    /**
     * Create access token from code
     * 
     * @param string $redirect_uri, string $code, string $verifier
     * @return array
     */

    public function getAccessToken($redirect_uri, $code, $verifier){
        $params = [
            "grant_type" => "authorization_code",
            "client_id" => $this->api_key,
            "redirect_uri" => $redirect_uri,
            'code' => $code,
            'code_verifier' => $verifier
          ];

        $request = Http::asForm()->post(self::TOKEN_URL, $params);

        if($request->successful()){
            return $request->json();
        }else{
            throw new \Exception($request->json()["error_description"]);
        }
    }
    /**
     * Create access token from refresh token
     * 
     * @param string $etsy_refresh_token
     * @return array
     */
    public function getAccessTokenFromRefreshToken($etsy_refresh_token){
        $params = [
            "grant_type" => "refresh_token",
            "client_id" => $this->api_key,
            "refresh_token" => $etsy_refresh_token
          ];

        $request = Http::asForm()->post(self::TOKEN_URL, $params);
	
        if($request->successful()){
            return $request->json();
        }
        return false;
    }

    /**
     * Get data from etsy endpoint
     * 
     * @param string $access_token
     * @return array
     */
    public function getDataFromEndpoint($end_point, $access_token){
        $header = [
            "x-api-key" => $this->api_key,
	        "charset" => "utf-8"
        ];

        $request = Http::asForm()->withHeaders($header)->withToken($access_token)->get(self::API_URL.$end_point);

        if($request->status() == 401){
            return [
                'error' => 'Cannot connect to Etsy, Please try reconnecting'
            ];
        }

        return $request->json();
    }
    
    /**
     * Creates a random string of bytes.
     *
     * @param int $bytes
     * @return string
     */
    public function createNonce(int $bytes = 12) {
        return bin2hex(random_bytes($bytes));
    }

    /**
     * Generates a code verifier and code challenge.
     *
     * @return array
     */
    public function generateChallengeCode() {
        // Create a random string.
        $string = $this->createNonce(32);
        // Base64 encode the string.
        $verifier = $this->base64Encode(
            pack("H*", $string)
        );
        // Create a SHA256 hash and base64 encode the string again.
        $code_challenge = $this->base64Encode(
            pack("H*", hash("sha256", $verifier))
        );
        return [$verifier, $code_challenge];
    }

    
    /**
     * Base64 encodes a string and replaces the + and / characters with - and _
     * respectively.
     *
     * @param string $string
     * @return string
     */
      private function base64Encode($string) {
        return strtr(
          trim(
            base64_encode($string),
            "="
          ),
          "+/", "-_"
        );
      }
}