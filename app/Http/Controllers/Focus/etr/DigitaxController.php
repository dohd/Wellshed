<?php

namespace App\Http\Controllers\Focus\etr;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;
use App\Models\creditnote\CreditNote;
use App\Models\invoice\Invoice;
use App\Models\items\InvoiceItem;
use Illuminate\Validation\ValidationException;
use Log;

class DigitaxController extends Controller
{
    /**
     * DigiTax ETR Validation
     */
    public function validation(Request $request)
    {
        try {
            $validated = $request->validate([
                'invoice_id' => 'required_without:creditnote_id',
                'creditnote_id' => 'required_without:invoice_id',
            ]);

            $apiKey = config('services.digitax.api_key');
            $cbUrl = config('services.digitax.cb_url') ?: route('digitax.validation_cb');
            $urls = config('digitax.urls');
            $urls = array_map(fn($v) => config('services.digitax.base_url') . $v, $urls);

            $businessItem = config('digitax.structs.businessItem');
            $adjustmentItem = config('digitax.structs.adjustmentItem');
            $sale = config('digitax.structs.sale');
            $afterSale = config('digitax.structs.afterSale');
            
            $client = new \GuzzleHttp\Client();
            $invoice = Invoice::find($request->invoice_id);
            $creditNote = CreditNote::find($request->creditnote_id);
            $model = $invoice ?: $creditNote;
            $modelName = $invoice? 'Invoice' : 'CreditNote';

            /**Invoice Validation */
            if (@$validated['invoice_id']) {
                if ($invoice && $invoice->customer) {
                    // Post Items
                    $businessItems = [];
                    $savedItemArr = [];
                    $mainTaxRate = +$invoice->tax_id;
                    $isLineHasTax = $invoice->products()->where('tax_rate', '!=', 0)->orWhere('product_tax', '!=', 0)->exists(); 
                    foreach ($invoice->products as $item) {
                        $taxTypeCode = '';
                        $itemTaxRate = +$item->tax_rate;
                        if ($isLineHasTax) {
                            if ($itemTaxRate == 16) $taxTypeCode = 'B'; // 16%;
                            if ($itemTaxRate == 8) $taxTypeCode = 'E'; // 8%
                            if ($itemTaxRate == 0) $taxTypeCode = 'D'; // Non-Vat;
                        } else {
                            if ($mainTaxRate == 16) $taxTypeCode = 'B'; // 16%;
                            if ($mainTaxRate == 8) $taxTypeCode = 'E'; // 8%
                            if ($mainTaxRate == 0) $taxTypeCode = 'A'; // Exempt
                        }
                        $itemName = $item->description;
                        $itemQty = +$item->product_qty;
                        $itemPrice = +$item->product_price;
                        
                        $prodVariation = $item->product_variation;
                        $parentProd = @$prodVariation->product;
                        if ($prodVariation && $parentProd) {
                            if ($parentProd->stock_type == 'service') {
                                // Post Service
                                $client_resp = $client->post($urls['items'], [
                                    'headers' => [
                                        'Content-Type' => "application/json",
                                        'Accept' => "application/json",
                                        'X-API-Key' => $apiKey,
                                    ],
                                    'json' => array_replace($businessItem, [
                                        "item_class_code" => "99020000", // Services classification code
                                        "item_type_code" => "3", // Service without stock
                                        "item_name" => $itemName,
                                        "origin_nation_code" => "KE", // Kenya
                                        "package_unit_code" => "NT", // Net
                                        "quantity_unit_code" => "U", // Pieces or No.Items
                                        "tax_type_code" => $taxTypeCode,
                                        "default_unit_price" => $itemPrice,
                                        'callback_url' => $cbUrl,        
                                    ]),
                                ]);
                                $savedBusinessItem = json_decode($client_resp->getBody()->getContents());
                            } else {
                                // Post Item
                                $client_resp = $client->post($urls['items'], [
                                    'headers' => [
                                        'Content-Type' => "application/json",
                                        'Accept' => "application/json",
                                        'X-API-Key' => $apiKey,
                                    ],
                                    'json' => array_replace($businessItem, [
                                        "item_class_code" => "99010000", // Goods classification code
                                        "item_type_code" => "1", // Raw Material Type Code
                                        "item_name" => $itemName,
                                        "origin_nation_code" => "KE", // Kenya
                                        "package_unit_code" => "NT", // Net
                                        "quantity_unit_code" => "U", // Pieces or No.Items
                                        "tax_type_code" => $taxTypeCode,
                                        "default_unit_price" => $itemPrice,
                                        "is_stock_item" => true,
                                        'callback_url' => $cbUrl,                                    ]),
                                ]);
                                $savedBusinessItem = json_decode($client_resp->getBody()->getContents());
                                // Post Qty Adjustment
                                $client_resp = $client->post($urls['adjustStock'], [
                                    'headers' => [
                                        'Content-Type' => "application/json",
                                        'Accept' => "application/json",
                                        'X-API-Key' => $apiKey,
                                    ],
                                    'json' => array_replace($adjustmentItem, [
                                        'item_id' => $savedBusinessItem->id,
                                        'quantity' => $itemQty,
                                    ]),
                                ]);
                            }
                        } else {
                            // Post Service                    
                            $client_resp = $client->post($urls['items'], [
                                'headers' => [
                                    'Content-Type' => "application/json",
                                    'Accept' => "application/json",
                                    'X-API-Key' => $apiKey,
                                ],
                                'json' => array_replace($businessItem, [
                                    "item_class_code" => "99020000", // Services classification code
                                    "item_type_code" => "3", // Service without stock
                                    "item_name" => $itemName,
                                    "origin_nation_code" => "KE", // Kenya
                                    "package_unit_code" => "NT", // Net
                                    "quantity_unit_code" => "U", // Pieces or No.Items
                                    "tax_type_code" => $taxTypeCode,
                                    "default_unit_price" => $itemPrice,
                                    'callback_url' => $cbUrl, 
                                ]),                                
                            ]);
                            $savedBusinessItem = json_decode($client_resp->getBody()->getContents());
                        }
                        
                        $businessItems[] = [
                            'id' => $savedBusinessItem->id,
                            'quantity' => $itemQty,
                            'unit_price' => $itemPrice,
                            'discount_rate' => 0,
                            'discount_amount' => 0
                        ];
                        $savedItemArr[] = [
                            'id' => $item->id,
                            'digitax_id' => $savedBusinessItem->id,
                            'etims_item_code' => $savedBusinessItem->etims_item_code,
                        ];
                    }  
    
                    // POST Sale
                    try {
                        $customer = $invoice->customer;
                        $client_resp = $client->post($urls['sales'], [
                            'headers' => [
                                'Content-Type' => "application/json",
                                'Accept' => "application/json",
                                'X-API-Key' => $apiKey,
                            ],
                            'json' => array_replace($sale, [
                                'customer_pin' => @$customer->taxid ?: '',
                                'customer_name' => @$customer->company ?: @$customer->name,
                                'trader_invoice_number' => gen4tid('INV-', $invoice->tid),
                                'items' => $businessItems,
                                'general_invoice_details' => $invoice->notes,
                                'callback_url' => $cbUrl,
                            ]),
                        ]);
                        $savedSale = json_decode($client_resp->getBody()->getContents());
    
                        // update e-Tims params
                        $invoice->update([
                            'digitax_id' =>  $savedSale->digitax_id, 
                            'sale_detail_url' => $savedSale->sale_detail_url, 
                            'etims_url' => @$savedSale->etims_url, 
                            'queue_status' => $savedSale->queue_status,
                            'original_invoice_number' => @$savedSale->original_invoice_number,
                            'receipt_number' => @$savedSale->receipt_number,
                            'serial_number' => @$savedSale->serial_number,
                        ]);
                        if (!$invoice->etims_qrcode && $invoice->etims_url) {
                            $this->createQRCode($invoice, 'Invoice');
                        }
                        foreach($savedItemArr as $item2) {
                            $invoiceItem = $invoice->products->where('id', $item2['id'])->first();
                            if ($invoiceItem) $invoiceItem->update(['digitax_id' => $item2['digitax_id'], 'etims_item_code' => $item2['etims_item_code']]);    
                        }
                    } catch (\Exception $e) {
                        // remove prior items
                        $client2 = new \GuzzleHttp\Client();
                        foreach ($businessItems as $savedItem) {
                            $client_resp = $client2->delete($urls['items'] . '/' . $savedItem['id'], [
                                'headers' => [
                                    'accept' => "application/json",
                                    'X-API-Key' => $apiKey,
                                ],
                            ]);
                        }
                        // regenerate QRCode
                        if (@$model->etims_url && !@$model->etims_qrcode) {
                            $this->createQRCode($model, $modelName);
                        }

                        if ($e instanceof \GuzzleHttp\Exception\RequestException) {
                            if ($e->hasResponse()) {
                                $respBody = $e->getResponse()->getBody()->getContents();
                                Log::error('Guzzle response error: ' . $respBody);
                                $errorBag = json_decode($respBody, true);

                                // Duplicate Error
                                if ($e->getResponse()->getStatusCode() == 409) {
                                    return response()->json([
                                        'status' => 'Error', 
                                        'error_data' => $errorBag,
                                        'message' => "Duplicate! {$modelName} has already been validated ",
                                        'props' => [
                                            'digitax_id' => @$model->digitax_id,
                                            'etims_url' => @$model->etims_url,
                                        ],
                                    ], 409);
                                }
                            }
                        } 
                        throw $e;
                    } 
                    
                    return response()->json([
                        'refresh' => 1,
                        'status' => 'Success', 
                        'message' => 'Validation successfully queued for processing',
                        'props' => [
                            'digitax_id' => $invoice->digitax_id,
                            'sale_detail_url' => $invoice->sale_detail_url,
                            'etims_url' => $invoice->etims_url,
                            'queue_status' => $invoice->queue_status,
                            'etims_qrcode' => $invoice->etims_qrcode,
                            'original_invoice_number' => $invoice->original_invoice_number,
                            'receipt_number' => $invoice->receipt_number,
                            'serial_number' => $invoice->serial_number,
                        ],
                    ]);
                } else {
                    throw new Exception('Invoice or customer could not be found');
                }
            }


            /**Credit Note Validation */
            if (@$validated['creditnote_id']) {
                if ($creditNote && $creditNote->invoice && $creditNote->customer) {
                    $businessItems = [];
                    foreach ($creditNote->items as $item) {
                        $businessItems[] = [
                            'id' => @$item->invoice_item->digitax_id,
                            'quantity' => +$item->qty,
                            'unit_price' => +$item->rate,
                            'discount_rate' => 0,
                            'discount_amount' => 0
                        ];
                    }  
                    
                    // POST Credit Note (After Sale)
                    try {
                        $customer = $creditNote->customer;
                        $client_resp = $client->post($urls['sales'], [
                            'headers' => [
                                'Content-Type' => "application/json",
                                'Accept' => "application/json",
                                'X-API-Key' => $apiKey,
                            ],
                            'json' => array_replace($afterSale, [
                                'customer_pin' => @$customer->taxid ?: '',
                                'customer_name' => @$customer->company ?: @$customer->name,
                                'trader_invoice_number' => gen4tid('CN-', $creditNote->tid),
                                'original_invoice_number' => @$creditNote->invoice->original_invoice_number,
                                'items' => $businessItems,
                                'general_invoice_details' => $creditNote->note,
                                'callback_url' => $cbUrl,
                            ]),
                        ]);
                    } catch (\Exception $e) {
                        // custom duplicate error
                        if ($e instanceof \GuzzleHttp\Exception\RequestException) {
                            if ($e->hasResponse() && $e->getResponse()->getStatusCode() == 409) {
                                // check duplicate validation
                                if ($model && ($model->digitax_id || $model->etims_url)) {
                                    $etimsUrl = $model->etims_url;
                                    if ($etimsUrl && !$model->etims_qrcode) {
                                        $this->createQRCode($model, $modelName);
                                    }
                                    return response()->json([
                                        'status' => 'Error', 
                                        'message' => "Duplicate! {$modelName} has already been validated ",
                                        'props' => [
                                            'digitax_id' => $model->digitax_id,
                                            'etims_url' => $model->etims_url,
                                        ],
                                    ], 409);
                                }
                            }
                        }
                        throw $e;
                    }
                    
                    $savedSale = json_decode($client_resp->getBody()->getContents());
    
                    // update e-Tims params
                    $creditNote->update([
                        'digitax_id' =>  $savedSale->digitax_id, 
                        'sale_detail_url' => $savedSale->sale_detail_url, 
                        'etims_url' => @$savedSale->etims_url, 
                        'queue_status' => $savedSale->queue_status,
                        'original_invoice_number' => @$savedSale->original_invoice_number,
                        'receipt_number' => @$savedSale->receipt_number,
                        'serial_number' => @$savedSale->serial_number,
                    ]);
                    if (!$creditNote->etims_qrcode && @$savedSale->etims_url) {
                        $this->createQRCode($creditNote, 'CreditNote');
                    }
    
                    return response()->json([
                        'refresh' => 1,
                        'status' => 'Success', 
                        'message' => 'Validation successfully queued for processing',
                        'props' => [
                            'digitax_id' => $creditNote->digitax_id,
                            'sale_detail_url' => $creditNote->sale_detail_url,
                            'etims_url' => $creditNote->etims_url,
                            'queue_status' => $creditNote->queue_status,
                            'etims_qrcode' => $creditNote->etims_qrcode,
                            'original_invoice_number' => $creditNote->original_invoice_number,
                            'receipt_number' => $creditNote->receipt_number,
                            'serial_number' => $creditNote->serial_number,
                        ],
                    ]);
                } else {
                    throw new Exception('CreditNote or customer could not be found');
                }
            }
        } catch (\Exception $e) {
            $msg = $e->getMessage() .' {user_id: '. auth()->user()->id . '}' . ' at ' . $e->getFile() . ':' . $e->getLine();
            \Illuminate\Support\Facades\Log::error($msg);

            if ($e instanceof ValidationException) {
                return response()->json(['status' => 'Error', 'message' => 'invoice_id or creditnote_id required'], 400);    
            }
            
            return response()->json(['status' => 'Error', 'message' => 'Error processing validation: ' . $e->getMessage()], 500);
        }
    }

