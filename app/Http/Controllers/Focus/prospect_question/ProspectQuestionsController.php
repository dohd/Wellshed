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
namespace App\Http\Controllers\Focus\prospect_question;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\prospect_question\CreateResponse;
use App\Http\Responses\Focus\prospect_question\EditResponse;
use App\Models\prospect_question\ProspectQuestion;
use App\Repositories\Focus\prospect_question\ProspectQuestionRepository;


/**
 * ProspectQuestionsController
 */
class ProspectQuestionsController extends Controller
{
    /**
     * variable to store the repository object
     * @var ProspectQuestionRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param ProspectQuestionRepository $repository ;
     */
    public function __construct(ProspectQuestionRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\prospect_question\Manageprospect_questionRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index()
    {
        return new ViewResponse('focus.prospect_questions.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * 
     * @return \App\Http\Responses\Focus\prospect_question\CreateResponse
     */
    public function create()
    {
        return new CreateResponse('focus.prospect_questions.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * 
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    {
        $data = $request->only(['title','description']);
        $data_items = $request->only(['question','type']);
        $data_items = modify_array($data_items);
        try {
            //Create the model using repository create method
            $this->repository->create(compact('data','data_items'));
        } catch (\Throwable $th) {
            return errorHandler('Error creating Prospect Question '+$th->getMessage(), $th);
        }
        //return with successfull message
        return new RedirectResponse(route('biller.prospect_questions.index'), ['flash_success' => 'Prospect Questions Created Successfully!!']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * 
     * @return \App\Http\Responses\Focus\prospect_question\EditResponse
     */
    public function edit(ProspectQuestion $prospect_question)
    {
        return new EditResponse($prospect_question);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Updateprospect_questionRequestNamespace $request
     * @param App\Models\prospect_question\prospect_question $prospect_question
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(Request $request, ProspectQuestion $prospect_question)
    {
        //Input received from the request
        $data = $request->only(['title','description']);
        $data_items = $request->only(['question','type','id']);
        $data_items = modify_array($data_items);
        try {
            //Update the model using repository update method
            $this->repository->update($prospect_question, compact('data','data_items'));
        } catch (\Throwable $th) {
            //throw $th;
            return errorHandler('Error updating Prospect Question '+$th->getMessage(), $th);
        }
        //return with successfull message
        return new RedirectResponse(route('biller.prospect_questions.index'), ['flash_success' => 'Prospect Questions Updated Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     *
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(ProspectQuestion $prospect_question)
    {
        //Calling the delete method on repository
        $this->repository->delete($prospect_question);
        //returning with successfull message
        return new RedirectResponse(route('biller.prospect_questions.index'), ['flash_success' => 'Prospect Questions Deleted Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(ProspectQuestion $prospect_question)
    {

        //returning with successfull message
        return new ViewResponse('focus.prospect_questions.view', compact('prospect_question'));
    }

    public function get_items(Request $request){
        $prospect_question = ProspectQuestion::find($request->prospect_question_id);
        $data = $prospect_question->questions;
        return response()->json($data);
    }

}
