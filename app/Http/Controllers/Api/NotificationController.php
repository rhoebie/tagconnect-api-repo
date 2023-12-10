<?php

namespace App\Http\Controllers\Api;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use GuzzleHttp\Exception\ClientException;

class NotificationController extends Controller
{
    function sendNotification(Request $request)
    {
        try {
            $url = 'https://fcm.googleapis.com/v1/projects/tagconnect-ff743/messages:send';

            // Load the Firebase service account JSON file
            $credentials = json_decode(file_get_contents(storage_path('app/firebase/tagconnect-ff743-e4b4340f03bb.json')), true);

            // Get the request data
            $userToken = $request->input('userToken');
            $title = $request->input('title');
            $body = $request->input('body');

            // Create the request payload
            $payload = [
                'message' => [
                    'token' => $userToken,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                ],
            ];

            // Check if there is a non-expired access token in the database
            $client = new Client();
            $existingToken = DB::table('firebase_access_tokens')
                ->where('expires_at', '>', now())
                ->first();

            if ($existingToken) {
                // Use the existing non-expired token
                $accessToken = $existingToken->token;
            } else {
                // Obtain the access token from Firebase using the service account credentials
                $response = $client->post(
                    'https://www.googleapis.com/oauth2/v4/token',
                    [
                        'form_params' => [
                            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                            'assertion' => $this->generateJwtAssertion($credentials),
                        ],
                    ]
                );

                // Get the access token and its expiration time from the response
                $responseData = json_decode((string) $response->getBody(), true);
                $accessToken = $responseData['access_token'];
                $expiresAt = now()->addSeconds($responseData['expires_in']);

                // Insert the token and its expiration time into the database
                DB::table('firebase_access_tokens')->insert([
                    'token' => $accessToken,
                    'expires_at' => $expiresAt,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Send the POST request to Firebase Cloud Messaging API with the bearer token
            $response = $client->post(
                $url,
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $accessToken,
                        'Content-Type' => 'application/json',
                    ],
                    'json' => $payload,
                ]
            );

            // Check the response status and return the result
            if ($response->getStatusCode() === 200) {
                return response()->json(['message' => 'Notification sent successfully']);
            } else {
                return response()->json(['error' => 'Failed to send notification: ' . $response->getBody()], 500);
            }
        } catch (ClientException $e) {
            // GuzzleHttp exception handling
            $responseBody = $e->getResponse()->getBody()->getContents();
            return response()->json(['error' => 'GuzzleHttp Exception: ' . $responseBody], 500);
        } catch (Exception $e) {
            // Other exceptions
            return response()->json(['error' => 'Unexpected Exception: ' . $e->getMessage()], 500);
        }
    }

    function generateJwtAssertion($credentials)
    {
        $now = time();
        $payload = [
            'iss' => $credentials['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://www.googleapis.com/oauth2/v4/token',
            'exp' => $now + 3600,
            'iat' => $now,
        ];

        $privateKey = $credentials['private_key'];
        $headers = [
            'alg' => 'RS256',
            'typ' => 'JWT',
            'kid' => $credentials['private_key_id'],
        ];

        // Encode the JWT assertion
        $base64UrlHeader = $this->base64UrlEncode(json_encode($headers));
        $base64UrlPayload = $this->base64UrlEncode(json_encode($payload));
        $signature = $this->createSignature($base64UrlHeader . '.' . $base64UrlPayload, $privateKey);

        return $base64UrlHeader . '.' . $base64UrlPayload . '.' . $signature;
    }

    function base64UrlEncode($data)
    {
        $base64 = base64_encode($data);
        if ($base64 === false) {
            return false;
        }
        $base64Url = strtr($base64, '+/', '-_');
        return rtrim($base64Url, '=');
    }

    function createSignature($data, $privateKey)
    {
        $signature = null;
        $success = openssl_sign($data, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        if ($success) {
            return $this->base64UrlEncode($signature);
        } else {
            throw new Exception('Failed to generate JWT signature.');
        }
    }
}