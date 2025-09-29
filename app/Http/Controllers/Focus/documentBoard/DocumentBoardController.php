<?php

namespace App\Http\Controllers\Focus\documentBoard;

use App\Http\Controllers\Controller;
use App\Models\companyNotice\CompanyNotice;
use App\Models\companyNotice\CompanyNoticeImage;
use App\Models\companyNotice\CompanyNoticeTempImage;
use App\Models\documentBoard\DocumentBoard;
use App\Models\documentBoard\WelcomeMessage;
use App\Models\welcomeMessage\WelcomeMessageImage;
use App\Models\welcomeMessage\WelcomeMessageTempImage;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class DocumentBoardController extends Controller
{

    public function index(Request $request)
    {

        if (!access()->allow('manage-company-notice-board')) return redirect()->back();

        if ($request->ajax()) {
            $documents = DocumentBoard::select(['id', 'caption']); // Select only necessary columns for optimization

            return Datatables::of($documents)
                ->addColumn('action', function ($document) {
                    $download = '<a href="' . route('biller.company-notice-board.download', $document->id) . '" class="btn btn-success">Download</a>';
                    $delete = '<form action="' . route('biller.company-notice-board.destroy', $document->id) . '" method="POST" style="display:inline-block;">' .
                        csrf_field() .
                        method_field("DELETE") .
                        '<button type="submit" class="btn btn-danger" onclick="return confirm(\'Are you sure you want to delete this document?\')">Delete</button>' .
                        '</form>';

                    if (!access()->allow('view-company-notice-board')) $view = '';
                    if (!access()->allow('view-company-notice-board')) $download = '';
                    if (!access()->allow('delete-company-notice-board')) $delete = '';

                    return $download . ' ' . $delete;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('focus.documentBoard.index');
    }


    public function central()
    {

        $welcomeMessage = WelcomeMessage::first();
        $welcomeTemplate = "<h1 style=\"text-align: center;\"><span style=\"font-size: 36pt;\"><em>Template Welcome Message</em></span></h1>\r\n<h2 style=\"text-align: center;\"><span style=\"font-size: 24pt;\"><em>Lorem ipsum</em></span></h2>\r\n<p style=\"text-align: center;\"><span style=\"font-size: 24pt;\"><span style=\"font-size: 14pt;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus laoreet dolor eu sapien malesuada, a lacinia justo convallis. Fusce sit amet sapien ac justo cursus malesuada sed et odio. Mauris sit amet mi id turpis sodales facilisis. Integer ut efficitur sapien, et dignissim nulla. Aliquam erat volutpat. Sed vestibulum orci non nisi feugiat, id auctor erat interdum. Cras fringilla est at lacus gravida scelerisque. Sed vehicula neque sit amet lacus sagittis faucibus. Nunc iaculis eros vitae sem vulputate, ut dictum nunc efficitur. Integer bibendum urna nec turpis convallis, a fermentum orci malesuada.</span><em><br><img src=\"https://picsum.photos/300/200\" alt=\"\" width=\"300\" height=\"200\">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <img src=\"https://picsum.photos/400/300\" alt=\"\" width=\"400\" height=\"300\">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;<img src=\"https://picsum.photos/300/200\" alt=\"\" width=\"300\" height=\"200\"><br></em></span></p>\r\n<p style=\"text-align: center;\">&nbsp;</p>\r\n<p>&nbsp;</p>\r\n<h1 style=\"text-align: center;\"><span style=\"font-size: 24pt;\"><em>Ut ultricies<br></em></span></h1>\r\n<p style=\"text-align: center;\"><span style=\"font-size: 14pt;\">Ut ultricies purus nec nunc feugiat, id sollicitudin lorem tempor. Aenean tempor ligula sed arcu feugiat, id volutpat elit bibendum. Donec mollis erat quis erat ullamcorper, ac gravida odio pellentesque. Nulla facilisi. Curabitur sed mauris ac leo dictum scelerisque ac ut magna. Suspendisse potenti. Integer ut nisl eget arcu pretium fermentum. Pellentesque id nulla justo. Cras nec urna a nisi viverra fermentum vel sit amet elit. Ut vehicula quam vitae nulla dictum, a suscipit est dignissim. Vestibulum tempor nisl sed mauris lacinia, eget facilisis orci elementum.<br><br><img src=\"https://picsum.photos/400/300\" alt=\"\" width=\"400\" height=\"300\"><br></span></p>\r\n<p>&nbsp;</p>\r\n<p style=\"text-align: center;\"><span style=\"font-size: 24pt;\"><em><strong>Phasellus bibendum<br></strong></em></span><span style=\"font-size: 14pt;\">Phasellus bibendum, felis ac dictum varius, lacus lorem ultrices lacus, eget egestas metus justo non justo. Mauris nec est vitae orci faucibus laoreet. Morbi sollicitudin arcu sed ex condimentum, vel facilisis est pellentesque. Nulla facilisi. Vestibulum ut mauris vitae odio suscipit congue sed vel orci. Sed nec felis ut justo vehicula malesuada. Etiam auctor magna nec vestibulum maximus. Suspendisse potenti. Duis efficitur tortor nec ligula aliquam, sit amet maximus dui efficitur. Proin in nunc id magna scelerisque iaculis.<br><br><img src=\"https://picsum.photos/800/400\" alt=\"\" width=\"800\" height=\"400\"><br></span></p>";

        $noticeMessage = CompanyNotice::first();
        $noticeTemplate = "<h1 style=\"text-align: center;\"><span style=\"font-size: 36pt;\"><em>Template Company Notice</em></span></h1>\r\n<h2 style=\"text-align: center;\"><span style=\"font-size: 24pt;\"><em>Lorem ipsum</em></span></h2>\r\n<p style=\"text-align: center;\"><span style=\"font-size: 24pt;\"><span style=\"font-size: 14pt;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus laoreet dolor eu sapien malesuada, a lacinia justo convallis. Fusce sit amet sapien ac justo cursus malesuada sed et odio. Mauris sit amet mi id turpis sodales facilisis. Integer ut efficitur sapien, et dignissim nulla. Aliquam erat volutpat. Sed vestibulum orci non nisi feugiat, id auctor erat interdum. Cras fringilla est at lacus gravida scelerisque. Sed vehicula neque sit amet lacus sagittis faucibus. Nunc iaculis eros vitae sem vulputate, ut dictum nunc efficitur. Integer bibendum urna nec turpis convallis, a fermentum orci malesuada.</span><em><br><img src=\"https://picsum.photos/300/200\" alt=\"\" width=\"300\" height=\"200\">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <img src=\"https://picsum.photos/400/300\" alt=\"\" width=\"400\" height=\"300\">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;<img src=\"https://picsum.photos/300/200\" alt=\"\" width=\"300\" height=\"200\"><br></em></span></p>\r\n<p style=\"text-align: center;\">&nbsp;</p>\r\n<p>&nbsp;</p>\r\n<h1 style=\"text-align: center;\"><span style=\"font-size: 24pt;\"><em>Ut ultricies<br></em></span></h1>\r\n<p style=\"text-align: center;\"><span style=\"font-size: 14pt;\">Ut ultricies purus nec nunc feugiat, id sollicitudin lorem tempor. Aenean tempor ligula sed arcu feugiat, id volutpat elit bibendum. Donec mollis erat quis erat ullamcorper, ac gravida odio pellentesque. Nulla facilisi. Curabitur sed mauris ac leo dictum scelerisque ac ut magna. Suspendisse potenti. Integer ut nisl eget arcu pretium fermentum. Pellentesque id nulla justo. Cras nec urna a nisi viverra fermentum vel sit amet elit. Ut vehicula quam vitae nulla dictum, a suscipit est dignissim. Vestibulum tempor nisl sed mauris lacinia, eget facilisis orci elementum.<br><br><img src=\"https://picsum.photos/400/300\" alt=\"\" width=\"400\" height=\"300\"><br></span></p>\r\n<p>&nbsp;</p>\r\n<p style=\"text-align: center;\"><span style=\"font-size: 24pt;\"><em><strong>Phasellus bibendum<br></strong></em></span><span style=\"font-size: 14pt;\">Phasellus bibendum, felis ac dictum varius, lacus lorem ultrices lacus, eget egestas metus justo non justo. Mauris nec est vitae orci faucibus laoreet. Morbi sollicitudin arcu sed ex condimentum, vel facilisis est pellentesque. Nulla facilisi. Vestibulum ut mauris vitae odio suscipit congue sed vel orci. Sed nec felis ut justo vehicula malesuada. Etiam auctor magna nec vestibulum maximus. Suspendisse potenti. Duis efficitur tortor nec ligula aliquam, sit amet maximus dui efficitur. Proin in nunc id magna scelerisque iaculis.<br><br><img src=\"https://picsum.photos/800/400\" alt=\"\" width=\"800\" height=\"400\"><br></span></p>";

        return view('focus.documentBoard.central', compact('welcomeMessage', 'welcomeTemplate', 'noticeMessage', 'noticeTemplate'));
    }

    public function createWelcome()
    {
        if (!access()->allow('create-welcome-message')) return redirect()->back();

        $welcomeMessage = WelcomeMessage::first();
        $template = "<h1 style=\"text-align: center;\"><span style=\"font-size: 36pt;\"><em>Template Welcome Message</em></span></h1>\r\n<h2 style=\"text-align: center;\"><span style=\"font-size: 24pt;\"><em>Lorem ipsum</em></span></h2>\r\n<p style=\"text-align: center;\"><span style=\"font-size: 24pt;\"><span style=\"font-size: 14pt;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus laoreet dolor eu sapien malesuada, a lacinia justo convallis. Fusce sit amet sapien ac justo cursus malesuada sed et odio. Mauris sit amet mi id turpis sodales facilisis. Integer ut efficitur sapien, et dignissim nulla. Aliquam erat volutpat. Sed vestibulum orci non nisi feugiat, id auctor erat interdum. Cras fringilla est at lacus gravida scelerisque. Sed vehicula neque sit amet lacus sagittis faucibus. Nunc iaculis eros vitae sem vulputate, ut dictum nunc efficitur. Integer bibendum urna nec turpis convallis, a fermentum orci malesuada.</span><em><br><img src=\"https://picsum.photos/300/200\" alt=\"\" width=\"300\" height=\"200\">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <img src=\"https://picsum.photos/400/300\" alt=\"\" width=\"400\" height=\"300\">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;<img src=\"https://picsum.photos/300/200\" alt=\"\" width=\"300\" height=\"200\"><br></em></span></p>\r\n<p style=\"text-align: center;\">&nbsp;</p>\r\n<p>&nbsp;</p>\r\n<h1 style=\"text-align: center;\"><span style=\"font-size: 24pt;\"><em>Ut ultricies<br></em></span></h1>\r\n<p style=\"text-align: center;\"><span style=\"font-size: 14pt;\">Ut ultricies purus nec nunc feugiat, id sollicitudin lorem tempor. Aenean tempor ligula sed arcu feugiat, id volutpat elit bibendum. Donec mollis erat quis erat ullamcorper, ac gravida odio pellentesque. Nulla facilisi. Curabitur sed mauris ac leo dictum scelerisque ac ut magna. Suspendisse potenti. Integer ut nisl eget arcu pretium fermentum. Pellentesque id nulla justo. Cras nec urna a nisi viverra fermentum vel sit amet elit. Ut vehicula quam vitae nulla dictum, a suscipit est dignissim. Vestibulum tempor nisl sed mauris lacinia, eget facilisis orci elementum.<br><br><img src=\"https://picsum.photos/400/300\" alt=\"\" width=\"400\" height=\"300\"><br></span></p>\r\n<p>&nbsp;</p>\r\n<p style=\"text-align: center;\"><span style=\"font-size: 24pt;\"><em><strong>Phasellus bibendum<br></strong></em></span><span style=\"font-size: 14pt;\">Phasellus bibendum, felis ac dictum varius, lacus lorem ultrices lacus, eget egestas metus justo non justo. Mauris nec est vitae orci faucibus laoreet. Morbi sollicitudin arcu sed ex condimentum, vel facilisis est pellentesque. Nulla facilisi. Vestibulum ut mauris vitae odio suscipit congue sed vel orci. Sed nec felis ut justo vehicula malesuada. Etiam auctor magna nec vestibulum maximus. Suspendisse potenti. Duis efficitur tortor nec ligula aliquam, sit amet maximus dui efficitur. Proin in nunc id magna scelerisque iaculis.<br><br><img src=\"https://picsum.photos/800/400\" alt=\"\" width=\"800\" height=\"400\"><br></span></p>";

        return view('focus.documentBoard.create-welcome', compact('welcomeMessage', 'template'));
    }

    public function storeWelcome(Request $request)
    {
        if (!access()->allow('create-welcome-message')) return redirect()->back();

        $validated = $request->validate([
            'message' => ['required', 'string']
        ]);

        try {
            DB::beginTransaction();

            $welcomeMessage = WelcomeMessage::first() ?? new WelcomeMessage();
            $welcomeMessage->message = $validated['message'];

            if (!$welcomeMessage) {
                $welcomeMessage = new WelcomeMessage();
                $welcomeMessage->message = $validated['message'];
                $welcomeMessage->save();
            }

            $tempImages = WelcomeMessageTempImage::all();

            foreach ($tempImages as $image) {
                $tempPath = 'uploads/temp/welcome-message-photos/' . Auth::user()->ins . '/' . $image->filename;
                $permPath = 'uploads/welcome-message-photos/' . Auth::user()->ins . '/' . $image->filename;

                // Check if the temporary file exists
                if (Storage::disk('public')->exists($tempPath)) {
                    // Create the directories if they do not exist
                    $destinationPath = 'uploads/welcome-message-photos/' . Auth::user()->ins;
                    if (!Storage::disk('local')->exists($destinationPath)) {
                        Storage::disk('local')->makeDirectory($destinationPath);
                    }

                    // Move the file to the permanent location within storage/app/uploads
                    Storage::disk('public')->move($tempPath, $permPath);

                    // Update the database record with the new location
                    $welcomeMessageImage = new WelcomeMessageImage();
                    $welcomeMessageImage->welcome_message_id = $welcomeMessage->id;
                    $welcomeMessageImage->filename = $image->filename;
                    $welcomeMessageImage->location = $permPath; // Store the relative path
                    $welcomeMessageImage->save();

                    // Optionally delete the old temporary record if not needed
                    WelcomeMessageTempImage::where('id', $image->id)->delete();
                }
            }

            $welcomeMessage->message = str_replace(
                'uploads/temp/welcome-message-photos/' . Auth::user()->ins . '/',
                'uploads/welcome-message-photos/' . Auth::user()->ins . '/',
                $welcomeMessage->message
            );

            $welcomeMessage->save();


            //Deleting removed Images
            $images = $welcomeMessage->images ? $welcomeMessage->images->pluck('filename') : [];
            $excluded = [];

            foreach ($images as $img) {
                if (!strpos($welcomeMessage->message, $img)) array_push($excluded, $img);
            }

            $trashedImages = WelcomeMessageImage::whereIn('filename', $excluded)->get();

            foreach ($trashedImages as $image){

                $permPath = $image->location;
                if (Storage::disk('public')->exists($permPath)) Storage::disk('public')->delete($permPath);
            }

            WelcomeMessageImage::whereIn('filename', $excluded)->delete();

            DB::commit();

        } catch (Exception $exception) {
            DB::rollBack();

            return response()->json([
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ], 500);
        }

        return redirect()->route('biller.company-notice-board.central')->with('success', 'Welcome Message Updated successfully.');
    }

    public function showWelcomeImage($filename)
    {
        // Define the path to the file
        $path = storage_path('app/uploads/welcome-message-photos/' . $filename);

        // Check if the file exists
        if (!file_exists($path)) {
            abort(404);
        }

        // Return the file as a response
        return response()->file($path, [
            'Content-Type' => 'image/jpeg', // Change this if you're serving different types of images
        ]);
    }



    public function createNotice()
    {
        if (!access()->allow('create-company-notice')) return redirect()->back();

        $companyNotice = CompanyNotice::first();
        $template = "<h1 style=\"text-align: center;\"><span style=\"font-size: 36pt;\"><em>Template Company Notice</em></span></h1>\r\n<h2 style=\"text-align: center;\"><span style=\"font-size: 24pt;\"><em>Lorem ipsum</em></span></h2>\r\n<p style=\"text-align: center;\"><span style=\"font-size: 24pt;\"><span style=\"font-size: 14pt;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus laoreet dolor eu sapien malesuada, a lacinia justo convallis. Fusce sit amet sapien ac justo cursus malesuada sed et odio. Mauris sit amet mi id turpis sodales facilisis. Integer ut efficitur sapien, et dignissim nulla. Aliquam erat volutpat. Sed vestibulum orci non nisi feugiat, id auctor erat interdum. Cras fringilla est at lacus gravida scelerisque. Sed vehicula neque sit amet lacus sagittis faucibus. Nunc iaculis eros vitae sem vulputate, ut dictum nunc efficitur. Integer bibendum urna nec turpis convallis, a fermentum orci malesuada.</span><em><br><img src=\"https://picsum.photos/300/200\" alt=\"\" width=\"300\" height=\"200\">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <img src=\"https://picsum.photos/400/300\" alt=\"\" width=\"400\" height=\"300\">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;<img src=\"https://picsum.photos/300/200\" alt=\"\" width=\"300\" height=\"200\"><br></em></span></p>\r\n<p style=\"text-align: center;\">&nbsp;</p>\r\n<p>&nbsp;</p>\r\n<h1 style=\"text-align: center;\"><span style=\"font-size: 24pt;\"><em>Ut ultricies<br></em></span></h1>\r\n<p style=\"text-align: center;\"><span style=\"font-size: 14pt;\">Ut ultricies purus nec nunc feugiat, id sollicitudin lorem tempor. Aenean tempor ligula sed arcu feugiat, id volutpat elit bibendum. Donec mollis erat quis erat ullamcorper, ac gravida odio pellentesque. Nulla facilisi. Curabitur sed mauris ac leo dictum scelerisque ac ut magna. Suspendisse potenti. Integer ut nisl eget arcu pretium fermentum. Pellentesque id nulla justo. Cras nec urna a nisi viverra fermentum vel sit amet elit. Ut vehicula quam vitae nulla dictum, a suscipit est dignissim. Vestibulum tempor nisl sed mauris lacinia, eget facilisis orci elementum.<br><br><img src=\"https://picsum.photos/400/300\" alt=\"\" width=\"400\" height=\"300\"><br></span></p>\r\n<p>&nbsp;</p>\r\n<p style=\"text-align: center;\"><span style=\"font-size: 24pt;\"><em><strong>Phasellus bibendum<br></strong></em></span><span style=\"font-size: 14pt;\">Phasellus bibendum, felis ac dictum varius, lacus lorem ultrices lacus, eget egestas metus justo non justo. Mauris nec est vitae orci faucibus laoreet. Morbi sollicitudin arcu sed ex condimentum, vel facilisis est pellentesque. Nulla facilisi. Vestibulum ut mauris vitae odio suscipit congue sed vel orci. Sed nec felis ut justo vehicula malesuada. Etiam auctor magna nec vestibulum maximus. Suspendisse potenti. Duis efficitur tortor nec ligula aliquam, sit amet maximus dui efficitur. Proin in nunc id magna scelerisque iaculis.<br><br><img src=\"https://picsum.photos/800/400\" alt=\"\" width=\"800\" height=\"400\"><br></span></p>";

        return view('focus.documentBoard.createNotice', compact('companyNotice', 'template'));
    }

    public function storeNotice(Request $request)
    {
        if (!access()->allow('create-company-notice')) return redirect()->back();

        $validated = $request->validate([
            'message' => ['required', 'string']
        ]);

        try {
            DB::beginTransaction();

            $companyNotice = CompanyNotice::first() ?? new CompanyNotice();
            $companyNotice->message = $validated['message'];
            $companyNotice->save();

            if (!$companyNotice) {
                $companyNotice = new CompanyNotice();
                $companyNotice->message = $validated['message'];
                $companyNotice->save();
            }

            $tempImages = CompanyNoticeTempImage::all();

            foreach ($tempImages as $image) {
                $tempPath = 'uploads/temp/notice-photos/' . Auth::user()->ins . '/' . $image->filename;
                $permPath = 'uploads/notice-photos/' . Auth::user()->ins . '/' . $image->filename;

                // Check if the temporary file exists
                if (Storage::disk('public')->exists($tempPath)) {
                    // Create the directories if they do not exist
                    $destinationPath = 'uploads/notice-photos/' . Auth::user()->ins;
                    if (!Storage::disk('local')->exists($destinationPath)) {
                        Storage::disk('local')->makeDirectory($destinationPath);
                    }

                    // Move the file to the permanent location within storage/app/uploads
                    Storage::disk('public')->move($tempPath, $permPath);

                    // Update the database record with the new location
                    $companyNoticeImage = new CompanyNoticeImage();
                    $companyNoticeImage->company_notice_id = $companyNotice->id;
                    $companyNoticeImage->filename = $image->filename;
                    $companyNoticeImage->location = $permPath; // Store the relative path
                    $companyNoticeImage->save();

                    // Optionally delete the old temporary record if not needed
                    CompanyNoticeTempImage::where('id', $image->id)->delete();
                }
            }

            $companyNotice->message = str_replace(
                'uploads/temp/notice-photos/' . Auth::user()->ins . '/',
                'uploads/notice-photos/' . Auth::user()->ins . '/',
                $companyNotice->message
            );

            $companyNotice->save();


            //Deleting removed Images
            $images = $companyNotice->images ? $companyNotice->images->pluck('filename') : [];
            $excluded = [];

            foreach ($images as $img) {
                if (!strpos($companyNotice->message, $img)) array_push($excluded, $img);
            }

            $trashedImages = CompanyNoticeImage::whereIn('filename', $excluded)->get();

            foreach ($trashedImages as $image){

                $permPath = $image->location;
                if (Storage::disk('public')->exists($permPath)) Storage::disk('public')->delete($permPath);
            }

            CompanyNoticeImage::whereIn('filename', $excluded)->delete();

            DB::commit();

        } catch (Exception $exception) {
            DB::rollBack();

            return response()->json([
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ], 500);
        }

        return redirect()->route('biller.company-notice-board.central')->with('success', 'Welcome Message Updated successfully.');
    }


    public function create()
    {
        return view('focus.documentBoard.create');
    }

    public function store(Request $request)
    {

        if (!access()->allow('create-company-notice-board')) return redirect()->back();

        // Validate the request data
        $request->validate([
            'caption' => 'required|string|max:255',
            'file' => 'required|max:10000', // Adjust file types and size as needed
        ]);

        // Get the uploaded file
        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();

        // Generate a unique file name using uniqid
        $uniqueFileName = uniqid(pathinfo(str_replace(' ' , '', $originalName), PATHINFO_FILENAME) . '-', true) . '.' . $file->getClientOriginalExtension();

        // Store the file in the public storage (you can choose a different disk if needed)
        $filePath = $file->storeAs('documents', $uniqueFileName, 'public');

        // Create a new document record in the database
        DocumentBoard::create([
            'caption' => $request->input('caption'),
            'file_path' => $filePath,
        ]);

        // Redirect back with a success message
        return redirect()->route('biller.company-notice-board.central')->with('success', 'Document uploaded successfully.');
    }
    public function view(DocumentBoard $documentBoard)
    {

        if (!access()->allow('view-company-notice-board')) return redirect()->back();

        $url = Storage::url($documentBoard->file_path);
        return redirect($url);
    }

    public function download(DocumentBoard $documentBoard)
    {
        if (!access()->allow('view-company-notice-board')) return redirect()->back();

        // Specify the public disk to match where the file is stored
        return Storage::disk('public')->download($documentBoard->file_path);
    }

    public function destroy(DocumentBoard $documentBoard)
    {
        if (!access()->allow('delete-company-notice-board')) return redirect()->back();

        // Delete the file from storage
        Storage::delete($documentBoard->file_path);

        // Delete the document record from the database
        $documentBoard->delete();

        // Redirect back with a success message
        return redirect()->route('biller.company-notice-board.index')->with('success', 'Document deleted successfully.');
    }
}