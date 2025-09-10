<?php

namespace App\Classes;

use App\Models\VirtualModels\PostResponse;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Log;
use Webklex\PHPIMAP\Message;

class GeneralHelper
{

    /**
     * @param string $classOfToken
     * @param string $client_id
     * @param string $client_secret
     * @param string $root_url
     * @return null
     * @throws GuzzleException
     */
    public static function getAccessToken(string $classOfToken, string $client_id, string $client_secret, string $root_url) {

        $tokenObj =  $classOfToken::orderBy('id', 'desc')->first();

        if($tokenObj) {
            $created = strtotime($tokenObj->created_at);
            $currentTime = time();
            $timeDif = $currentTime - $created;
            if($timeDif < $tokenObj->expired_in) {
                return $tokenObj->token_string;
            }
        }

        $postObj = [
            "client_id" => $client_id,
            "client_secret" => $client_secret,
            "grant_type" => "client_credentials",
            "scope" => "*"
        ];

        $client = new Client(['verify' => false]);
        $full_url = $root_url."/oauth/token";

        try {
            $raw_response = $client->post($full_url, [
                RequestOptions::JSON => $postObj
            ]);

        } catch (ClientException $exception) {
            Log::channel("slack")->error("getAccessToken Error to ".$classOfToken, [
                "Response Phrase" => $exception->getResponse()->getReasonPhrase(),
                "statusCode" => $exception->getResponse()->getStatusCode(),
                "Message" => $exception->getMessage()
            ]);
            return null;
        }

        $dataResponse = $raw_response->getBody()->getContents();
        $dataResObj = json_decode($dataResponse);
        if(property_exists($dataResObj, "access_token") && property_exists($dataResObj, "expires_in")) {
            $tokenData = new $classOfToken();
            $tokenData->token_string = $dataResObj->access_token;
            $tokenData->expired_in = $dataResObj->expires_in;
            $tokenData->save();
            return $tokenData->token_string;
        }

        \Log::channel("slack")->error("getAccessToken UNKNOW Error".$classOfToken);
        return null;
    }



    /**
     * @param string $path
     * @param object $dataToSend
     * @return PostResponse
     * @throws GuzzleException
     */
    public static function postTo(string $path, object $dataToSend,string $token=null,string $api_key=null): PostResponse
    {

        $jsonString = json_encode($dataToSend);


        $client = new Client(['verify' => false]);
        $toupesuResponse = new PostResponse();
        $header = [
            'Content-Type' => 'application/json',
            "Accept"=>  'application/json',
        ];
        if($token !=null){
            $header['Authorization' ]= 'Bearer ' . $token;
        }
        if($api_key !=null){
            $header['api-key' ]= $api_key;
        }


        try {
            $raw_response = $client->post($path, [
                'headers' => $header,
                'body' => $jsonString
            ]);



            $toupesuResponse->is_success = true;
            $toupesuResponse->code = 200;
            $toupesuResponse->result = json_decode($raw_response->getBody()->getContents());
            info("Error has occur",["data"=>$dataToSend,"error"=>$toupesuResponse->result]);

        } catch (\GuzzleHttp\Exception\ClientException $exception) {


            Log::error("sendMessage Error Toupesu", [
                "Response Phrase" => $exception->getResponse()->getReasonPhrase(),
                "statusCode" => $exception->getResponse()->getStatusCode(),
                "Message" => $exception->getMessage()
            ]);

            $toupesuResponse->is_success = false;
            $toupesuResponse->code = $exception->getResponse()->getStatusCode();
            $toupesuResponse->result = json_decode($exception->getResponse()->getBody()->getContents());
            $toupesuResponse->errorResponsePhrase = $exception->getResponse()->getReasonPhrase();
        }catch (\Exception $e){

            info("Error has occur",["data"=>$dataToSend,"error"=>$e]);
            $toupesuResponse->is_success = false;
            $toupesuResponse->code = 500;
            $toupesuResponse->errorResponsePhrase = "some thing when wrong";

        }


        return $toupesuResponse;
    }

