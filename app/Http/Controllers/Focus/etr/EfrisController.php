<?php

namespace App\Http\Controllers\Focus\etr;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Log;

class EfrisController extends Controller
{
    use EfrisTrait;

    protected $baseUrl, 
        $reqBody, 
        $serviceGood,
        $stockGood,
        $stockGoodIn,
        $stockGoodOut, 
        $invoiceGood,
        $invoiceBody,
        $creditNoteBody,
        $privateKey, 
        $AESKey, 
        $deviceNo,
        $business;

    public function __construct()
    {
        $this->baseUrl = config('services.efris.base_url');

        $efrisConfig = unserialize(serialize(config('efris')));
        $this->privateKey = $efrisConfig['private_key'];
        $this->AESKey = $efrisConfig['aes_key'];
        $this->reqBody = $efrisConfig['req_body'];        
        $this->invoiceBody = $efrisConfig['invoice_body'];
        $this->creditNoteBody = $efrisConfig['credit_note_body'];
        $this->serviceGood = $efrisConfig['service_good'];
        $this->stockGood = $efrisConfig['stock_good'];
        $this->stockGoodIn = $efrisConfig['stock_good_in'];
        $this->stockGoodOut = $efrisConfig['stock_good_out'];
        $this->invoiceGood = $efrisConfig['invoice_good'];

        $business = @auth()->user()->business;
        if ($business) $this->setDynamicInfo($business);
        // Case of Request cycle
        $this->middleware(function ($request, $next) {
            $business = @auth()->user()->business;
            if ($business) $this->setDynamicInfo($business);
            return $next($request);
        });
    }

    // Set Dynamic Info
    public function setDynamicInfo($business)
    {
        $this->business = $business;
        $this->deviceNo = $business->etr_code;
        if ($business->efris_aes_key) {
            $this->AESKey = $business->efris_aes_key;
        }
        $this->reqBody->globalInfo->tin = $business->taxid;
        $this->reqBody->globalInfo->deviceNo = $business->etr_code;
        $this->reqBody->globalInfo->longitude = $business->efris_longitude;
        $this->reqBody->globalInfo->latitude = $business->efris_latitude;
        $this->reqBody->globalInfo->requestTime = date('Y-m-d H:i:s');
    }

    // Get Server Time: T101
    public function getServerTime()
    {
        try {
            $this->setInterfaceCode('T101');
            $response = $this->postRequest($this->reqBody);
            $contentData = $this->extractContentData($response);
            return $contentData;
        } catch (\Exception $e) {
            \Log::error('EFRIS, server time error: ' . $e->getMessage());
            return $e->getMessage();
        }
    }

    // Get Symmetric Key: T104
    public function getSymmetricKey()
    {
        try {
            $this->setInterfaceCode('T104');
            $response = $this->postRequest($this->reqBody);
            $contentData = $this->extractContentData($response);
            $encryptedData = base64_decode($contentData['passowrdDes']);
            $decryptedData = $this->RSADecrypt($encryptedData);
            if (!is_string($decryptedData)) throw new Exception("RSA Decryption Failed");
            if ($this->business && $decryptedData) {
                $this->business->update(['efris_aes_key' => $decryptedData]);
            }
            \Log::info('EFRIS, AES key set');
            return $decryptedData;
        } catch (\Exception $e) {
            \Log::error('EFRIS, AES key not set: ' . $e->getMessage());
        }
    }

    // Get All Exchange Rates: T126
    public function getAllExchangeRates()
    {
        try {
            $this->setInterfaceCode('T126');
            $response = $this->postRequest($this->reqBody);            
            $contentData = $this->extractContentData($response);
            return response()->json($contentData);
        } catch (Exception $e) {
            $this->logError($e);
            return response()->json(['message' => $e->getMessage()],500);
        }
    }

    // Query Tax Payer Info By TIN or NinBrn: T119
    public function infoByTinOrNinBrn($tin)
    {   
        $this->setInterfaceCode('T119');        
        $content = json_encode(['tin' => $tin], JSON_UNESCAPED_SLASHES);
        $encryptedContentB64 = $this->AESEncryptAndEncode($content);
        $signatureB64 = $this->RSASignContent($encryptedContentB64);
        $this->setContentAndSignature($encryptedContentB64, $signatureB64);

        $response = $this->postRequest($this->reqBody);
        $contentData = $this->extractContentData($response);
        return $contentData;
    }

