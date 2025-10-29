<?php

namespace App\Http\Controllers\Facility;

use App\Models\MatterCode;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MatterCodeController extends Controller
{
    
    public function index()
    {
        $data = MatterCode::latest()->paginate(25);
        return view('facility.matter-code.index',compact('data'))
            ->with('i', (request()->input('page', 1) - 1) * 5);
    }
    public function create()
    {
        return view('facility.matter-code.create');
    }

    public function store(Request $request)
    {
        request()->validate([
            'matter_code' => 'required',
            'client_name' => 'required|string|max:1000',
        ]);
    
        MatterCode::create($request->all());
    
        return redirect()->route('matter-code.index')
                        ->with('success','Matter Code created successfully.');
    }

    public function show(string $id)
    {
        $data=MatterCode::where('id',$id)->first();
        return view('facility.matter-code.show',compact('data'));
    }

    public function edit(string $id)
    {
        $data = MatterCode::find($id);
        return view('facility.matter-code.edit',compact('data'));
    }

    public function update(Request $request, string $id)
    {
        request()->validate([
            'matter_code' => 'required',
            'client_name' => 'required|string|max:1000',
        ]);
    
        $data = MatterCode::findOrfail($id);
        $data->client_name=$request->client_name;
        $data->matter_code=$request->matter_code;
        
        $data->save();
        return redirect()->route('matter-code.index')
                        ->with('success','Matter Code updated successfully');
    }

    public function destroy(string $id)
    {
        $data = MatterCode::findOrfail($id);
        $data->delete();
    
        return redirect()->route('matter-code.index')
                        ->with('success','Matter Code deleted successfully');
    }

    public function downloadSampleCsv()
    {
        $headers = [
            "Content-Type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=sample_matter_code.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0",
        ];

        $columns = ['Client_Name', 'Matter_Code'];
        
        $callback = function () use ($columns) {
            $file = fopen('php://output', 'w');

            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, $columns);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function uploadCsv(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|mimes:csv,txt|max:2048',
            ]);

            $file = $request->file('file');

            if (!$file) {
                throw new \Exception("No file uploaded or incorrect input name");
            }

            if (($handle = fopen($file->getRealPath(), 'r')) === false) {
                throw new \Exception("Unable to open file");
            }

            $header = fgetcsv($handle, 1000, ',');

            $header[0] = preg_replace('/^\x{FEFF}/u', '', $header[0]);

            $clientIndex = array_search('Client_Name', $header);
            $matterIndex = array_search('Matter_Code', $header);

            if ($clientIndex === false || $matterIndex === false) {
                throw new \Exception("CSV must contain 'Client Name' and 'Matter Code' columns.");
            }

            $insertData = [];
            while (($row = fgetcsv($handle, 1000, ',')) !== false) {

                if (!empty($row[$clientIndex]) && !empty($row[$matterIndex])) {
                    $insertData[] = [
                        'client_name' => $row[$clientIndex],
                        'matter_code' => $row[$matterIndex],
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ];
                }
            }

            fclose($handle);

            if (!empty($insertData)) {
                MatterCode::insert($insertData);
            }

            return back()->with('success', 'CSV uploaded successfully!');

        } catch (\Throwable $e) {
            dd([
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
            ]);

        }
    }

    public function suggestMatterCode(Request $request)
    {
        $search = $request->input('query');

        $results = MatterCode::where('matter_code', 'like', "%{$search}%")
                            ->orWhere('client_name', 'like', "%{$search}%")
                            ->limit(10)
                            ->get(['matter_code', 'client_name']);

        return response()->json($results);
    }

}
