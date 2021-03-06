<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    public function create()
    {
        return view('posts.create');
    }

    public function store()
    {
        $inputs = request()->validate([
            'title' => 'required | max:50',
            'body' => 'required',
            'post_image' => 'file'
        ]);

        if(request('file'))
        {
            $inputs['post_image'] = request('file')->store('images');
        }

        Auth::user()->posts()->create($inputs);
        Session::flash('message', 'Post created successfully!');

        return redirect()->back();
    }

    public function show(Post $post)
    {
        if($post->comments())
        {
            $comments = $post->comments()->orderBy('created_at', 'desc')->get();
            return view('posts.index', compact('post', 'comments'));
        }
        return view('posts.index', compact('post'));
    }

    public function destroy(Post $post)
    {
        //Because relationship is polymorphic, SQL does not understand Laravel Polymorphism
        // so when deleting a polymorphic object, one must be explicit as to delete the respective
        //children of the relationship 1st !!!
        $post->comments()->delete();
        $post->delete();

      return redirect()->back();
    }

    public function edit(Post $post)
    {
        return view('posts.edit', compact('post'));
    }

    public function update(Post $post)
    {
        $this->authorize('update', $post);
        $inputs = Validator::make(request()->all(),[
            'title' => 'required | max:50',
            'body' => 'required',
            'post_image' => 'file',
        ]);


        if(request('file'))
        {
           $post->post_image = request('file')->store('images');
        }


        //Get image updating to work
        if(!$inputs->fails())
        {
            $post->title = request('title');
            $post->body = request('body');

            $post->save();

            return response()->json([
                'code' => 200,
                'msg' => "Post updated successfully"
            ]);
        }
        else
        {
            return response()->json([
             'code'=> 0,
             'error' => $inputs->errors()]);
        }
        // $post->update($inputs);
        // Session::flash('message', 'Post updated successfully!');

        // return redirect()->route('home');
    }
}