    // System Dictionary Update: T115
    // Query system parameters such as VAT, Excise Duty, and Currency
    public function systemDictionaryUpdate(Request $request)
    {
        $this->setInterfaceCode('T115');
        $response = $this->postRequest($this->reqBody);            
        $contentData = $this->extractContentData($response);
        if (request('key') == 'rateUnit') {
            return $contentData['rateUnit'];
        } elseif (request('key') == 'currencyType') {
            return $contentData['currencyType'];
        }

        return $contentData;
    }

    // Goods Upload: T130
    public function goodsUpload($goods=[])
    {   
        $this->setInterfaceCode('T130');
        $content = json_encode($goods, JSON_UNESCAPED_SLASHES);
        $encryptedContentB64 = $this->AESEncryptAndEncode($content);
        $signatureB64 = $this->RSASignContent($encryptedContentB64);
        $this->setContentAndSignature($encryptedContentB64, $signatureB64);
        
        $response = $this->postRequest($this->reqBody);
        $contentData = $this->extractContentData($response);
        return $contentData;
    }

    // Goods Stock Maintain: T131
    // Update Stock for a given product
    public function stockMaintain($stockGoodAdj=[])
    {
        $this->setInterfaceCode('T131');
        $content = json_encode($stockGoodAdj, JSON_UNESCAPED_SLASHES);
        $encryptedContentB64 = $this->AESEncryptAndEncode($content);
        $signatureB64 = $this->RSASignContent($encryptedContentB64);
        $this->setContentAndSignature($encryptedContentB64, $signatureB64);

        $response = $this->postRequest($this->reqBody);
        $contentData = $this->extractContentData($response);
        return $contentData;
    }

    // Query Goods or Goods by Code: T127
    public function queryGoods($goodsCode='', $pageNo='1', $pageSize='10')
    {
        $this->setInterfaceCode('T127');
        $content = json_encode(compact('goodsCode', 'pageNo', 'pageSize'), JSON_UNESCAPED_SLASHES);
        $encryptedContentB64 = $this->AESEncryptAndEncode($content);
        $signatureB64 = $this->RSASignContent($encryptedContentB64);
        $this->setContentAndSignature($encryptedContentB64, $signatureB64);

        $response = $this->postRequest($this->reqBody);
        $contentData = $this->extractContentData($response);
        return $contentData;
    }

    // Query Invoice By Number: T108
    public function queryInvoices($invoiceNo='')
    {
        $this->setInterfaceCode('T108');
        $content = json_encode(compact('invoiceNo'), JSON_UNESCAPED_SLASHES);
        $encryptedContentB64 = $this->AESEncryptAndEncode($content);
        $signatureB64 = $this->RSASignContent($encryptedContentB64);
        $this->setContentAndSignature($encryptedContentB64, $signatureB64);

        $response = $this->postRequest($this->reqBody);
        $contentData = $this->extractContentData($response);
        return $contentData;
    }

    // Invoice Upload: T109
    // Upload the Invoice/Receipt
    public function invoiceUpload($model=[])
    {
        // create invoice
        $invoice = $this->createInvoice($model); 
        
        $this->setInterfaceCode('T109');
        $content = json_encode($invoice, JSON_UNESCAPED_SLASHES);
        $encryptedContentB64 = $this->AESEncryptAndEncode($content);
        $signatureB64 = $this->RSASignContent($encryptedContentB64);
        $this->setContentAndSignature($encryptedContentB64, $signatureB64);
        
        $response = $this->postRequest($this->reqBody);
        $contentData = $this->extractContentData($response);
        return $contentData;
    }

