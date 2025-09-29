<?php
/**
 * DigiTax API Payload Structure
 */

$businessItem = [
  'item_class_code' => '', // string required
  'item_type_code' => '', // string required
  'item_name' => '', // string required
  'origin_nation_code' => '', // string required
  'package_unit_code' => '', // string required
  'quantity_unit_code' => '', // string required
  'tax_type_code' => '', // string required
  'default_unit_price' => 1, // number required
  'import_item_ref' => '', // string 
  'is_stock_item' => false, // boolean 
  'callback_url' => '' // string 
];

$adjustmentItem = [
  'item_id' => '', // string
  'quantity' => 1,
  'action' => 'add',
  'type' => 'adjustment', // string
];

$saleItem = [
  'id' => '', // string required
  'quantity' => '', // number required
  'unit_price' => '', // number required
  'discount_rate' => '', // number required
  'discount_amount' => '', // number required
  'meta_data' => [
    'invoice_desc' => '' // string
  ],
];

$sale = [
  'customer_pin' => '', // string
  'customer_name' => '', // string
  'trader_invoice_number' => '', // string required (unique)
  'receipt_type_code' => 'S', // string required (Sales Receipt Type Code)
  'payment_type_code' => '03', // string required (Cash or Credit Payment Type Code)
  'invoice_status_code' => '02', // string required (Invoice Status Code)
  'callback_url' => '', // string
  // 'offline_url' => '', // string
  'items' => [],
  // Reverse or third-party invoicing e.g customer on behalf of the vendor
  // 'invoice_auth' => [ 
  //   [
  //     'type' => '', // string
  //     'issuer' => '', // string
  //   ],
  // ],
];

$afterSale = [
  'customer_pin' => '', // string
  'customer_name' => '', // string
  'trader_invoice_number' => '', // string required (unique)
  'receipt_type_code' => 'R', // string required (Credit Note Receipt Type Code)
  'payment_type_code' => '03', // string required (Cash or Credit Payment Type Code)
  'invoice_status_code' => '02', // string required (Invoice Status Code)
  'original_invoice_number' => 0, // number required (eTIMS invoice number of the referenced sale)
  'callback_url' => '', // string
  // 'offline_url' => '', // string
  'items' => [],
  // Reverse or third-party invoicing e.g customer on behalf of the vendor
  // 'invoice_auth' => [ 
  //   [
  //     'type' => '', // string
  //     'issuer' => '', // string
  //   ],
  // ],
];

return [
  'urls' => [
    'items' => '/items',
    'adjustStock' => '/stock/adjust',
    'sales' => '/sales',
  ],
  'structs' => compact('businessItem', 'adjustmentItem', 'sale', 'saleItem', 'afterSale'),
];
