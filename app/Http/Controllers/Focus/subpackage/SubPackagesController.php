<?php

namespace App\Http\Controllers\Focus\subpackage;

use App\Http\Controllers\Controller;
use App\Models\subpackage\SubPackage;
use Exception;
use Illuminate\Http\Request;

class SubPackagesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('focus.subpackages.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('focus.subpackages.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'price' => 'required',
        ]);
        $data = $request->except(['_token']);

        try {
            $data['price'] = numberClean($data['price']);
            $subpackage = SubPackage::create($data);
            
            return redirect(route('biller.subpackages.index'))->with(['flash_success' => 'Package Created Successfully']);
        } catch (Exception $e) {
            return errorHandler('Error Creating Package', $e);
        }
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
    public function edit(SubPackage $subpackage)
    {
        return view('focus.subpackages.edit', compact('subpackage'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SubPackage $subpackage)
    {
        $request->validate([
            'name' => 'required',
            'price' => 'required',
        ]);
        $data = $request->except(['_token', '_method']);

        try {
            $data['price'] = numberClean($data['price']);
            $data['is_disabled'] = $data['is_disabled'] ?? null;
            $subpackage->update($data);
            
            return redirect(route('biller.subpackages.index'))->with(['flash_success' => 'Package Updated Successfully']);
        } catch (Exception $e) {
            return errorHandler('Error Updating Package', $e);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(SubPackage $subpackage)
    {
        try {
            $subpackage->delete();
            
            return redirect(route('biller.subpackages.index'))->with(['flash_success' => 'Package Deleted Successfully']);
        } catch (Exception $e) {
            return errorHandler('Error Deleting Package', $e);
        }
    }
}
