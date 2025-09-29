<?php

namespace App\Jobs;

use App\Models\product\Product;
use App\Models\product\ProductVariation;
use App\Models\productcategory\Productcategory;
use App\Models\productvariable\Productvariable;
use DB;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class CopyTenantProducts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Wright Trading
        $from = null; 
        // Paschal Construction
        $to   = null; 
        $warehouse = null; // default warehouse

        /** Note **/
        // Ensure slug_id columns have been set - they're unique joining columns between 
        // old tenant and new tenant

        DB::disableQueryLog();
        Log::info("Copying from tenant {$from} to {$to}");

        DB::transaction(function () use ($from, $to, $warehouse) {
            // ---------- 1) PARENTS: UNIT OF MEASURE ----------
            Log::info('Upserting units of measure...');
            $unitsQ = Productvariable::query()->where('ins', $from);                

            $unitsQ->chunkById(2000, function($chunk) use ($to) {
                $rows = $chunk->map(function(Productvariable $uom) use ($to) {
                    $data = $uom->toArray();
                    if (isset($data['id'])) unset($data['id']);
                    return array_replace($data, [
                        'ins'       => $to,
                        'user_id'   => null,
                        'created_at'=> now(),
                        'updated_at'=> now(),
                    ]);
                })->all();

                if ($rows) {
                    foreach ($rows as $row) {
                        Productvariable::updateOrCreate(
                            ['ins' => $row['ins'], 'title' => $row['title']],
                            $row
                        );
                    }
                }
            });
            Log::info('Upserted units-of-measure: ' . Productvariable::where('ins', $to)->count());

            // ---------- 2) MAP: OLD UNIT OF MEASURE ID -> NEW UNIT OF MEASURE ID ----------
            Log::info('Building unit of measure ID map...');
            // We resolve by joining on slug_id across tenants.
            $unitsMap = DB::table('product_variables as p_old')
                ->join('product_variables as p_new', function($j) use ($from, $to) {
                    $j->on('p_old.slug_id', '=', 'p_new.slug_id')
                      ->where('p_old.ins', '=', $from)
                      ->where('p_new.ins', '=', $to);
                })
                ->select('p_old.id as old_id', 'p_new.id as new_id')
                ->pluck('new_id', 'old_id'); // [old_id => new_id]

            // ---------- 3) UPDATE: OLD BASE-UNIT ID -> NEW BASE-UNIT ID ---------------
            $newUnits = Productvariable::where('ins', $to)->get();
            foreach ($newUnits as $unit) {
                $newUnitId = $unitsMap[$unit->base_unit_id] ?? null;
                if ($newUnitId) {
                    $unit->update(['base_unit_id' => $newUnitId]);
                } elseif ($unit->base_unit_id) {
                    $unit->update(['base_unit_id' => null]);
                }
            }

            // ---------- 1) PARENTS: PRODUCT CATEGORIES ----------
            Log::info('Upserting product categories...');
            $categoriesQ = Productcategory::query()->where('ins', $from);                

            $categoriesQ->chunkById(2000, function($chunk) use ($to) {
                $rows = $chunk->map(function(Productcategory $pc) use ($to) {
                    $data = $pc->toArray();
                    if (isset($data['id'])) unset($data['id']);
                    return array_replace($data, [
                        'ins'       => $to,
                        'user_id'   => null,
                        'created_at'=> now(),
                        'updated_at'=> now(),
                    ]);
                })->all();

                if ($rows) {
                    foreach ($rows as $row) {
                        Productcategory::updateOrCreate(
                            ['ins' => $row['ins'], 'title' => $row['title']],
                            $row
                        );
                    }
                }
            });
            Log::info('Upserted product-categories: ' . Productcategory::where('ins', $to)->count());

            // ---------- 2) MAP: OLD PRODUCT CATEGORY ID -> NEW PRODUCT CATEGORY ID ----------
            Log::info('Building product category ID map...');
            // We resolve by joining on slug_id across tenants.
            $productCategoriesMap = DB::table('product_categories as p_old')
                ->join('product_categories as p_new', function($j) use ($from, $to) {
                    $j->on('p_old.slug_id', '=', 'p_new.slug_id')
                      ->where('p_old.ins', '=', $from)
                      ->where('p_new.ins', '=', $to);
                })
                ->select('p_old.id as old_id', 'p_new.id as new_id')
                ->pluck('new_id', 'old_id'); // [old_id => new_id]

            // ---------- 1) PARENTS: PRODUCTS ----------
            Log::info('Upserting products...');
            $productsQ = Product::query()->where('ins', $from);             

            $productsQ->chunkById(2000, function($chunk) use ($to, $productCategoriesMap, $unitsMap) {
                $rows = $chunk->map(function(Product $p) use ($to, $productCategoriesMap, $unitsMap) {
                    $newProductCategoryId = $productCategoriesMap[$p->productcategory_id] ?? null;
                    $newUnitId = $unitsMap[$p->unit_id] ?? null;

                    $data = $p->toArray();
                    if (isset($data['id'])) unset($data['id']);
                    return array_replace($data, [
                        'ins'               => $to,
                        'productcategory_id'=> $newProductCategoryId,
                        'unit_id'           => $newUnitId,
                        'user_id'           => null,
                        'created_at'        => now(),
                        'updated_at'        => now(),
                    ]);
                })->all();

                if ($rows) {
                    foreach ($rows as $row) {
                        Product::updateOrCreate(
                            ['ins' => $row['ins'], 'name' => $row['name']],
                            $row
                        );
                    }
                }
            });
            Log::info('Upserted products: ' . Product::where('ins', $to)->count());

            // ---------- 2) MAP: OLD PRODUCT ID -> NEW PRODUCT ID ----------
            Log::info('Building product ID map...');
            // We resolve by joining on SKU across tenants.
            $productsMap = DB::table('products as p_old')
                ->join('products as p_new', function($j) use ($from, $to) {
                    $j->on('p_old.slug_id', '=', 'p_new.slug_id')
                      ->where('p_old.ins', '=', $from)
                      ->where('p_new.ins', '=', $to);
                })
                ->select('p_old.id as old_id', 'p_new.id as new_id')
                ->pluck('new_id', 'old_id'); // [old_id => new_id]

            // ---------- 3) CHILDREN: PRODUCT VARIATIONS ----------
            Log::info('Upserting product variations...');
            $variationsQ = ProductVariation::query()->where('ins', $from);

            $variationsQ->chunkById(2000, function($chunk) use ($to, $warehouse, $productsMap, $productCategoriesMap) {
                $rows = [];
                foreach ($chunk as $pv) {
                    $newProductId = $productsMap[$pv->parent_id] ?? null;
                    $newProductCategoryId = $productCategoriesMap[$pv->productcategory_id] ?? null;
                    $newWarehouseId = $pv->warehouse_id? $warehouse : null;
                    if (!$newProductId) continue; // parent not copied, skip

                    $data = $pv->toArray();
                    if (isset($data['id'])) unset($data['id']);
                    $rows[] = array_replace($data, [
                        'ins'               => $to,
                        'parent_id'         => $newProductId,
                        'productcategory_id'=> $newProductCategoryId, 
                        'warehouse_id'      => $newWarehouseId, 
                        'qty'               => 0,
                        'asset_account_id'  => null,
                        'exp_account_id'    => null,
                        'created_at'        => now(),
                        'updated_at'        => now(),
                    ]);
                }

                if ($rows) {
                    foreach ($rows as $row) {
                        ProductVariation::updateOrCreate(
                            ['ins' => $row['ins'], 'parent_id' => $row['parent_id'], 'name' => $row['name']],
                            $row
                        );
                    }
                }
            });
            Log::info('Upserted product-variations: ' . ProductVariation::where('ins', $to)->count());

            // ---------- 4) UPDATE: PRODUCT VARIATION CATEGORY ID ---------------
            $productVariations = ProductVariation::where('ins', $to)
                ->where(fn($q) => $q->whereNull('productcategory_id')->orwhere('productcategory_id', 0))
                ->with('product')
                ->get();
            foreach ($productVariations as $pv) {
                $categoryId = $pv->product->productcategory_id;
                if ($categoryId) {
                    $pv->update(['productcategory_id' => $categoryId]);
                }
            }

            // Add more tables in parent→child order using the same pattern:
            // parents (customers, categories) → build maps → children (addresses, order_items, etc.)
        });

        Log::info('Done.');
        return 1;
    }
}