    // Validation Callback
    public function validationCb(Request $request)
    {
        try {
            $data = $request->data;
            $event = $request->event;

            if ($event == 'sale.sync' && @$data['digitax_id']) {
                $invoice = Invoice::where('digitax_id', $data['digitax_id'])->first();
                $creditNote = CreditNote::where('digitax_id', $data['digitax_id'])->first();
                $model = $invoice ?: $creditNote;
                $modelName = $invoice? 'Invoice' : 'CreditNote';
                if (!$invoice && !$creditNote) throw new Exception('Invoice or CreditNote with digitax_id: ' . $data['digitax_id'] . ' could not be found');
    
                $model->update([
                    'etims_url' => @$data['etims_url'], 
                    'queue_status' => $data['queue_status'], 
                    'original_invoice_number' => @$data['original_invoice_number'],
                ]);
                if (@$data['etims_url'] && !$model->etims_qrcode) {
                    $this->createQRCode($model, $modelName);
                }
    
                return response()->json([
                    'status' => 'success',
                    'message' => 'Queue status updated successfully',
                    'props' => [
                        'digitax_id' => $model->digitax_id,
                        'sale_detail_url' => $model->sale_detail_url,
                        'etims_url' => $model->etims_url,
                        'queue_status' => $model->queue_status,
                        'etims_qrcode' => $model->etims_qrcode,
                    ],
                ]);
            } elseif ($event == 'item.sync' && @$data['id']) {
                $item = InvoiceItem::where('digitax_id', $data['id'])->first();
                if (!$item) throw new Exception('Item with id: ' . $data['digitax_id'] . ' could not be found');
                $item->update(['etims_item_code' => $data['etims_item_code']]);
                return response()->json([
                    'status' => 'success',
                    'message' => 'eTims Item Code updated successfully',
                    'props' => [
                        'digitax_id' => $item->id,
                        'etims_item_code' => $item->etims_item_code,
                    ],
                ]);
            } else {
                throw new Exception('Sync event or data-id could not be found');
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error($e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
            return response()->json([
                'status' => 'Error',
                'message' => 'Transaction callback event processing failed: ' . $e->getMessage(),
                'event' => $event,
            ], 500);
        }
    }

    // Generate ETR QR code
    public function createQRCode($model, $modelName)
    {
        // extract invoice no
        $params = [];
        parse_str(parse_url($model->etims_url, PHP_URL_QUERY), $params);
        $invoiceNum = $params['Data'];
        // generate QR code
        $timestamp = date('Y_m_d_H_i_s');
        $filename = "{$modelName}-({$invoiceNum})-{$timestamp}.png";
        $qrCode = new QrCode($model->etims_url);
        $qrCode->writeFile(Storage::disk('public')->path("qr".DIRECTORY_SEPARATOR."{$filename}"));
        $model->update(['etims_qrcode' => $filename]);
        return $filename;
    }    
}
