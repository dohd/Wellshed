<?php

namespace App\Http\Controllers\Focus\jobGrade;

use App\Http\Controllers\Controller;
use App\Models\jobGrade\JobGrade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JobGradeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        if (!access()->allow('manage-job-grades')) return response("", 403);

        $jobGrades = JobGrade::first();

        return view('focus.jobGrade.show', compact('jobGrades'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        if (!access()->allow('create-job-grades')) return response("", 403);

        if (!JobGrade::first()) {

            $newJobGrades = new JobGrade();
            $newJobGrades->save();
        }

        $jobGrades = JobGrade::first();

        return view('focus.jobGrade.create', compact('jobGrades'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        if (!access()->allow('create-job-grades')) return response("", 403);

        try{

            DB::beginTransaction();

            $jobGrades = JobGrade::first();
            $jobGrades->fill($request->toArray());
            $jobGrades->save();

            DB::commit();
        }
        catch (\Exception $exception){

            DB::rollBack();
            redirect()->back()->with('error', 'Something Went Wrong!');
        }

        return redirect()->route('biller.job-grades.index')->with('success', 'Job Grades updated successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }


    public function getJobGrade($gradeString){

        $grades = $this->getJobGradesArray();

        if (strlen($gradeString) > 3 || empty($gradeString)) return '';

        return $grades[$gradeString];
    }


    public function getJobGradesArray(): array{

        $jobGrade = JobGrade::first();

        return
            [
                "1A" => "Grade 1A | Upper Salary Limit: " . (@$jobGrade->{"1a_upper"} ?? 'Not Set') . ", Lower Salary Limit: " . @$jobGrade->{"1a_lower"} ?? 'Not Set',
                "1B" => "Grade 1B | Upper Salary Limit: " . (@$jobGrade->{"1b_upper"} ?? 'Not Set') . ", Lower Salary Limit: " . @$jobGrade->{"1b_lower"} ?? 'Not Set',
                "2A" => "Grade 2A | Upper Salary Limit: " . (@$jobGrade->{"2a_upper"} ?? 'Not Set') . ", Lower Salary Limit: " . @$jobGrade->{"2a_lower"} ?? 'Not Set',
                "2B" => "Grade 2B | Upper Salary Limit: " . (@$jobGrade->{"2b_upper"} ?? 'Not Set') . ", Lower Salary Limit: " . @$jobGrade->{"2b_lower"} ?? 'Not Set',
                "3A" => "Grade 3A | Upper Salary Limit: " . (@$jobGrade->{"3a_upper"} ?? 'Not Set') . ", Lower Salary Limit: " . @$jobGrade->{"3a_lower"} ?? 'Not Set',
                "3B" => "Grade 3B | Upper Salary Limit: " . (@$jobGrade->{"3b_upper"} ?? 'Not Set') . ", Lower Salary Limit: " . @$jobGrade->{"3b_lower"} ?? 'Not Set',
                "4A" => "Grade 4A | Upper Salary Limit: " . (@$jobGrade->{"4a_upper"} ?? 'Not Set') . ", Lower Salary Limit: " . @$jobGrade->{"4a_lower"} ?? 'Not Set',
                "4B" => "Grade 4B | Upper Salary Limit: " . (@$jobGrade->{"4b_upper"} ?? 'Not Set') . ", Lower Salary Limit: " . @$jobGrade->{"4b_lower"} ?? 'Not Set',
                "5A" => "Grade 5A | Upper Salary Limit: " . (@$jobGrade->{"5a_upper"} ?? 'Not Set') . ", Lower Salary Limit: " . @$jobGrade->{"5a_lower"} ?? 'Not Set',
                "5B" => "Grade 5B | Upper Salary Limit: " . (@$jobGrade->{"5b_upper"} ?? 'Not Set') . ", Lower Salary Limit: " . @$jobGrade->{"5b_lower"} ?? 'Not Set',
                "6A" => "Grade 6A | Upper Salary Limit: " . (@$jobGrade->{"6a_upper"} ?? 'Not Set') . ", Lower Salary Limit: " . @$jobGrade->{"6a_lower"} ?? 'Not Set',
                "6B" => "Grade 6B | Upper Salary Limit: " . (@$jobGrade->{"6b_upper"} ?? 'Not Set') . ", Lower Salary Limit: " . @$jobGrade->{"6b_lower"} ?? 'Not Set',
                "7A" => "Grade 7A | Upper Salary Limit: " . (@$jobGrade->{"7a_upper"} ?? 'Not Set') . ", Lower Salary Limit: " . @$jobGrade->{"7a_lower"} ?? 'Not Set',
                "7B" => "Grade 7B | Upper Salary Limit: " . (@$jobGrade->{"7b_upper"} ?? 'Not Set') . ", Lower Salary Limit: " . @$jobGrade->{"7b_lower"} ?? 'Not Set',
                "8A" => "Grade 8A | Upper Salary Limit: " . (@$jobGrade->{"8a_upper"} ?? 'Not Set') . ", Lower Salary Limit: " . @$jobGrade->{"8a_lower"} ?? 'Not Set',
                "8B" => "Grade 8B | Upper Salary Limit: " . (@$jobGrade->{"8b_upper"} ?? 'Not Set') . ", Lower Salary Limit: " . @$jobGrade->{"8b_lower"} ?? 'Not Set',
                "9A" => "Grade 9A | Upper Salary Limit: " . (@$jobGrade->{"9a_upper"} ?? 'Not Set') . ", Lower Salary Limit: " . @$jobGrade->{"9a_lower"} ?? 'Not Set',
                "9B" => "Grade 9B | Upper Salary Limit: " . (@$jobGrade->{"9b_upper"} ?? 'Not Set') . ", Lower Salary Limit: " . @$jobGrade->{"9b_lower"} ?? 'Not Set',
                "10A" => "Grade 10A | Upper Salary Limit: " . (@$jobGrade->{"10a_upper"} ?? 'Not Set') . ", Lower Salary Limit: " . @$jobGrade->{"10a_lower"} ?? 'Not Set',
                "10B" => "Grade 10B | Upper Salary Limit: " . (@$jobGrade->{"10b_upper"} ?? 'Not Set') . ", Lower Salary Limit: " . @$jobGrade->{"10b_lower"} ?? 'Not Set',
            ];
    }


}
