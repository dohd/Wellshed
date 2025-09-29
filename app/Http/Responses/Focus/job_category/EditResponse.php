<?php

namespace App\Http\Responses\Focus\job_category;

use Illuminate\Contracts\Support\Responsable;

class EditResponse implements Responsable
{
    /**
     * @var App\Models\job_category\job_category
     */
    protected $job_categories;

    /**
     * @param App\Models\job_category\job_category $job_categories
     */
    public function __construct($job_categories)
    {
        $this->job_categories = $job_categories;
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
        return view('focus.job_categories.edit')->with([
            'job_categories' => $this->job_categories
        ]);
    }
}