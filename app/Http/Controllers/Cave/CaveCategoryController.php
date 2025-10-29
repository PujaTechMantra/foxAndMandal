<?php

namespace App\Http\Controllers\Cave;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CaveForm;
use App\Models\CaveLocation;
use App\Models\CaveCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CaveCategoryController extends Controller
{
    function __construct()
    {
         $this->middleware('permission:view cavity room', ['only' => ['index']]);
         $this->middleware('permission:create cavity room', ['only' => ['create','store']]);
         $this->middleware('permission:update cavity room', ['only' => ['edit','update']]);
         $this->middleware('permission:delete cavity room', ['only' => ['destroy']]);
         ;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        
         // Validate the request
        $request->validate([
            'keyword' => 'nullable|string|min:1',
        ]);
    
        $keyword = $request->keyword;
    
        // Eloquent query with relationships and search conditions
        $data = CaveCategory::latest('id')
            ->where(function ($query) use ($keyword) {
                $query->where('name', 'LIKE', "%$keyword%")
                      ;
            })
            
            ->with('location')->paginate(25);
    
        // Return the search results as JSON
        return view('cave.category.index',compact('data'))
            ->with('i', (request()->input('page', 1) - 1) * 5);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $location=CaveLocation::all();
        
        return view('cave.category.create',compact('location'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        request()->validate([
            'name' => 'required',
           
        ]);
        
        
            $data=new CaveCategory();
            $data->location_id=$request['location_id'];
            $data->name=$request['name'];
            $data->save();
        
        return redirect()->route('vaultcategories.index')
                        ->with('success','Vault Category created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id): View
    {
        $data = CaveCategory::find($id);
        return view('cave.category.view',compact('data'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        $data = CaveCategory::find($id);
         $location=CaveLocation::all();
        return view('cave.category.edit',compact('data','location'));
    }

    /**
     * Update the specified resource in storage.
     */
     public function update(Request $request,  $id): RedirectResponse
    {
         $request->validate([
            'name' => [
                'required'
            ]
        ]);
    
        $data = CaveCategory::find($id);
        $data->location_id=$request['location_id'];
        $data->name=$request['name'];
        $data->save();
    
        return redirect()->back()
                        ->with('success','Vault Category updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): RedirectResponse
    {
       
        $data = CaveCategory::find($id);
        $data->delete();
    
        return redirect()->route('vaultcategories.index')
                        ->with('success','Vault Category deleted successfully');
    }
    
    
    
    public function roomList(Request $request,$id)
    {
        $stateName=CaveLocation::where('id',$id)->first();
		$region = CaveCategory::where('location_id',$id)->get();
        $resp = [
            'location' => $stateName->location,
            'room' => [],
        ];

        foreach($region as $area) {
            $resp['room'][] = [
                'room_id' => $area->id,
                'name' => $area->name,
            ];
        }
        
		return response()->json(['error' => false, 'resp' => 'Location wise room list', 'data' => $resp]);
    }

}
