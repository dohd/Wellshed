<?php

namespace App\Http\Controllers\Focus\promotions;

use App\Models\promotions\CompanyPromotionalPrefix;
use App\Models\Company\Company;
use Exception;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class CompanyPromotionalPrefixController extends Controller
{

    public static function getCompanyCode($companyId)
    {

        $promoPrefix = CompanyPromotionalPrefix::where('company_id', $companyId)->first();

        if ($promoPrefix) return $promoPrefix->prefix;

        else {

            $company = Company::find($companyId);

            // Step 1: Extract initials from the company name
            $initials = preg_replace('/\b(\w)/u', '$1', strtoupper($company->cname)); // First letters of each word
            $initials = str_replace(' ', '', $initials); // Remove spaces

            // Step 2: Add more characters from the name if initials are less than 4
            $remaining = str_replace(' ', '', strtoupper($company->cname)); // Remove spaces and make uppercase
            $textPart = substr($initials, 0, 4); // Start with initials

            // Add extra characters to make it exactly 4 characters
            if (strlen($textPart) < 4) {
                $extraChars = substr($remaining, strlen($textPart), 4 - strlen($textPart)); // Take next characters
                $textPart .= $extraChars;
            }

            // Ensure textPart is exactly 4 characters
            $textPart = substr($textPart, 0, 4);

            // Step 3: Append the unique company ID
            $code = $textPart . $companyId;

            $promoPrefix = new CompanyPromotionalPrefix();
            $promoPrefix->company_id = $companyId;
            $promoPrefix->prefix = $code;

            $promoPrefix->save();

            return $promoPrefix->prefix;
        }
    }

    public static function assignCodesToAllCompanies()
    {

        try {

            DB::beginTransaction();

            $companies = Company::all();

            foreach ($companies as $company) {
                $code = self::getCompanyCode($company->id);

                CompanyPromotionalPrefix::create([
                    'company_id' => $company->id,
                    'prefix' => $code
                ]);
            }

            return CompanyPromotionalPrefix::all();

            DB::commit();
        }
        catch (Exception $ex){

            return response()->json([
                'message' => $ex->getMessage(),
                'code' => $ex->getCode(),
                'file' => $ex->getFile(),
                'line' => $ex->getLine(),
            ], 500);
        }
    }
}