    // Create Invoice
    public function createInvoice($model)
    {
        $invoice = $this->invoiceBody;        
        $invoice['sellerDetails'] = array_replace($invoice['sellerDetails'], [
            'referenceNo' => gen4tid('', $model->tid),
            // 'referenceNo' => date('YmdHis'),
            'tin' => $this->business->taxid,
            'legalName' => $this->business->cname,
            'emailAddress' => $this->business->email,
        ]);
        $invoice['basicInformation'] = array_replace($invoice['basicInformation'], [
            'deviceNo' => $this->deviceNo,
            'issuedDate' => date($model->invoicedate . ' H:i:s'),
            'operator' => (string) @$model->user->full_name,
            'currency' => (string) @$model->currency->code,
        ]);
        $invoice['buyerDetails'] = array_replace($invoice['buyerDetails'], [
            'buyerType' => (string) @$model->customer->efris_buyer_type,
            'buyerTin' => (string) @$model->customer->taxid,
            'buyerLegalName' => strval(@$model->customer->company ?: @$model->customer->name),
        ]);

        // Set Goods Details
        $invoiceGoods = [];
        $taxDetails = [];
        $payWays = [];
        foreach ($model->products as $key => $item) {
            $productVar = $item->product_variation;
            $efrisGood = $productVar->efris_good;
            $product = $productVar->product;

            $itemQty = +$item->product_qty;
            $taxRate = $product->taxrate * 0.01;
            $unitPriceIncl = round($item->product_price * (1 + $taxRate), 4);
            $total = round($unitPriceIncl * $itemQty, 4);
            $tax = round(($total * $taxRate/(1+$taxRate)), 4);
            $subtotal = $total-$tax;

            $invoiceGoods[] = array_replace($this->invoiceGood, [
                'item' => $item->description,
                'itemCode' => $efrisGood->goods_code,
                'qty' => $itemQty,
                'unitOfMeasure' => $efrisGood->measure_unit,
                'unitPrice' => $unitPriceIncl,
                'total' => $total,
                'taxRate' => $taxRate,
                'tax' => $tax,
                'orderNumber' => (string) $key,
                'goodsCategoryId' => $productVar->efris_commodity_code,
            ]);
            $taxDetails[] = [
                'taxCategoryCode' => @$model->customer->is_tax_exempt? '03' : '01', // 01:A:Standard, 02:B:Zero, 03:C:Excempt, 04:D:Deemed, 05:E:Excise Duty,... 
                'grossAmount' => $total,
                'taxRate' => $taxRate,
                'taxAmount' => $tax,
                'netAmount' => $subtotal,
            ];
            $payWays[] = [
                'paymentMode' => '101', // 101:Credit, 102:Cash, 108:POS, 107:EFT, 109:RTGS
                'paymentAmount' => (string) round($total, 2),
                'orderNumber' => 'a', // sort alphabetically i.e a,b,c,d
            ];
        }
        $invoice['goodsDetails'] = array_replace($invoice['goodsDetails'], $invoiceGoods);
        $invoice['taxDetails'] = array_replace($invoice['taxDetails'], $taxDetails);
        $invoice['payWay'] = array_replace($invoice['payWay'], $payWays);

        $netAmount = array_reduce($taxDetails, fn($prev, $curr) => ($prev + $curr['netAmount']), 0);
        $taxAmount = array_reduce($taxDetails, fn($prev, $curr) => ($prev + $curr['taxAmount']), 0);
        $grossAmount = array_reduce($taxDetails, fn($prev, $curr) => ($prev + $curr['grossAmount']), 0);
        $invoice['summary'] = array_replace($invoice['summary'], [
            'itemCount' => (string) count($invoiceGoods),
            'grossAmount' => number_format($grossAmount, 4, '.', ''),
            'netAmount' => number_format($netAmount, 4, '.', ''),
            'taxAmount' => number_format($taxAmount, 4, '.', ''),
            'modeCode' => '1', // 1:Online Issuing Receipt Mode
        ]);
        
        return $invoice;
    }

    // Query Credit-note Application Status: T111
    public function queryCreditNoteStatus($referenceNo)
    {
        $queryType=1; // 1:current application list, 3:approved application list 
        $pageNo=1; 
        $pageSize=99;

        $this->setInterfaceCode('T111');
        $content = json_encode(compact('referenceNo', 'queryType', 'pageNo', 'pageSize'), JSON_UNESCAPED_SLASHES);
        $encryptedContentB64 = $this->AESEncryptAndEncode($content);
        $signatureB64 = $this->RSASignContent($encryptedContentB64);
        $this->setContentAndSignature($encryptedContentB64, $signatureB64);

        $response = $this->postRequest($this->reqBody);
        $contentData = $this->extractContentData($response);
        return $contentData;
    }

