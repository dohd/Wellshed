<?php

namespace App\Http\Controllers\Focus\etr;

use Exception;
use Illuminate\Support\Facades\File;
use Log;

trait EfrisTrait
{
    public function logError($e)
    {
        return Log::error($e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
    }
    
    public function setInterfaceCode($code='')
    {
        $this->reqBody->globalInfo->interfaceCode = $code;
        return;
    }

    public function setContentAndSignature($content='', $signature='')
    {
        $this->reqBody->data->content = $content;
        $this->reqBody->data->signature = $signature;
        return;
    }

    public function resetRequestBody()
    {
        $efrisConfig = unserialize(serialize(config('efris')));
        $this->reqBody = $efrisConfig['req_body'];    
        $this->reqBody->globalInfo->tin = $this->business->taxid;
        $this->reqBody->globalInfo->deviceNo = $this->business->etr_code;
        $this->reqBody->globalInfo->longitude = $this->business->efris_longitude;
        $this->reqBody->globalInfo->latitude = $this->business->efris_latitude;
        return $this->reqBody;
    }
    
    public function postRequest($content)
    {
        $client = new \GuzzleHttp\Client();
        $promise = $client->postAsync($this->baseUrl, [
            'headers' => [
                'Content-Type' => "application/json",
                'Accept' => "application/json",
            ],
            'json' => $content,
        ]);
        
        $responseData = (object) [];
        $promise->then(
            function ($response) use(&$responseData){
                $responseData = json_decode($response->getBody()->getContents());
            },
            function (\Exception $e) {
                \Log::error('EFRIS Post error, ' . $e->getMessage());
            }
        );
        $promise->wait();
        return $responseData;
    }

    public function errorHandle($response)
    {
        // check if status code is not success '00' 
        $returnCode = $response->returnStateInfo->returnCode;
        if ($returnCode != '00') {
            Log::error('EFRIS Error returnStateInfo, ', (array) $response->returnStateInfo);
            $errorMsg = $response->returnStateInfo->returnMessage;
            // check expired key code erro: '402'
            if ($returnCode == '402') {
                $errorMsg = 'Session Key Expired! Please try again';
                // Initiate new device key
                $this->getSymmetricKey();
            }

            // Check if the content is AES Encrypted
            if ($response->data->dataDescription->encryptCode === "2") {
                $encryptedContent = base64_decode($response->data->content);
                $decryptedContent = $this->AESDecrypt($encryptedContent);
                if ($decryptedContent) {
                    $content = json_decode($decryptedContent, true);
                    Log::error('EFRIS Error dataContent, ', is_array($content)? $content : [$content]);
                } else {
                    Log::error('EFRIS Error dataContent, ', [$encryptedContent]);
                }
            } 

            // check partial failure error: '45'
            if ($returnCode == '45') {
                if (is_array($content)) {
                    $product = @$content[0];
                    if ($product) $errorMsg = $product['returnMessage'];
                    return;
                }
            }

            throw new Exception('EFRIS Error, '. $errorMsg);
        }
    }

    public function extractContentData($response)
    {
        // reset request body 
        $this->resetRequestBody();
        // Check for errors before content processing
        $this->errorHandle($response);

        if (!$response->data->content) return '';
        $encryptedContent = base64_decode($response->data->content);

        // Check if the content is compressed
        if ($response->data->dataDescription->zipCode === "1") {
            // Decompress Gzip data
            $encryptedContent = gzinflate(substr($encryptedContent, 10, -8));
        }

        // Check if content is valid JSON
        $trimmed = trim($encryptedContent);
        if ($trimmed && ($trimmed[0] === '{' || $trimmed[0] === '[')) {
            return json_decode($encryptedContent, true);
        }

        // Check if the content is AES Encrypted
        if ($response->data->dataDescription->encryptCode === "2") {
            // check if content is binary data
            $decryptedContent = $this->AESDecrypt($encryptedContent);
        } 

        return json_decode(@$decryptedContent ?: $encryptedContent, true);
    }

    public function RSADecrypt($encryptedData)
    {
        $resource = openssl_pkey_get_private($this->privateKey);
        if (!$resource) return false;
        $decryptedData = '';
        $success = openssl_private_decrypt($encryptedData, $decryptedData, $resource);
        if (!$success) return false;
        File::put(storage_path('efris.aes.key'), $decryptedData);
        return $decryptedData;
    }

    public function AESDecrypt($encryptedData)
    {
        $AESKey = base64_decode($this->AESKey);
        // Decrypt using AES-128-ECB (No IV needed for ECB mode)
        $decryptedData = openssl_decrypt($encryptedData,'AES-128-ECB', $AESKey, OPENSSL_RAW_DATA);
        if (!$decryptedData) {
            $errorMsg = openssl_error_string();
            return false;
        }
        return $decryptedData;
    }

    public function AESEncryptAndEncode($content)
    {
        $AESKey = base64_decode($this->AESKey);
        $encryptedContent = openssl_encrypt($content, 'AES-128-ECB', $AESKey, OPENSSL_RAW_DATA);
        if (!$encryptedContent) return false;
        return base64_encode($encryptedContent);
    }

    public function RSASignContent($content)
    {
        // Load RSA private Key
        $resource = openssl_pkey_get_private($this->privateKey);
        if (!$resource) return false;
        // RSA Sign the content with SHA1 Algorithm
        $signature = '';
        openssl_sign($content, $signature, $resource, OPENSSL_ALGO_SHA1);
        return base64_encode($signature);
    }
}