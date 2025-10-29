<?php

namespace App\Http\Controllers\Cave;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CaveForm;
use App\Models\CaveLocation;
use App\Models\CaveCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CaveLocationController extends Controller
{
    function __construct()
    {
         $this->middleware('permission:view cave location', ['only' => ['index']]);
         $this->middleware('permission:create cave location', ['only' => ['create','store']]);
         $this->middleware('permission:update cave location', ['only' => ['edit','update']]);
         $this->middleware('permission:delete cave location', ['only' => ['destroy']]);
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
        $data = CaveLocation::latest('id')
            ->where(function ($query) use ($keyword) {
                $query->where('location', 'LIKE', "%$keyword%")
                      ;
            })
            
            ->paginate(25);
    
        // Return the search results as JSON
        return view('cave.location.index',compact('data'))
            ->with('i', (request()->input('page', 1) - 1) * 5);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        
        return view('cave.location.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        request()->validate([
            'location' => 'required',
           
        ]);
        
           function generateUniqueAlphaNumeric($length = 10) {
            $random_string = '';
            for ($i = 0; $i < $length; $i++) {
                $number = random_int(0, 36);
                $character = base_convert($number, 10, 36);
                $random_string .= $character;
            }
                return $random_string;
            }
            $data=new CaveLocation();
            $data->location=$request['location'];
            $data->qrcode=strtoupper(generateUniqueAlphaNumeric(10));
            $data->save();
        
        return redirect()->route('vaultlocations.index')
                        ->with('success','Vault Location created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id): View
    {
        $data = CaveLocation::find($id);
        return view('cave.location.view',compact('data'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        $data = CaveLocation::find($id);
        return view('cave.location.edit',compact('data'));
    }

    /**
     * Update the specified resource in storage.
     */
     public function update(Request $request,  $id): RedirectResponse
    {
         $request->validate([
            'location' => [
                'required'
            ]
        ]);
    
        $data = CaveLocation::find($id);
        $data->location=$request['location'];
        $data->save();
    
        return redirect()->back()
                        ->with('success','Vault Location updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): RedirectResponse
    {
       
        $data = CaveLocation::find($id);
        $data->delete();
    
        return redirect()->route('vaultlocations.index')
                        ->with('success','Vault Location deleted successfully');
    }
}
