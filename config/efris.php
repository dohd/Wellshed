<?php

/**
 * EFRIS API Payload Structure
 */
return [
  // Private Key
  'private_key' => is_file(storage_path('efris.privkey.pem'))? file_get_contents(storage_path('efris.privkey.pem')) : '',
  // AES Key (Token)
  'aes_key' => is_file(storage_path('efris.aes.key'))? file_get_contents(storage_path('efris.aes.key')) : '',
  
  // Request Body
  'req_body' => (object) [
    "data" => (object) [
      "content" => "",
      "signature" => "",
      "dataDescription" => (object) [
        "codeType" => "0",
        "encryptCode" => "1",
        "zipCode" => "0"
      ]
    ],
    "globalInfo" => (object) [
      "appId" => "AP04",
      "version" => "1.1.20191201",
      "dataExchangeId" => "9230489223014123",
      "interfaceCode" => "",
      "requestCode" => "TP",
      "requestTime" => date('Y-m-d H:i:s'),
      "responseCode" => "TA",
      "userName" => "admin",
      "deviceMAC" => "FFFFFFFFFFFF",
      "deviceNo" => "",
      "tin" => "",
      "brn" => "",
      "taxpayerID" => "1",
      "longitude" => "",
      "latitude" => "",
      "agentType" => "0",
      "extendField" => (object) [
        "responseDateFormat" => "dd/MM/yyyy",
        "responseTimeFormat" => "dd/MM/yyyy HH:mm:ss",
        "referenceNo" => "21PL010020807",
        "operatorName" => "administrator",
        "offlineInvoiceException" => (object) [
          "errorCode" => "",
          "errorMsg" => ""
        ]
      ]
    ],
    "returnStateInfo" => (object) [
      "returnCode" => "",
      "returnMessage" => ""
    ]
  ],

  // Stock Good
  'stock_good' => [
    // "goodsName" => "Radio", // required
    // "goodsCode" => "Radio", // required
    // "measureUnit" => "101", // 101:Per Stick on System Dictionary
    // "unitPrice" => "100000", // required
    // "currency" => "101", // 101:UGX on System Dictionary
    // "commodityCategoryId" => "43221704", // Radio Core Equipment
    // "haveExciseTax" => "102", // 102:No, 101:Yes
    // "havePieceUnit" => '101', // 102:No, 101:Yes
    // 'pieceUnitPrice' => "100000", // required
    // 'pieceMeasureUnit' => '101', // required
    // "packageScaledValue" => "1", // required
    // "pieceScaledValue" => "1", // required
    // "stockPrewarning" => "0", // required

    "goodsName" => "", // required
    "goodsCode" => "", // md5 hash of goodsName (lowercase without spaces)
    "measureUnit" => "UN", // UN:Unit, 101:Per-Stick
    "unitPrice" => "", // required
    "currency" => "101", // 101:UGX on System Dictionary
    "commodityCategoryId" => "72151207", // From commodity category list e.g 72151207:Heating & Cooling & AC HVAC installation and maintenance
    "haveExciseTax" => "102", // 102:No, 101:Yes
    "havePieceUnit" => '101', // 102:No, 101:Yes
    'pieceUnitPrice' => "", // same as unit Price but may differ
    'pieceMeasureUnit' => 'UN', // UN:Unit, 101:Per-Stick
    "packageScaledValue" => "1", // required
    "pieceScaledValue" => "1", // required
    "stockPrewarning" => "0", // required
  ],

  // Service Good
  'service_good' => [
    // "goodsName" => "Network Planning Services", // required
    // "goodsCode" => "81111706NPS01", // unique code
    // "measureUnit" => "UN",
    // "unitPrice" => "20000000", // nullable
    // "currency" => "101", // UGX as per System Dictionary, Currency Type
    // "commodityCategoryId" => "81111706", // 81111706:Network Planning Services
    // "haveExciseTax" => "102", // 102:No, 101:Yes
    // "havePieceUnit" => '102', // 102:No, 101:Yes

    "goodsName" => "", // required
    "goodsCode" => "", // md5 hash of goodsName (lowercase without spaces)
    "measureUnit" => "UN", // UN:Unit
    "unitPrice" => "",
    "currency" => "101", // 101:UGX on System Dictionary
    "commodityCategoryId" => "72151207", // From commodity category list e.g 72151207:Heating & Cooling & AC HVAC installation and maintenance
    "stockPrewarning" => "",
    "haveExciseTax" => "102", // 102:No, 101:Yes
    "havePieceUnit" => '102', // 102:No, 101:Yes
  ],

  // Stock Good In
  'stock_good_in' => [
    'goodsStockIn' => [
      'operationType' => '101', // 101:Increase, 
      'stockInType' => '102', // 101:Import, 102:Local-Purchase, 103:Manufacture/Assembly, 104:Opening Stock
      'supplierName' => '', // required if 102
      'supplierTin' => '', // required if 102
      'remarks' => 'Increase Inventory',
      'rollBackIfError' => '1',
      'stockInDate' => '',
    ],
    'goodsStockInItem' => [
      // [
      //     'goodsCode' => 'Radio',
      //     'quantity' => '10',
      //     'unitPrice' => '100000',
      // ],
    ],
  ],

  // Stock Good Out
  'stock_good_out' => [
    'goodsStockIn' => [
      'operationType' => '102', // 102:Decrease
      'adjustType' => '104', // 104:Others, 105:Raw-Materials, 103:Personal Uses, 102:Damaged Goods,  101:Expired Goods
      'remarks' => 'Decrease Inventory', // Reason for adjustment required
      'rollBackIfError' => '1',
    ],
    'goodsStockInItem' => [
      // [
      //     'goodsCode' => 'Radio',
      //     'quantity' => '2',
      //     'unitPrice' => '100000',
      // ],
    ],
  ],

  // Invoice Good
  'invoice_good' => [
    'item' => '', // goodsName
    'itemCode' => '', // goodsCode
    'qty' => '', // required
    'unitOfMeasure' => 'UN',
    'unitPrice' => '', // required
    'total' => '', // required
    'taxRate' => '', // required
    'tax' => '', // required
    'orderNumber' => '', // key index from goods list
    'discountFlag' => '2', // 2:Non-discount Item
    'exciseFlag' => '2', // 2:Not-excise
    'deemedFlag' => '2', // 2:Not-deemed                
    'goodsCategoryId' => '', // required
  ],

  // Invoice Body
  'invoice_body' => [
    'sellerDetails' => [
      'tin' => '',
      'legalName' => '',
      // 'emailAddress' => '',
      // 'branchName' => '',
      'referenceNo' => '',
    ],
    'basicInformation' => [
      'deviceNo' => '', // required
      'issuedDate' => '', // required
      'operator' => '', // required
      'currency' => 'UGX',
      'invoiceType' => '1', // 1:Invoice, 5:CreditMemo, 4:DebitNote
      'invoiceKind' => '1', // 1:Invoice, 2:Receipt
      'dataSource' => '103', // 103:Web Service API
      'invoiceIndustryCode' => '101', // 101:General Industry
    ],
    'buyerDetails' => [
      // 'BuyerCitizenship' => 'Ugandan',
      // 'BuyerSector' => '1',
      'buyerType' => '1', // 0:B2B, 1:B2C, 2:Foreigner, 3:B2G
      'buyerTin' => '', // required if B2B or B2G
      'buyerLegalName' => '', // required if Walk-In
    ],
    'goodsDetails' => [
      // [
      //   'Item' => 'Radio',
      //   'ItemCode' => 'Radio',
      //   'Quantity' => '2',
      //   'UnitOfMeasure' => '103',
      //   'UnitPrice' => '500000.00',
      //   'Total' => '1000000.00',
      //   'TaxRate' => '0.18',
      //   'Tax' => '180000.00',
      //   'OrderNumber' => '0',
      //   'DiscountFlag' => '2', // Non-discount item
      //   'ExciseFlag' => '2', // 2:Not-excise
      //   'DeemedFlag' => '2',
      //   'GoodsCategoryId' => '43221704',
      //   'GoodsCategoryName' => 'Radio core equipment',
      // ],
    ],
    'taxDetails' => [
      // [
      // 'taxCategoryCode' => '01', // 01:A:Standard, 02:B:Zero, 03:C:Excempt, 04:D:Deemed, 05:E:Excise Duty,... 
      // 'TaxCategory' => 'Standard', // Unrequired
      //   'GrossAmount' => '1000000.00',
      //   'TaxRate' => '0.18',
      //   'TaxAmount' => '180000.00',
      //   'NetAmount' => '820000.00',
      //   'TaxRateName' => 'Value Added Tax',
      // ],
    ],
    'summary' => [
      // 'ItemCount' => '1',
      // 'GrossAmount' => '1000000',
      // 'NetAmount' => '820000',
      // 'TaxAmount' => '180000',
      'ModeCode' => '1', // 1:Online Issuing Receipt Mode
    ],
    'payWay' => [
      // [
      //   'PaymentMode' => '102',
      //   'PaymentAmount' => '1000000.00',
      //   'OrderNumber' => '0',
      // ],
    ],
  ],

  // Credit Note Body
  'credit_note_body' => [
    'oriInvoiceId' => '', // required
    'oriInvoiceNo' => '', // required
    'reasonCode' => '104', // 104:Partial or complete waive-off of the product sale (Dictionary)
    'applicationTime' => date('Y-m-d H:i:s'), // Limit: Original invoice billing time + credit Note Maximum InvoicingDays (Dictionary)
    'invoiceApplyCategoryCode' => '101', // 101:CreditNote
    'currency' => 'UGX', // similar to original invoice
    'source' => '103', // 103:Web-service API
    'goodsDetails' => [
      // [
      //   'Item' => 'Radio',
      //   'ItemCode' => 'Radio',
      //   'Quantity' => '-2',
      //   'UnitOfMeasure' => '103',
      //   'UnitPrice' => '500000.00',
      //   'Total' => '-1000000.00',
      //   'TaxRate' => '0.18',
      //   'Tax' => '-180000.00',
      //   'OrderNumber' => '0',
      //   'DiscountFlag' => '2', // Non-discount item
      //   'ExciseFlag' => '2', // 2:Not-excise
      //   'DeemedFlag' => '2',
      //   'GoodsCategoryId' => '43221704',
      //   'GoodsCategoryName' => 'Radio core equipment',
      // ],
    ],
    'taxDetails' => [
      // [
      // 'taxCategoryCode' => '01', // 01:A:Standard, 02:B:Zero, 03:C:Excempt, 04:D:Deemed, 05:E:Excise Duty,... 
      // 'TaxCategory' => 'Standard', // Unrequired
      //   'GrossAmount' => '-1000000.00',
      //   'TaxRate' => '0.18',
      //   'TaxAmount' => '-180000.00',
      //   'NetAmount' => '-820000.00',
      //   'TaxRateName' => 'Value Added Tax',
      // ],
    ],
    'summary' => [
      // 'ItemCount' => '1',
      // 'GrossAmount' => '-1000000',
      // 'NetAmount' => '-820000',
      // 'TaxAmount' => '-180000',
      'ModeCode' => '1', // 1:Online Issuing Receipt Mode
    ],
    'payWay' => [
      // [
      //   'PaymentMode' => '102',
      //   'PaymentAmount' => '-1000000.00',
      //   'OrderNumber' => '0',
      // ],
    ],
    'buyerDetails' => [
      'buyerType' => '1', // 0:B2B, 1:B2C, 2:Foreigner, 3:B2G
      'buyerTin' => '', // required if B2B or B2G
      'buyerLegalName' => '', // required if Walk-In
    ],
    'basicInformation' => [
      'operator' => '', // required
      'invoiceKind' => '1', // 1:Invoice, 2:Receipt
    ],
  ],
];
