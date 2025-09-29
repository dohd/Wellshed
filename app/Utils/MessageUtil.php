<?php

namespace App\Utils;

use Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

//use GuzzleHttp\Client;



class MessageUtil
{


         //sample code
public function sendMessage($phone,$message)
{

    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://api.mobilesasa.com/v1/send/message',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS =>'{
        "senderID": "TOPGEARS",
        "message": "'.$message.'",
        "phone": "'.$phone.'"
    }',
      CURLOPT_HTTPHEADER => array(
        'Accept: application/json',
        'Content-Type: application/json',
        'Authorization: Bearer Ysi8Z1HPxPBmCgmp9JPcEEco8Xw2VR7e4j5EjmChGhrXwUexIVp3G5RuBHlj'
      ),
    ));
    
    $response = json_decode(curl_exec($curl));
    
  curl_close($curl);

  return $response;

    
}



}
