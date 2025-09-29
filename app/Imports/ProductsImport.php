<?php

namespace App\Imports;

use App\Models\product\Product;
use App\Models\product\ProductVariation;
use App\Models\productcategory\Productcategory;
use App\Models\productvariable\Productvariable;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Validation\ValidationException;

class ProductsImport implements ToCollection, WithBatchInserts, WithValidation, WithStartRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    private $rows;

    private $data;

    public function __construct(array $data = [])
    {
        $this->data = $data;
        $this->rows = 0;
    }

    public function collection(Collection $rows)
    {
        // dd($rows);
        if (empty($this->data['warehouse_id'])) {
            throw ValidationException::withMessages(['Warehouse is required!']);
        }
            
        // $category_id = $this->data['category_id'];
        $warehouse_id = $this->data['warehouse_id'];

        foreach ($rows as $key => $row) {
            $row_num = $key+1;

            // headers
            if ($row_num == 1 && $row->count() < 12) {
                throw ValidationException::withMessages(['Missing columns! Use latest CSV file template.']);
            } 

            // products
            if ($row_num > 1) {
                // validations
                if (empty($row[0])) throw ValidationException::withMessages(['Product Name is required on row no. $row_num']);
                if (empty($row[2])) throw ValidationException::withMessages(['Unit is required on row no. $row_num']);
                if (empty($row[11])) throw ValidationException::withMessages(['Product Category is required on row no. $row_num']);

                $row[0] = preg_replace('/\s+/', ' ', trim($row[0])); // product name
                $row[11] = preg_replace('/\s+/', ' ', trim($row[11])); // product category

                $uom = strtolower(trim($row[2]));
                if ($uom == '1 pc') $uom = 'pcs';
                $unit = Productvariable::whereRaw('LOWER(code) LIKE ?', ["%{$uom}%"])->where('unit_type', 'base')->first();

                $productCateg = Productcategory::where('title', 'LIKE', '%'. $row[11] .'%')->orWhere('title', 'Other')->first();
                if (!$productCateg) {
                    $title = $row[11];
                    if ($title == 'NULL' || $title == 0) $title = 'Other';
                    $categCode = strtoupper(substr($title, 0, 1) . substr($title, -1));
                    $productCateg = Productcategory::create([
                        'title' => $title,
                        'extra' => $title,
                        'code_initials' => $categCode,
                        'user_id' => auth()->user()->id,
                        'ins' => auth()->user()->ins,
                    ]);
                }
                $codes = ProductVariation::where('productcategory_id', $productCateg->id)->where('code', '!=','')->get(['code'])->toArray();
                $newCode = addMissingOrNextCode($codes, $productCateg->code_initials);

                // skip duplicate product
                $exists = Product::where('name', 'LIKE', '%'. $row[0] .'%')->exists();
                if ($exists) continue;

                $product = Product::create([
                    'productcategory_id' => $productCateg ? $productCateg->id : 0,
                    'name' => $row[0],
                    'taxrate' => numberClean($row[1]),
                    'product_des' => $row[0],
                    'unit_id' => $unit? $unit->id : null,
                    'code_type' => $row[7],
                    'stock_type' => strtolower($row[10]),
                    'sku' => $row[12],
                    'ins' => $this->data['ins'],
                ]);
                $product->standard()->create([
                    'parent_id' => $product->id,
                    'name' => $product->name,
                    'warehouse_id' => $warehouse_id,
                    'productcategory_id' => $product->productcategory_id,
                    'code' => $newCode,
                    'price' => numberClean($row[3]),
                    'purchase_price' => numberClean($row[4]),
                    'disrate' => numberClean($row[5]),
                    'qty' => numberClean(0),
                    'alert' => numberClean($row[6]),
                    'barcode' => $row[8],
                    'moq' => numberClean($row[9]),
                    'ins' => $product->ins,
                ]);
                if (empty($product['compound_unit_id'])) $product['compound_unit_id'] = [];
                $product->units()->sync(array_merge([$product->unit_id], $product['compound_unit_id']));
                ++$this->rows;
            }            
        }
    }

    public function rules(): array
    {
        return [
            '0' => 'required|string',
            '10' => 'required|string',
        ];
    }

    public function batchSize(): int
    {
        return 200;
    }

    public function getRowCount(): int
    {
        return $this->rows;
    }

    public function startRow(): int
    {
        return 1;
    }
}
