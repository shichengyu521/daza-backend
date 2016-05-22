<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Post;

use Auth;

use Illuminate\Http\Request;

use App\Http\Requests;

class PostController extends Controller
{

    public function __construct()
    {
        // 执行 auth 认证
        $this->middleware('auth', [
            'except' => [
                'index',
                'show'
            ]
        ]);
    }

    public function index(Request $request)
    {
        $params = $request->all();

        $query = Post::orderBy('updated_at', 'desc')
            ->with([
                'user'  => function ($query) {
                    $query->select('id', 'name');
                },
                'group' => function ($query) {
                    $query->select('id', 'name');
                },
            ]);

        if (array_key_exists('group_id', $params)) {
            $query->where('group_id', $params['group_id']);
        }

        $results = $query->paginate();
        return $this->pagination($results);
    }

    public function store(Request $request)
    {
        $request->merge(['user_id' => Auth::id()]);
        $this->validate($request, [
            'group_id' => 'required|exists:groups,id',
            'title'    => 'required|min:6|max:255',
            'content'  => 'required',
        ]);

        $data = Post::create($request->all());
        if ($data) {
            return $this->success($data);
        }
        return $this->failure();
    }

    public function show(Request $request, $post_id)
    {
        $request->merge(['post' => $post_id]);
        $this->validate($request, ['post' => 'exists:posts,id']);

        $data = Post::with([
                'user'  => function ($query) {
                    $query->select('id', 'name');
                },
                'group' => function ($query) {
                    $query->select('id', 'name');
                },
            ])
            ->find($post_id);
        return $this->success($data);
    }

    public function update(Request $request)
    {
        return $this->failure();
    }

    public function destroy(Request $request, $post_id)
    {
        $request->merge(['post' => $post_id]);
        $this->validate($request, ['post' => 'exists:posts,id,user_id,' . Auth::id()]);

        $data = Post::where($post_id);
        $data->delete();
        return $this->failure();
    }

}
