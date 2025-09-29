<?php

namespace App\Http\Responses\Focus\prospect_question;

use Illuminate\Contracts\Support\Responsable;

class EditResponse implements Responsable
{
    /**
     * @var App\Models\prospect_question\prospect_question
     */
    protected $prospect_questions;

    /**
     * @param App\Models\prospect_question\prospect_question $prospect_questions
     */
    public function __construct($prospect_questions)
    {
        $this->prospect_questions = $prospect_questions;
    }

    /**
     * To Response
     *
     * @param \App\Http\Requests\Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function toResponse($request)
    {
        return view('focus.prospect_questions.edit')->with([
            'prospect_questions' => $this->prospect_questions
        ]);
    }
}