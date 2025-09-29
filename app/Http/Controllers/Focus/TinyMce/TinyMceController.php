<?php

namespace App\Http\Controllers\Focus\TinyMce;

use App\Http\Controllers\Controller;
use App\Models\companyNotice\CompanyNoticeTempImage;
use App\Models\welcomeMessage\WelcomeMessageTempImage;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TinyMceController extends Controller
{
    public function storePicture(Request $request, $module)
    {

        try {
            if ($request->hasFile('file')) {

                $file = $request->file('file');

                if ($module === 'welcome') {

                    $path = $file->store('uploads/temp/welcome-message-photos/' . Auth::user()->ins, 'public');
                    $tempImage = new WelcomeMessageTempImage();
                }
                else if ($module === 'notice') {

                    $path = $file->store('uploads/temp/notice-photos/' . Auth::user()->ins, 'public');
                    $tempImage = new CompanyNoticeTempImage();
                }

                $tempImage->fill([
                    'location' => asset('storage/' . $path),  // Full URL for the image
                    'filename' => basename($path)  // Only the unique filename
                ]);
                $tempImage->save();

                return response()->json([
                    'location' => asset('storage/' . $path),  // Full URL for the image
                    'filename' => basename($path)  // Only the unique filename
                ]);
            }

            return response()->json(['error' => 'No file uploaded'], 400);
        } catch (Exception $e) {

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function download(Request $request){

        return 'HOORAAH';
    }
}