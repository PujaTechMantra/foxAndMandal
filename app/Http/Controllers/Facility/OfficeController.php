<?php

namespace App\Http\Controllers\Facility;

use App\Models\Office;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Controller;
class OfficeController extends Controller
{
     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
         $this->middleware('permission:view office', ['only' => ['index']]);
         $this->middleware('permission:create office', ['only' => ['create','store']]);
         $this->middleware('permission:update office', ['only' => ['update','edit']]);
         $this->middleware('permission:delete office', ['only' => ['destroy']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(): View
    {
        $data = Office::latest()->paginate(25);
        return view('facility.office.index',compact('data'))
            ->with('i', (request()->input('page', 1) - 1) * 5);
    }
    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(): View
    {
        return view('facility.office.create');
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request): RedirectResponse
    {
        request()->validate([
            'name' => 'required',
            'address' => 'required',
        ]);
    
        Office::create($request->all());
    
        return redirect()->route('offices.index')
                        ->with('success','Office created successfully.');
    }
    
    /**
     * Display the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Office $product): View
    {
        return view('facility.office.show',compact('product'));
    }
    
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit($id): View
    {
        $data = Office::find($id);
        return view('facility.office.edit',compact('data'));
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id): RedirectResponse
    {
         request()->validate([
            'name' => 'required',
        ]);
    
        $data = Office::findOrfail($id);
        $data->name=$request->name;
        $data->address=$request->address;
        $data->save();
        return redirect()->route('offices.index')
                        ->with('success','Office updated successfully');
    }
    
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy($id): RedirectResponse
    {
        $data = Office::findOrfail($id);
        $data->delete();
    
        return redirect()->route('offices.index')
                        ->with('success','Office deleted successfully');
    }
}
