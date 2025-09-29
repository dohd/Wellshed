<?php
/**
 * Datecs API - ETR endpoint payloads 
 */

$invoice = [
  'buyer' => [
    'buyerAddress' => 'string',
    'buyerName' => 'string',
    'buyerPhone' => 'string',
    'pinOfBuyer' => 'P123456789P',
  ],
  'cashier' => 'string',
  'ExemptionNumber' => 'string',
  'invoiceType' => 0,
  'items' => [
    [
      'description' => [
        [
          'value' => 'Sample Invoice',
        ],
      ],
      'discounts' => 0,
      'gtin' => '012345678905',
      'hsCode' => '0011.11.00',
      'name' => 'Test plu name',
      'quantity' => 1,
      'unitPrice' => 1.16,
    ],
  ],
  'lines' => [
    [
      'alignment' => 'Left',
      'format' => 'Bold',
      'lineType' => 'Text',
      'value' => 'Free text',
    ],
  ],
  'payment' => [
    [
      'amount' => 1.16,
      'paymentType' => 0,
    ],
  ],
  'relevantNumber' => 'string',
  'TraderSystemInvoiceNumber' => 'string',
  'transactionType' => 0,
];

$credit_note = [
  'buyer' => [
    'buyerAddress' => 'string',
    'buyerName' => 'string',
    'buyerPhone' => 'string',
    'pinOfBuyer' => 'P123456789P',
  ],
  'cashier' => 'string',
  'ExemptionNumber' => 'string',
  'invoiceType' => 0,
  'items' => [
    [
      'hsCode' => '0011.11.00',
      'name' => 'Test plu name',
      'quantity' => 1,
      'totalAmount' => 10500,
    ],
  ],
  'lines' => [
    [
      'alignment' => 'Left',
      'format' => 'Bold',
      'lineType' => 'Text',
      'value' => 'Free text',
    ],
  ],
  'relevantNumber' => 'string',
  'TraderSystemInvoiceNumber' => 'string',
  'transactionType' => 1,
];

return compact('invoice', 'credit_note');