    /**
     * @param string $path
     * @param object $dataToSend
     * @return PostResponse
     * @throws GuzzleException
     */
    public static function postToPayment(string $path, object $dataToSend): PostResponse
    {

        $jsonString = json_encode($dataToSend);
        $urlToPost = "https://api.toupesu.com/livepaygateway2". $path;
        $client = new Client(['verify' => false]);
        $toupesuResponse = new PostResponse();

        try {
            $raw_response = $client->post($urlToPost, [
                'headers' => [
                    'Content-Type' => 'application/json'
                ],
                'body' => $jsonString
            ]);

            $toupesuResponse->is_success = true;
            $toupesuResponse->code = 200;
            $toupesuResponse->result = json_decode($raw_response->getBody()->getContents());

//            Log::channel("slack")->info("Toupesu Posted Response", [
//                "Response Posting" => $raw_response->getBody()->getContents(),
//                "Message Posted" => $dataToSend,
//                "Path" => $path
//            ]);

        } catch (\GuzzleHttp\Exception\ClientException $exception) {

            Log::channel("slack")->error("sendMessage Error Toupesu", [
                "Response Phrase" => $exception->getResponse()->getReasonPhrase(),
                "statusCode" => $exception->getResponse()->getStatusCode(),
                "Message" => $exception->getMessage()
            ]);

            $toupesuResponse->is_success = false;
            $toupesuResponse->code = $exception->getResponse()->getStatusCode();
            $toupesuResponse->result = json_decode($exception->getResponse()->getBody()->getContents());
            $toupesuResponse->errorResponsePhrase = $exception->getResponse()->getReasonPhrase();
        }

        return $toupesuResponse;




    }

    /**
     * @param string $path
     * @param object $dataToSend
     * @return PostResponse
     * @throws GuzzleException
     */
    public static function postToApiLayer($from,$to,$amount=1): PostResponse
    {



      $data = [
          "to"=>$to,
          "from"=>$from,
          "amount"=>$amount
      ];



        $client = new Client(['verify' => false]);
        $toupesuResponse = new PostResponse();
        $header = [
            'Content-Type' => 'application/json',
            "Accept"=>  'application/json',
            "apikey"=> env("API_LAYER_CURRENCY_KEY","")
        ];



        try {
            $raw_response = $client->get(env("API_LAYER_CURRENCY_PATH","") . "?to={$to}&from={$from}&amount={$amount}", [
                'headers' => $header,

            ]);

            $toupesuResponse->is_success = true;
            $toupesuResponse->code = 200;
            $toupesuResponse->result = json_decode($raw_response->getBody()->getContents());

            info("Error has occur",["data"=>$data ,"error"=>$toupesuResponse->result]);

        } catch (\GuzzleHttp\Exception\ClientException $exception) {


            Log::error("request has failed", [
                "Response Phrase" => $exception->getResponse()->getReasonPhrase(),
                "statusCode" => $exception->getResponse()->getStatusCode(),
                "Message" => $exception->getMessage()
            ]);

            $toupesuResponse->is_success = false;
            $toupesuResponse->code = $exception->getResponse()->getStatusCode();
            $toupesuResponse->result = json_decode($exception->getResponse()->getBody()->getContents());
            $toupesuResponse->errorResponsePhrase = $exception->getResponse()->getReasonPhrase();

        }catch (\Exception $e){

            info("Error has occur",["data"=>$data ,"error"=>$e]);
            $toupesuResponse->is_success = false;
            $toupesuResponse->code = 500;
            $toupesuResponse->errorResponsePhrase = "some thing when wrong";

        }


        return $toupesuResponse;
    }



    public static function getRawBobyMessageFNBEmail(Message $message) {

        $msgParts = explode("_Part_", $message->getRawBody());
        $importantMsg = $msgParts[4];
        $importantMsg = strip_html($importantMsg);
        $splitingMsg = explode("inline", $importantMsg);
        return $splitingMsg[1];
    }
}
