<?php

namespace App\Http\Controllers\Facility;

use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Controller;

class PropertyController extends Controller
{
    function __construct()
    {
         $this->middleware('permission:property list', ['only' => ['index']]);
         $this->middleware('permission:create property', ['only' => ['create','store']]);
         $this->middleware('permission:update property', ['only' => ['update','edit']]);
         $this->middleware('permission:delete property', ['only' => ['destroy']]);
         $this->middleware('permission:view property', ['only' => ['show']]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Property::latest()->paginate(25);
        return view('facility.property.index',compact('data'))
            ->with('i', (request()->input('page', 1) - 1) * 5);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('facility.property.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
          request()->validate([
            'name' => 'required',
        ]);
    
        Property::create($request->all());
    
        return redirect()->route('properties.index')
                        ->with('success','Property created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data=Property::where('id',$id)->first();
        return view('facility.property.show',compact('data'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $data = Property::find($id);
        return view('facility.property.edit',compact('data'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
         request()->validate([
            'name' => 'required',
        ]);
    
        $data = Property::findOrfail($id);
        $data->name=$request->name;
        $data->address=$request->address;
        $data->type=$request->type;
        $data->rent=$request->rent;
        $data->bedrooms=$request->bedrooms;
        $data->bathrooms=$request->bathrooms;
        $data->floor_area=$request->floor_area;
        $data->description=$request->description;
        
        $data->save();
        return redirect()->route('properties.index')
                        ->with('success','Property updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = Property::findOrfail($id);
        $data->delete();
    
        return redirect()->route('properties.index')
                        ->with('success','Property deleted successfully');
    }
}