    // Credit Note Application: T110
    // Upload Credit Note
    public function creditNoteUpload($model=[])
    {
        // create credit-note
        $creditNote = $this->createCreditNote($model);
        
        $this->setInterfaceCode('T110');
        $content = json_encode($creditNote, JSON_UNESCAPED_SLASHES);
        $encryptedContentB64 = $this->AESEncryptAndEncode($content);
        $signatureB64 = $this->RSASignContent($encryptedContentB64);
        $this->setContentAndSignature($encryptedContentB64, $signatureB64);

        $response = $this->postRequest($this->reqBody);
        $contentData = $this->extractContentData($response);
        return $contentData;
    }

    // Create Credit Note
    public function createCreditNote($model)
    {
        $creditNote = $this->creditNoteBody;   
        $creditNote['oriInvoiceId'] = (string) @$model->invoice->efris_invoice_id;
        $creditNote['oriInvoiceNo'] = (string) @$model->invoice->efris_invoice_no;
        $creditNote['applicationTime'] = date($model->date . ' H:i:s');
        $creditNote['basicInformation'] = array_replace($creditNote['basicInformation'], [
            'operator' => (string) @$model->user->full_name,
        ]);
        $creditNote['buyerDetails'] = array_replace($creditNote['buyerDetails'], [
            'buyerType' => (string) @$model->customer->efris_buyer_type,
            'buyerTin' => (string) @$model->customer->taxid,
            'buyerLegalName' => (string) (@$model->customer->company ?: @$model->customer->name),
        ]);

        // Set Goods Details
        $creditNoteGoods = [];
        $taxDetails = [];
        $payWays = [];
        foreach ($model->items as $key => $item) {
            $productVar = $item->productvar ?: $item->invoice_item->product_variation;
            $efrisGood = $productVar->efris_good;

            $itemQty = -$item->qty;
            $taxRate = $item->tax_id * 0.01;
            $unitPriceIncl = round($item->rate * (1 + $taxRate), 4);
            $total = round($unitPriceIncl * $itemQty, 4);
            $tax = round(($total * $taxRate/(1+$taxRate)), 4);
            $subtotal = $total-$tax;

            $creditNoteGoods[] = array_replace($this->invoiceGood, [
                'item' => $item->name,
                'itemCode' => $efrisGood->goods_code,
                'qty' => $itemQty,
                'unitOfMeasure' => $efrisGood->measure_unit,
                'unitPrice' => $unitPriceIncl,
                'total' => $total,
                'taxRate' => $taxRate,
                'tax' => $tax,
                'orderNumber' => (string) $key,
                'goodsCategoryId' => $productVar->efris_commodity_code,
            ]);

            $taxDetails[] = [
                'taxCategoryCode' => @$model->customer->is_tax_exempt? '03' : '01', // 01:A:Standard, 02:B:Zero, 03:C:Excempt, 04:D:Deemed, 05:E:Excise Duty,... 
                'grossAmount' => $total,
                'taxRate' => $taxRate,
                'taxAmount' => $tax,
                'netAmount' => $subtotal,
            ];
            $payWays[] = [
                'paymentMode' => '101', // 101:Credit, 102:Cash, 108:POS, 107:EFT, 109:RTGS
                'paymentAmount' => (string) round($total, 2),
                'orderNumber' => 'a', // sort alphabetically i.e a,b,c,d
            ];
        }
        $creditNote['goodsDetails'] = array_replace($creditNote['goodsDetails'], $creditNoteGoods);
        $creditNote['taxDetails'] = array_replace($creditNote['taxDetails'], $taxDetails);
        $creditNote['payWay'] = array_replace($creditNote['payWay'], $payWays);

        $netAmount = array_reduce($taxDetails, fn($prev, $curr) => ($prev + $curr['netAmount']), 0);
        $taxAmount = array_reduce($taxDetails, fn($prev, $curr) => ($prev + $curr['taxAmount']), 0);
        $grossAmount = array_reduce($taxDetails, fn($prev, $curr) => ($prev + $curr['grossAmount']), 0);
        $creditNote['summary'] = array_replace($creditNote['summary'], [
            'itemCount' => (string) count($creditNoteGoods),
            'grossAmount' => number_format($grossAmount, 4, '.', ''),
            'netAmount' => number_format($netAmount, 4, '.', ''),
            'taxAmount' => number_format($taxAmount, 4, '.', ''),
        ]);
        
        return $creditNote;
    }
}
