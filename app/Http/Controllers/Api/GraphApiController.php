<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;


class GraphApiController extends Controller
{
    private $access_token = "";
    private $refresh_token = "";
    private $client;

    public function __construct()
    {
        $this->client = new Client();
        $this->getAccessToken();
    }

    public function getUsers($displayName = null)
    {
        if ($displayName) {
            $url = 'https://graph.microsoft.com/v1.0/users?$count=true&$search="displayName:' . $displayName . '"&$filter=endsWith(mail,\'tripatra.com\')&$select=id,displayName,mail,jobTitle&$orderBy=displayName';
        } else {
            $url = 'https://graph.microsoft.com/v1.0/users?$count=true&$filter=endsWith(mail,\'tripatra.com\')&$select=id,displayName,mail,jobTitle&$orderBy=displayName';
        }
        try {
            $data = $this->doRequest($url, $this->access_token);
            return $data->original['value'];
        } catch (\Throwable $th) {
            // if access token expired, refresh token
            $this->doRefreshToken();
            $data = $this->doRequest($url, $this->access_token);
            return $data->original['value'];
        }
    }

    public function getAccounts($next_url = null)
    {
        $url = $next_url ?? 'https://graph.microsoft.com/v1.0/users?$count=true&$filter=endsWith(mail,\'tripatra.com\')&$select=id,displayName,mail,jobTitle&$orderBy=displayName';
        try {
            if (!$this->access_token) {
                $this->doRefreshToken();
            }
            $data = $this->doRequest($url, $this->access_token);
            return $data;
        } catch (\Throwable $th) {
            return $th;
        }
    }

    public function getUserbyEmail($email)
    {
        $url = 'https://graph.microsoft.com/v1.0/users/' . $email;
        try {
            $data = $this->doRequest($url, $this->access_token);
            return $data->original;
        } catch (\Throwable $th) {
            // if access token expired, refresh token
            $this->doRefreshToken();
            $data = $this->doRequest($url, $this->access_token);
            return $data->original;
        }
    }

    public function getUser($id)
    {
        $url = 'https://graph.microsoft.com/v1.0/users/' . $id;
        try {
            $data = $this->doRequest($url, $this->access_token);
            return $data;
        } catch (\Throwable $th) {
            // if access token expired, refresh token
            $this->doRefreshToken();
            $data = $this->doRequest($url, $this->access_token);
            return $data;
        }
    }

    private function doRequest($url, $access_token)
    {
        $response = $this->client->get($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $access_token,
                'Accept' => 'application/json',
                'ConsistencyLevel' => 'eventual'
            ],
        ]);
        $data = json_decode($response->getBody()->getContents(), true);
        return response()->json($data);
    }

    private function getAccessToken()
    {
        $url = "https://login.microsoftonline.com/ac6d4546-7d1e-48c7-b04e-0cc38318aec8/oauth2/v2.0/token";
        $body = [
            'form_params' =>  [
                'grant_type' => 'password',
                'scope' => 'user.read email openid profile offline_access',
                'client_id' => env('AZURE_CLIENT_ID'),
                'username' => env('MAIL_USERNAME'),
                'password' => env('MAIL_PASSWORD'),
                'client_secret' => env('AZURE_CLIENT_SECRET')
            ]
        ];
        $request = $this->client->request('GET', $url, $body);
        $response = json_decode($request->getBody()->getContents());
        $this->access_token = $response->access_token;
        $this->refresh_token = $response->refresh_token;
    }

    private function doRefreshToken()
    {
        $url = "https://login.microsoftonline.com/ac6d4546-7d1e-48c7-b04e-0cc38318aec8/oauth2/v2.0/token";
        $body = [
            'form_params' =>  [
                'grant_type' => 'refresh_token',
                'scope' => 'user.read email openid profile offline_access',
                'client_id' => env('AZURE_CLIENT_ID'),
                'client_secret' => env('AZURE_CLIENT_SECRET'),
                'refresh_token' => $this->refresh_token
            ]
        ];
        $request = $this->client->request('GET', $url, $body);
        $response = json_decode($request->getBody()->getContents());
        $this->access_token = $response->access_token;
        $this->refresh_token = $response->refresh_token;
    }
}
