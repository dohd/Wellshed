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
namespace App\Http\Controllers\Focus\message_template;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Focus\whatsapp\WhatsappController;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\message_template\CreateResponse;
use App\Http\Responses\Focus\message_template\EditResponse;
use App\Models\Company\Company;
use App\Models\message_template\MessageTemplate;
use App\Models\tenant\Tenant;
use App\Repositories\Focus\message_template\MessageTemplateRepository;
use GuzzleHttp\Client;

/**
 * MessageTemplatesController
 */
class MessageTemplatesController extends Controller
{
    /**
     * variable to store the repository object
     * @var message_templateRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param message_templateRepository $repository ;
     */
    public function __construct(MessageTemplateRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\message_template\Managemessage_templateRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index()
    {
        return new ViewResponse('focus.message_templates.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Createmessage_templateRequestNamespace $request
     * @return \App\Http\Responses\Focus\message_template\CreateResponse
     */
    public function create()
    {
        $business = auth()->user()->business;
        return view('focus.message_templates.create',compact('business'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Storemessage_templateRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    {
        //Input received from the request
        $input = $request->except(['_token', 'ins']);
        $input['ins'] = auth()->user()->ins;
        try {
             //Create the model using repository create method
            $this->repository->create($input);
        } catch (\Throwable $th) {
            //throw $th;
            return errorHandler('Error Creating Message Template', $th);
        }
       
        //return with successfull message
        return new RedirectResponse(route('biller.message_templates.index'), ['flash_success' => 'Message Template Created Successfully!!']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\message_template\message_template $message_template
     * @param Editmessage_templateRequestNamespace $request
     * @return \App\Http\Responses\Focus\message_template\EditResponse
     */
    public function edit(MessageTemplate $message_template)
    {
        $business = auth()->user()->business;
        return view('focus.message_templates.edit', compact('message_template','business'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Updatemessage_templateRequestNamespace $request
     * @param App\Models\message_template\message_template $message_template
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(Request $request, MessageTemplate $message_template)
    {
        //Input received from the request
        $input = $request->except(['_token', 'ins']);
        try {
            //Update the model using repository update method
            $this->repository->update($message_template, $input);
        } catch (\Throwable $th) {
            //throw $th;
            return errorHandler('Error Updating Message Template', $th);
        }
        //return with successfull message
        return new RedirectResponse(route('biller.message_templates.index'), ['flash_success' => 'Message Template Updated Successfully!!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Deletemessage_templateRequestNamespace $request
     * @param App\Models\message_template\message_template $message_template
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(MessageTemplate $message_template)
    {
        try {
            //Calling the delete method on repository
            $this->repository->delete($message_template);
        } catch (\Throwable $th) {
            //throw $th;
            return errorHandler('Error Deleting Message Template', $th);
        }
        //returning with successfull message
        return new RedirectResponse(route('biller.message_templates.index'), ['flash_success' => 'Message Template Deleted Successfully!!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Deletemessage_templateRequestNamespace $request
     * @param App\Models\message_template\message_template $message_template
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(MessageTemplate $message_template, Request $request)
    {

        //returning with successfull message
        return new ViewResponse('focus.message_templates.view', compact('message_template'));
    }

    function extractTemplateVariables(array $templateJson, array $values = [], $phoneNo = null)
    {
        $variableTypes = [];
        $variables     = [];

        foreach ($templateJson["components"] as $component) {
            if ($component["type"] === "BODY" && isset($component["text"])) {
                preg_match_all('/\{\{(\d+|\w+)\}\}/', $component["text"], $matches);

                foreach ($matches[0] as $index => $match) {
                    $variableTypes[] = "body";
                    $variables[]     = $values[$index] ?? null;
                }
            }

            if ($component["type"] === "HEADER" && isset($component["text"])) {
                preg_match_all('/\{\{(\d+|\w+)\}\}/', $component["text"], $matches);

                foreach ($matches[0] as $index => $match) {
                    $variableTypes[] = "header";
                    $variables[]     = $values[$index] ?? null;
                }
            }

            if ($component["type"] === "BUTTONS" && isset($component["buttons"])) {
                foreach ($component["buttons"] as $btnIndex => $button) {
                    if (!empty($button["text"])) {
                        preg_match_all('/\{\{(\d+|\w+)\}\}/', $button["text"], $matches);

                        foreach ($matches[0] as $index => $match) {
                            $variableTypes[] = "button_text";
                            $variables[]     = $values[$index] ?? null;
                        }
                    }

                    if (!empty($button["url"])) {
                        preg_match_all('/\{\{(\d+|\w+)\}\}/', $button["url"], $matches);

                        foreach ($matches[0] as $index => $match) {
                            $variableTypes[] = "button_url";
                            $variables[]     = $values[$index] ?? null;
                        }
                    }
                }
            }
        }

        return [
            "template_name" => $templateJson["name"] ?? null,
            "template_id"   => $templateJson["id"] ?? null,
            "phone_no"      => $phoneNo,
            "variable_type" => $variableTypes,
            "variable"      => $variables,
            // 'button_type'   => ['url'],
        ];
    }


    public function getWhatsappTemplates($business)
    {
        $client = new Client();

        $url = "{$business->graph_api_url}/{$business->whatsapp_business_account_id}/message_templates";

        $response = $client->request('GET', $url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $business->whatsapp_access_token,
                'Accept'        => 'application/json',
            ],
            'query' => [
                'limit' => 50, // optional
            ],
        ]);

        $body = json_decode($response->getBody()->getContents(), true);

        return $body['data'] ?? [];
    }

    public function getTemplateVariables(Request $request)
    {
        $templateId = $request->input('template_id');
        $phone = $request->input('phone');
        $values = $request->input('variable',[]);
        $business = Company::find(2) ?? optional(auth()->user()->business);// adjust this to your app

        $templates = $this->getWhatsappTemplates($business);

        $selected = collect($templates)->firstWhere('id', $templateId);

        if (!$selected) {
            return response()->json(['error' => 'Template not found'], 404);
        }
        $result = $this->extractTemplateVariables($selected,$values,$phone);

        $whatsapp = new WhatsappController;
        $what_request_data = new Request($result);
        $whatsapp->messages_store($what_request_data);
        return response()->json([
            'template'  => $selected,
            'variables' => $result
        ]);
    }


}
