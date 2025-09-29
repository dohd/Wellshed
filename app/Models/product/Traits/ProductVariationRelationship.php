<?php

namespace App\Models\product\Traits;

use App\Models\client_product\ClientProduct;
use App\Models\items\GoodsreceivenoteItem;
use App\Models\items\OpeningStockItem;
use App\Models\items\ProjectstockItem;
use App\Models\items\PurchaseItem;
use App\Models\items\PurchaseorderItem;
use App\Models\items\QuoteItem;
use App\Models\product\Product;
use App\Models\product\ProductMeta;
use App\Models\warehouse\Warehouse;
use App\Models\pricegroup\Pricegroup;
use App\Models\pricegroup\PriceGroupVariation;
use App\Models\product\EfrisGood;
use App\Models\project\BudgetItem;
use App\Models\sale_return\SaleReturnItem;
use App\Models\stock_adj\StockAdjItem;
use App\Models\stock_issue\StockIssueItem;
use App\Models\supplier_product\SupplierProduct;

/**
 * Class ProductRelationship
 */
trait ProductVariationRelationship
{
    public function efris_good()
    {
        return $this->hasOne(EfrisGood::class, 'productvar_id');
    }

    public function openingstock_item()
    {
        return $this->hasOne(OpeningStockItem::class, 'productvar_id');
    }

    public function quote_item()
    {
        return $this->hasOne(QuoteItem::class, 'product_id');
    }
    public function budget_item()
    {
        return $this->hasOne(BudgetItem::class, 'product_id');
    }

    public function purchase_item()
    {
        return $this->hasOne(PurchaseItem::class, 'item_id')->where('type', 'Stock');
    }

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class, 'item_id')->where('type', 'Stock');
    }

    public function purchaseorder_item()
    {
        return $this->hasOne(PurchaseorderItem::class, 'item_id');
    }

    public function project_stock_item()
    {
        return $this->hasOne(ProjectstockItem::class, 'product_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'parent_id');
    }

    public function quote_service_items()
    {
        return $this->belongsTo(Product::class, 'parent_id')->where('stock_type', 'service');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function product_serial()
    {
        return $this->hasMany(ProductMeta::class, 'ref_id', 'id')->where('rel_type', '=', 2)->withoutGlobalScopes();
    }

    public function v_prices()
    {
        return $this->hasOne(PriceGroupVariation::class, 'product_variation_id', 'id');
    }

    public function variation_price()
    {
        return $this->hasOneThrough(Pricegroup::class, PriceGroupVariation::class, 'product_variation_id', 'pricegroup_id');
    }

    public function stockIssueItems()
    {
        return $this->hasMany(StockIssueItem::class, 'productvar_id');
    }


    public function stock_issue_item()
    {
        return $this->hasMany(StockIssueItem::class, 'productvar_id');
    }
    
    public function purchase_items()
    {
        return $this->hasMany(PurchaseItem::class, 'item_id')->where('type', 'Stock');
    }

    public function purchaseorder_items()
    {
        return $this->hasMany(PurchaseorderItem::class, 'product_id');
    }
    
    public function sale_return_item()
    {
        return $this->hasMany(SaleReturnItem::class, 'productvar_id');
    }
    public function stock_adj_item()
    {
        return $this->hasMany(StockAdjItem::class, 'productvar_id');
    }
    //Add Supplier Pricelist relationship
    public function supplier_products()
    {
        return $this->hasMany(SupplierProduct::class, 'product_id');
    }
    //Add Client Pricelist relationship
    public function client_products()
    {
        return $this->hasMany(ClientProduct::class, 'item_id');
    }
}
