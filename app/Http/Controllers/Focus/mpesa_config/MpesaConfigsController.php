<?php

namespace App\Http\Controllers\Focus\mpesa_config;

use App\Http\Controllers\Controller;
use App\Models\mpesa\MpesaConfig;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class MpesaConfigsController extends Controller
{
    public function index()
    {
        $configs = MpesaConfig::all();
        return view('focus.mpesa_configs.index', compact('configs'));
    }

    public function create()
    {
        return view('focus.mpesa_configs.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'env' => 'required|in:sandbox,production',
            'type' => 'required|in:b2c,c2b_store,c2b_paybill,stk_push',
            'consumer_key' => 'required|string',
            'consumer_secret' => 'required|string',
            'shortcode' => 'required|string',
            'cert_file' => 'nullable|file|mimes:cer,pem|max:2048', // max 2MB
        ]);
        $data = $request->all();

        if ($request->hasFile('cert_file')) {
            $path = $request->file('cert_file')->store('mpesa_certs', 'public');
            $data['cert_path'] = $path;
        }

        MpesaConfig::create($data);
        return redirect()->route('biller.mpesa_configs.index')->with('flash_success', 'Mpesa Config created successfully.');
    }

    public function edit(MpesaConfig $mpesaConfig)
    {
        return view('focus.mpesa_configs.edit', compact('mpesaConfig'));
    }

    public function update(Request $request, MpesaConfig $mpesaConfig)
    {
        $request->validate([
            'env' => 'required|in:sandbox,production',
            'type' => 'required|in:b2c,c2b_store,c2b_paybill,stk_push',
            'consumer_key' => 'required|string',
            'consumer_secret' => 'required|string',
            'shortcode' => 'required|string',
            'cert_file' => 'nullable|file|mimetypes:application/x-x509-ca-cert,text/plain|max:2048',
        ]);

        try {
            $data = $request->only([
                'env',
                'type',
                'consumer_key',
                'consumer_secret',
                'shortcode',
                'head_office_shortcode',
                'initiator_name',
                'initiator_password_enc',
                'result_url',
                'timeout_url',
                'validation_url',
                'confirmation_url',
                'passkey',
                'account_reference',
                'callback_url',
            ]);

           if ($request->hasFile('cert_file')) {
                $filename = pathinfo($request->file('cert_file')->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $request->file('cert_file')->getClientOriginalExtension();

                // Append timestamp to filename, keep extension
                $newFilename = $filename . '_' . time() . '.' . $extension;

                $path = $request->file('cert_file')->storeAs(
                    'mpesa_certs',
                    $newFilename,
                    'public'
                );

                $data['cert_path'] = $path;
            }
            $mpesaConfig->update($data);
        } catch (\Throwable $th) {
            return errorHandler('Error Updating Mpesa Configs', $th);
        }

        return redirect()->route('biller.mpesa_configs.index')
            ->with('flash_success', 'Mpesa Config updated successfully.');
    }


    public function destroy(MpesaConfig $mpesaConfig)
    {
        $mpesaConfig->delete();
        return redirect()->route('biller.mpesa_configs.index')->with('flash_success', 'Charge deleted successfully.');
    }

    public function get(Request $request)
    {
        $core = MpesaConfig::get();
        return DataTables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('env', function ($mpesa_config) {
                return $mpesa_config->env;
            })
            ->addColumn('type', function ($mpesa_config) {
                return $mpesa_config->type;
            })
            ->addColumn('actions', function ($mpesa_config) {
                return $mpesa_config->action_buttons;
            })
            ->make(true);
    }
}
