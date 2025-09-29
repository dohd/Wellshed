<?php
/*
 * Rose Business Suite - Accounting, CRM and POS Software
 * Copyright (c) UltimateKode.com. All Rights Reserved
 * ***********************************************************************
 *
 *  Email: support@ultimatekode.com
 *  Website: https://www.ultimatekode.com
 *
 *  ************************************************************************
 *  * This software is furnished under a license and may be used and copied
 *  * only  in  accordance  with  the  terms  of such  license and with the
 *  * inclusion of the above copyright notice.
 *  * If you Purchased from Codecanyon, Please read the full License from
 *  * here- http://codecanyon.net/licenses/standard/
 * ***********************************************************************
 */
namespace App\Http\Controllers\Focus\general;

use App\Models\Company\ConfigMeta;
use App\Models\items\MetaEntry;
use App\Models\project\ProjectLog;
use App\Models\project\ProjectMeta;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use File;
use Illuminate\Support\Facades\Storage;
use Log;

class FileController extends Controller
{
    /**
     *customer_picture_path .
     *
     * @var string
     */
    protected $file_item_path;


    /**
     * Storage Class Object.
     *
     * @var \Illuminate\Support\Facades\Storage
     */
    protected $storage;

    protected $file_path;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->file_item_path = 'files' . DIRECTORY_SEPARATOR;
        $this->storage = Storage::disk('public');

        $this->file_path = $this->absolute_path(storage_path(), ['app', 'public', 'files']);
    }

    public function absolute_path($path='', $dirs=[])
    {
        $path .= DIRECTORY_SEPARATOR;
        foreach ($dirs as $dir) {
            $path .= $dir . DIRECTORY_SEPARATOR;
        }
        return $path;
    }


    public function bill_attachment(Request $request)
    {
        $action = $request->only('op', 'id');
        if (@$action['op'] == 'delete') {
            $file_value = MetaEntry::find($action['id']);

            if (delete_file($this->file_item_path . $file_value['value'])) $file_value->delete();

            return response()->json(['success' => 'Deleted']);
        } else {
            $upload_setting = ConfigMeta::where('feature_id', '=', 9)->where('ins', '=', auth()->user()->ins)->first(array('feature_value', 'value1'));
            if ($upload_setting['feature_value']) {
                $itemName = $request->only('files');
                $item_rel = $request->only('id', 'bill');
                $request->validate([
                    'files' => 'required|mimes:' . $upload_setting['value1'],
                ]);
                $path = $this->file_item_path;
                $bill_type = array();
                $up = array();
                foreach ($itemName as $item) {
                    $name = $item->getClientOriginalName();
                    $file_name = strlen($name) > 20 ? substr($name, 0, 20) . '.' . $item->getClientOriginalExtension() : $name;
                    $file_name = time() . $file_name;
                    $this->storage->put($path . $file_name, file_get_contents($item->getRealPath()));
                    $bill_type = array('rel_type' => $item_rel['bill'], 'rel_id' => $item_rel['id'], 'value' => $file_name, 'ins' => auth()->user()->ins);
                    $upload = MetaEntry::create($bill_type);
                    $up[] = array('id' => $upload->id, 'name' => $file_name);
                }
                return response()->json($up);
            }
        }
    }

    /**
     * Project attachment file handler
     */
    public function project_attachment(Request $request)
    {
        // dd($request->all());
        $operation = $request->op;
        $meta_id = $request->meta_id;
        $project_id = $request->project_id;
        $caption = $request->caption;
        $files = $request->files;

        DB::beginTransaction();
        
        try {
            if ($operation == 'delete') {
                $project_meta = ProjectMeta::find($meta_id);
                $file = $this->file_path . $project_meta->value;
                // dd($file);
                // if (file_exists($file)) delete_file($file); 
                if (File::exists($file)) File::delete($file); 
                $project_meta->delete();

                DB::commit();
                return response()->json(['status' => 'Success', 'message' => 'deleted']);
            }

            $uploads = [];
            $config_meta = ConfigMeta::where('feature_id', 9)->first(['feature_value', 'value1']);
            if (@$config_meta->feature_value) {
                $request->validate(['files' => 'required|mimes:jpeg,png,pdf' . $config_meta->value1]);
                foreach ($files as $file) {
                    $origin_filename = $file->getClientOriginalName();
                    $file_name = time() . '_' . substr($origin_filename, 0, 20);
                    // File::put($this->file_path . $file_name, file_get_contents($file->getRealPath()));
                    $image_name = $this->uploadPicture($file, $this->file_item_path);

                    $project_meta = ProjectMeta::create(['project_id' => $project_id, 'meta_key' => 1, 'value' => $image_name, 'caption' => $caption]);
                    $uploads[] = ['id' => $project_meta->id, 'name' => $image_name,'caption' => $caption];
                }
            }
            $project_meta = ProjectMeta::latest()->first();
            ProjectLog::create(['project_id' => $project_meta->project_id, 'value' => '[' . trans('general.files') . '] ' . $project_meta->value]);

            DB::commit();
            return response()->json($uploads);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
        }
    }

    public function uploadPicture($file, $path)
    {
        $image_name = time() . $file->getClientOriginalName();
        $this->storage->put($path . $image_name, file_get_contents($file->getRealPath()));
        return $image_name;
    }
}
