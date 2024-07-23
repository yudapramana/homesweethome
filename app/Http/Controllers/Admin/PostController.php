<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Category, Kabkota, Post, Tag};
use Auth;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;

class PostController extends Controller
{
    /**
         * Create a new controller instance.
         *
         * @return void
         */
    public function __construct()
    {
        $this->middleware('auth');
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // dd($request->category);
            $category = $request->input('category');
            $pQuery = Post::query();
            $pQuery = $pQuery->whereHas('category', function ($q) use ($category) {
                $q->where('slug', $category);
            });
            $pQuery = $pQuery->orderBy('created_at', 'desc');
            $posts = $pQuery->get();

            return DataTables::of($posts)->addIndexColumn()->make(true);
        } else {
            return response()->json([], 400);
        }

        // return response()->json($reports, 200);
    }

    public function store()
    {
        $validated = request()->validate([
            'cover' => 'required',
            'title' => 'required',
            'category' => 'required',
            'desc' => 'required',
            'keywords' => 'required',
            'meta_desc' => 'required',
            'kabkota' => 'sometimes',
            'editor' => 'sometimes',
            'photographer' => 'sometimes',
            'is_breaking' => 'sometimes',
            'is_recommended' => 'sometimes',
            'is_featured' => 'sometimes',
            'is_slider' => 'sometimes'
        ]);

        // $category = Category::where('slug', $validated['category'])->first();

        $post = new Post();
        $post->cover = $validated['cover'];
        $post->title = $validated['title'];
        $post->slug         = \Str::slug(substr($validated['title'], 0, 150));
        $post->user_id      = Auth::user()->id;
        // $post->category_id  = $category ? $category->id : 0;
        $post->category_id  = $validated['category'];
        $post->desc = $validated['desc'];
        $post->keywords = $validated['keywords'];
        $post->meta_desc = $validated['meta_desc'];
        $post->id_kabkota = $validated['kabkota'];
        $post->editor = $validated['editor'];
        $post->photographer = $validated['photographer'];
        $post->is_breaking = $validated['is_breaking'];
        $post->is_recommended = $validated['is_recommended'];
        $post->is_featured = $validated['is_featured'];
        $post->is_slider = $validated['is_slider'];
        $post->save();
        $post->fresh();

        return response()->json(['message' => 'Laporan kerja sukses dibuat!']);

    }

    public function edit(Post $post)
    {
        // return $post->id;
        // $post = Post::find($post);
        return $post;
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
        // return $request->all();
        $validator = Validator::make($request->all(), [
            "title"     => "required|unique:posts,title," . $id,
            'cover' => 'sometimes',
            'category' => 'required',
            'desc' => 'required',
            'keywords' => 'required',
            'meta_desc' => 'required',
            'kabkota' => 'sometimes',
            'editor' => 'sometimes',
            'photographer' => 'sometimes',
            'is_breaking' => 'sometimes',
            'is_recommended' => 'sometimes',
            'is_featured' => 'sometimes',
            'is_slider' => 'sometimes'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }


        $post = Post::findOrFail($id);
        $post->cover        = isset($request->cover) ? $request->cover : null;
        $post->title        = $request->title;
        $post->slug         = \Str::slug(substr($request->title, 0, 150));
        $post->category_id  = $request->category;
        $post->desc         = $request->desc;
        $post->keywords     = $request->keywords;
        $post->meta_desc    = $request->meta_desc;
        $post->id_kabkota    = $request->kabkota;
        $post->is_breaking      = $request->is_breaking;
        $post->is_recommended   = $request->is_recommended;
        $post->is_featured      = $request->is_featured;
        $post->is_slider        = $request->is_slider;
        $post->editor    = $request->editor;
        $post->photographer   = $request->photographer;
        $post->save();

        if(isset($request->tags)) {
            $post->tags()->sync($request->tags);
        }

        $categorySlug = \App\Models\Category::find($request->category)->slug;


        return response()->json(['success' => true]);

        // return redirect()->route('posts.index', ['category' => $categorySlug])->with('success', 'Data added successfully');
    

        // return redirect()->route('posts.index')->with('success', 'Data updated successfully');
    }

}
