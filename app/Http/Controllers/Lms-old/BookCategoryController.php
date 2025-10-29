<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\BookCategory;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class BookCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
         $this->middleware('permission:book category list|book category status change', ['only' => ['index']]);
         $this->middleware('permission:create book category', ['only' => ['create','store']]);
         $this->middleware('permission:update book category', ['only' => ['edit','update']]);
         $this->middleware('permission:delete book category', ['only' => ['destroy']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(): View
    {
        $data = BookCategory::latest()->paginate(5);
        return view('lms.book-category.index',compact('data'))
            ->with('i', (request()->input('page', 1) - 1) * 5);
    }
    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(): View
    {
        return view('lms.book-category.create');
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
            'details' => 'nullable',
        ]);
    
        BookCategory::create($request->all());
    
        return redirect()->route('bookcategories.index')
                        ->with('success','Book Category created successfully.');
    }
    
    /**
     * Display the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(BookCategory $data): View
    {
        return view('lms.book-category.show',compact('data'));
    }
    
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit($id): View
    {
        $data = BookCategory::find($id);
        return view('lms.book-category.edit',compact('data'));
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,  $id): RedirectResponse
    {
         $request->validate([
            'name' => [
                'required',
                'string'
            ]
        ]);
    
        $data = BookCategory::find($id);
        $data->name = $request->input('name');
        $data->details = $request->input('details');
        $data->save();
    
        return redirect()->route('bookcategories.index')
                        ->with('success','Book Category updated successfully');
    }
    
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy($id): RedirectResponse
    {
        $bookList=Book::where('category_id',$id)->get();
        if(!empty($bookList)){
             return redirect()->route('bookcategories.index')
                        ->with('failure','The category cannot be deleted while it still contains books.');
        }else{
            $data = BookCategory::find($id);
            $data->delete();
        
            return redirect()->route('bookcategories.index')
                            ->with('success','Book Category deleted successfully');
        }
    }
    
    
    public function status($id): RedirectResponse
    {
        $data = BookCategory::find($id);
        $status = ($data->status == 1) ? 0 : 1;
        $data->status = $status;
        $data->save();
    
        return redirect()->route('bookcategories.index')
                        ->with('success','Book category status changed successfully');
    }
}
