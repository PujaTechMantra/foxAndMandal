<?php

namespace App\Http\Controllers\Api\Fms;

use App\Http\Controllers\Controller;
use App\Models\MatterCode;
use Illuminate\Http\Request;

class MatterCodeController extends Controller
{
    public function index(Request $request)
    {
       $data=MatterCode::get();

        if ($data) {
            return response()->json(['status'=>true,'message' => 'List of matter code','data' => $data ], 200);
        }else {
            return response()->json([
                'status' => false,
                'message' => 'Matter code list not found'
            ], 404);
        }
    }
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'matter_code' => 'required',
            'client_name' => 'required|string|max:1000',
        ]);

        try {
            $matterCode = MatterCode::create($validatedData);

            return response()->json([
                'status' => true,
                'message' => 'Matter Code created successfully.',
                'data' => $matterCode
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to create Matter Code.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    
    public function suggest(Request $request)
    {
        $search = $request->input('query');

        $results = MatterCode::where('matter_code', 'like', "%{$search}%")
                            ->orWhere('client_name', 'like', "%{$search}%")
                            ->limit(10)
                            ->get(['matter_code', 'client_name']);

        return response()->json(['success' => true, 'data' => $results]);
    }
}
