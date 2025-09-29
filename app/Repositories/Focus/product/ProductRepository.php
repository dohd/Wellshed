<?php

namespace App\Repositories\Focus\product;

use App\Models\items\PurchaseorderItem;
use App\Models\product\ProductVariation;
use DB;
use App\Models\product\Product;
use App\Exceptions\GeneralException;
use App\Models\items\PurchaseItem;
use App\Repositories\BaseRepository;
use DateTime;
use Error;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use App\Models\supplier_product\SupplierProduct;
use App\Models\productcategory\Productcategory;

/**
 * Class ProductRepository.
 */
class ProductRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Product::class;

    /**
     *file_path .
     * @var string
     */
    protected $file_path = 'img' . DIRECTORY_SEPARATOR . 'products' . DIRECTORY_SEPARATOR;

    /**
     * Storage Class Object.
     * @var \Illuminate\Support\Facades\Storage
     */
    protected $storage;

    /**
     * Constructor to initialize class objects
     */
    public function __construct()
    {
        $this->storage = Storage::disk('public');
    }

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query();
        $q->with([
            'standard' => fn($q) => $q->select('parent_id', 'code', 'purchase_price', 'expiry','moq','fifo_cost'), 
            'variations' => fn($q) => $q->select('parent_id', 'qty'),
            'unit' => fn($q) => $q->select('id', 'code'), 
            'category' => fn($q) => $q->select('id', 'title', 'rel_id', 'child_id'), 
            'category.parent_category',
            'category.child',
            'parent_cat',
            'child_cat',
            'grandChildCategory',
        ]);
        // by default only load products in stock 
        if (request('status') == 'out_of_stock') $q->whereHas('variations', fn ($q) => $q->where('qty', '<', 1));
        elseif (request('status') == 'in_stock') $q->whereHas('variations', fn ($q) => $q->where('qty', '>', 0));
        elseif (request('status') == 'all') $q->whereHas('variations');
        
        $q->when(request('warehouse_id'), function ($q) {
            $q->whereHas('variations', function ($q) {
                if (request('warehouse_id') == 'none') $q->whereNull('warehouse_id');
                else $q->where('warehouse_id', request('warehouse_id'));
            });
        });

        if (request('sub_sub_category_id')) {
            $q->where('productcategory_id', request('sub_sub_category_id'));
        } elseif (request('sub_category_id')) {
            $sub_cat_ids = Productcategory::where('child_id',request('sub_category_id'))->pluck('id')->toArray();
            $q->where(function ($query) use ($sub_cat_ids) {
                if (!empty($sub_cat_ids)) {
                    $query->whereIn('productcategory_id', $sub_cat_ids)
                          ->orWhere('productcategory_id', request('sub_category_id'));
                } else {
                    $query->where('productcategory_id', request('sub_category_id'));
                }
            });
        } elseif (request('category_id')) {
            $cat_ids = Productcategory::where('rel_id',request('category_id'))->pluck('id')->toArray();
            $q->where(function ($query) use ($cat_ids) {
                if (!empty($cat_ids)) {
                    $query->whereIn('productcategory_id', $cat_ids)
                          ->orWhere('productcategory_id', request('category_id'));
                } else {
                    $query->where('productcategory_id', request('category_id'));
                }
            });
           
        }
        
        $q->when(request('stock_type'), fn ($q) => $q->where('stock_type', request('stock_type')));

        // return collect();
        // return $q->limit(500)->get();
        return $q->get();
    }

    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @return bool
     * @throws GeneralException
     */
    public function create(array $input)
    {
        // validate stock keeping unit
        $sku_exists = Product::where('sku', $input['sku'])->count();
        if (empty($input['sku']) || $sku_exists) {
            $input['sku'] = substr($input['name'], 0, 1) . substr($input['name'], -1) . rand(1, 10000);
        }

        $account_ids = request()->only('asset_account_id', 'exp_account_id'); // extract account_ids
        unset($input['asset_account_id'], $input['exp_account_id']);
        
        DB::beginTransaction();

        $input['taxrate'] = numberClean($input['taxrate']);
        $result = Product::create($input);

        // units        
        if (empty($input['compound_unit_id'])) $input['compound_unit_id'] = array();    
        $result->units()->attach(array_merge([$result->unit_id], $input['compound_unit_id']));

        // product variations
        $variations = [];
        $data_items = Arr::only($input, [
            'price', 'purchase_price','selling_price', 'qty', 'code', 'barcode', 'disrate', 'alert', 'expiry', 
            'warehouse_id', 'variation_name', 'image','moq','image_description'
        ]);
        $data_items = modify_array($data_items);
        foreach ($data_items as $item) {
            $item = array_merge($item, $account_ids); // merge account_ids
            if (!@$item['image']) $item['image'] = 'example.png';
            $item['name'] = $item['variation_name'];
            unset($item['variation_name']);

            foreach ($item as $key => $val) {
                if ($key == 'image' && $val != 'example.png') $item[$key] = $this->uploadFile($val);
                if (in_array($key, ['price', 'purchase_price','selling_price', 'disrate', 'qty', 'alert'])) {
                    if ($key != 'disrate' && !$val) 
                        throw ValidationException::withMessages([$key . ' is required!']);
                    $item[$key] = numberClean($val);
                }
                if ($key == 'barcode' && !$val)
                    $item[$key] =  rand(100, 999) . rand(0, 9) . rand(1000000, 9999999) . rand(0, 9);
                if ($key == 'code' && !$val){
                    $productcategory = Productcategory::where('id',$input['productcategory_id'])->first();
                    $prefix = $productcategory->code_initials;
                    $codes = ProductVariation::where('productcategory_id', $input['productcategory_id'])->where('code', '!=','')->get(['code'])->toArray();
                    $newCode = $this->addMissingOrNextCode($codes, $prefix);
                    $item[$key] =  $newCode;
                }
                   

                if ($key == 'expiry') {
                    $expiry = new DateTime(date_for_database($val));
                    $now = new DateTime(date('Y-m-d'));
                    if ($expiry > $now) $item[$key] = date_for_database($val);
                    else $item[$key] = null;
                }
            }

            $variations[] =  array_replace($item, [
                'parent_id' => $result->id,
                'productcategory_id' => $input['productcategory_id'],
                'ins' => auth()->user()->ins,
                'name' => @$item['variation_name'] ?: $result->name,
            ]);
        }
        ProductVariation::insert($variations);   
    
        if ($result) {
            DB::commit();
            return $result;
        }
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Product $product
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update($product, array $input)
    {
        // validate stock keeping unit
        $sku_exists = Product::where('sku', $input['sku'])->where('id', '!=', $product->id)->count();
        if (empty($input['sku']) || $sku_exists) {
            $input['sku'] = substr($input['name'], 0, 1) . substr($input['name'], -1) . rand(1, 10000);
        }
        $account_ids = request()->only('asset_account_id', 'exp_account_id'); // extract account_ids
        unset($input['asset_account_id'], $input['exp_account_id']);
    
        DB::beginTransaction();

        $input['taxrate'] = numberClean($input['taxrate']);
        $result = $product->update($input);

        // update units        
        if (empty($input['compound_unit_id'])) $input['compound_unit_id'] = array();
        $product->units()->sync(array_merge([$product->unit_id], $input['compound_unit_id']));   

        // variations data
        $data_items = Arr::only($input, [
            'v_id', 'price', 'purchase_price','selling_price', 'qty', 'code', 'barcode', 'disrate', 'alert', 'expiry', 
            'warehouse_id', 'variation_name', 'image','moq','image_description','existing_image'
        ]);
        $data_items = modify_array($data_items);

        // delete omitted product variations
        $variation_ids = array_map(function ($v) { return $v['v_id']; }, $data_items);
        $product->variations()->whereNotIn('id', $variation_ids)->delete();
        
        // create or update product variation
        foreach ($data_items as $item) {
            $item = array_merge($item, $account_ids); // merge account_ids
            if (!@$item['image'] && empty($item['existing_image'])) $item['image'] = 'example.png';
            $item['name'] = $item['variation_name'];
            unset($item['variation_name']);

            foreach ($item as $key => $val) {
                if ($key == 'image' && $val != 'example.png' && $val != @$item['existing_image']) $item[$key] = $this->uploadFile($val);
                if (in_array($key, ['price', 'purchase_price','selling_price', 'disrate', 'qty', 'alert'])) {
                    if ($key != 'disrate' && !$val) 
                        throw ValidationException::withMessages([$key . ' is required!']);
                    $item[$key] = numberClean($val);
                }
                if ($key == 'barcode' && !$val)
                    $item[$key] =  rand(100, 999) . rand(0, 9) . rand(1000000, 9999999) . rand(0, 9);
                if ($key == 'code')
                    {
                        if (empty($item[$key])) {
                            $productcategory = Productcategory::where('id',$input['productcategory_id'])->first();
                            $prefix = $productcategory->code_initials;
                            //Get the codes from productvariation
                            $codes = ProductVariation::where('productcategory_id', $input['productcategory_id'])->where('code', '!=','')->get(['code'])->toArray();
                            $newCode = $this->addMissingOrNextCode($codes, $prefix);
                            $item[$key] =  $newCode;
                        }
                        elseif ($item[$key]) {
                           // dd($item[$key]);
                            $code_ext = ProductVariation::where('code', $item[$key])->first();
                            if ($code_ext) {
                                $productcategory = Productcategory::where('id',$input['productcategory_id'])->first();
                                $prefix = $productcategory->code_initials;
                                $code_substr = substr($item[$key], 0, 2);
                                if ($code_substr == $prefix) {
                                    $no_of_times = ProductVariation::where('code', $item[$key])->count();
                                    if($no_of_times > 1){
                                        $codes = ProductVariation::where('productcategory_id', $input['productcategory_id'])->where('code', '!=','')->get(['code'])->toArray();
                                        $newCode = $this->addMissingOrNextCode($codes, $prefix);
                                        $item[$key] =  $newCode;
                                    }
                                    $item[$key] = $item[$key];
                                }
                                else {
                                    $codes = ProductVariation::where('productcategory_id', $input['productcategory_id'])->where('code', '!=','')->get(['code'])->toArray();
                                    $newCode = $this->addMissingOrNextCode($codes, $prefix);
                                    $item[$key] =  $newCode;
                                }
                            }
                        }
                    }
                if ($key == 'expiry') {
                    $expiry = new DateTime(date_for_database($val));
                    $now = new DateTime(date('Y-m-d'));
                    if ($expiry > $now) $item[$key] = date_for_database($val);
                    else $item[$key] = null;
                }
            }

            $item = array_replace($item, [
                'parent_id' => $product->id,
                'productcategory_id' => $input['productcategory_id'],
            ]);
            $new_item = ProductVariation::firstOrNew(['id' => $item['v_id']]);
           if (count($new_item->supplier_products) > 0) {
                foreach ($new_item->supplier_products as $supplier_product) {
                    $supplier_product->product_code = $item['code'];
                    $supplier_product->uom = @$product->unit->code;
                    $supplier_product->update();
                }
           }
           if(count($new_item->client_products) > 0) {
               foreach ($new_item->client_products as $client_product) {
                    $client_product->product_code = $item['code'];
                    $client_product->uom = @$product->unit->code;
                    $client_product->update();
                }
           }
           
            $new_item->fill($item);
            unset($new_item->v_id);
            $new_item['productcategory_id'] = $input['productcategory_id'];
            $new_item->save();
        }

        if ($result) {
            DB::commit();
            return $result;
        }
    }

    public function addMissingOrNextCode(&$codes, $prefix) {
        if (empty($codes)) {
            // If the codes array is empty, create the first code
            $newCode = $prefix . '0001';
            $codes[] = ['code' => $newCode];
            return $newCode;
        }
    
        // Extract the codes and sort them
        $codeList = array_column($codes, 'code');
        sort($codeList);
    
        // Extract the numeric parts
        $numericParts = array_map(function($code) {
            return (int)substr($code, 2);
        }, $codeList);
    
        // Find the missing number or the next number
        $missingNumber = null;
        for ($i = 0; $i < count($numericParts) - 1; $i++) {
            if ($numericParts[$i + 1] - $numericParts[$i] > 1) {
                $missingNumber = $numericParts[$i] + 1;
                break;
            }
        }
    
        if ($missingNumber === null) {
            // No missing number found, add the next incremented code
            $nextNumber = max($numericParts) + 1;
        } else {
            // Missing number found
            $nextNumber = $missingNumber;
        }
    
        // Format the new code
        $newCode = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    
        // Add the new code to the array
        $codes[] = ['code' => $newCode];
    
        // Return the new code
        return $newCode;
    }
    /**
     * For deleting the respective model from storage
     *
     * @param Product $product
     * @return bool
     * @throws GeneralException
     */
    public function delete(Product $product)
    {
        $error_msg = '';
        foreach ($product['variations'] as $product_variation) {
            $quote = @$product_variation->quote_item->quote;
            if ($quote) {
                $type = $quote->bank_id? 'PI' : 'Quote';
                $error_msg = "Product is attached to {$type} number {$quote->tid} !";
            }
            $purchase = @$product_variation->purchase_item->purchase;
            if ($purchase) $error_msg = 'Product is attached to Purchase number {$purchase->tid} !';
            $product_supplier = @$product_variation->product_supplier->product;
            if ($product_supplier) $error_msg = 'Product is attached to Product Code {$product->code} !';
            $purchaseorder = @$product_variation->purchase_order_item->purchaseorder;
            if ($purchaseorder) $error_msg = 'Product is attached to Purchase Order number {$purchaseorder->tid} !';
            $project_stock = @$product_variation->project_stock_item->project_stock;
            if ($project_stock) $error_msg = 'Product is attached to Issued Project Stock number {$project_stock->tid} !';
            $goodsreceivenote = @$product_variation->grn_item->goodsreceivenote;
            if ($goodsreceivenote) $error_msg = 'Product is attached to Goods Receive Note number {$goodsreceivenote->tid} !';
            // EFRIS check
            if (config('services.efris.base_url')) {
                $efris_good = @$product_variation->efris_good;
                if ($efris_good) $error_msg = 'Product has been uploaded to EFRIS server, Goods Code: {$efris_good->goods_code} !';
            }

            if ($error_msg) break;
        }
        if ($error_msg) throw ValidationException::withMessages([$error_msg]);

        DB::beginTransaction();
        $product->variations()->delete();
        if ($product->delete()) {
            DB::commit();
            return true;
        }
    }

    /**
     * Upload logo image
     * @param mixed $file
     */
    public function uploadFile($file)
    {
        $file_name = time() . $file->getClientOriginalName();

        $this->storage->put($this->file_path . $file_name, file_get_contents($file->getRealPath()));

        return $file_name;
    }

    /**
     * Remove logo or favicon icon
     * @param Product $product
     * @param string $field
     * @return bool
     */
    public function removePicture(Product $product, $field)
    {
        $file = $this->file_path . $product->type;
        if ($product->type && $this->storage->exists($file))
            $this->storage->delete($file);

        if ($product->update([$field => null]))
            return true;

        throw new GeneralException(trans('exceptions.backend.settings.update_error'));
    }

    /**
     * LIFO (Last in First Out) Inventory valuation method
     * accounting principle
     * 
     * @return float
     */
    public function eval_purchase_price(int $id, float $qty, float $rate): float
    {
        if ($qty == 0) return $rate;

        /** Using Purchase Items */
        $price_cluster = PurchaseItem::select(DB::raw('rate, COUNT(*) as count'))
            ->where(['type' => 'Stock', 'item_id' => $id])
            ->groupBy('rate')->orderBy('updated_at', 'asc')->get();

        /** Using Purchase Order Items */
//        $price_cluster = PurchaseorderItem::select(DB::raw('rate, COUNT(*) as count'))
//            ->where(['type' => 'Stock', 'item_id' => $id])
//            ->groupBy('rate')->orderBy('updated_at', 'asc')->get();

        $qty_range = range(1, $qty);
        foreach ($price_cluster as $cluster) {
            $subset = array_splice($qty_range, 0, $cluster->count);
            if (!$subset) $subset = $qty_range;
            if ($qty >= current($subset) && $qty <= end($subset)) {
                $rate = $cluster->rate;
                break;
            } 
        }

        return $rate;
    }
}
