<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Article;

class ArticleController extends Controller
{
    public function index()
    {
        app('redis')->set('name', 'setyo');
        return response()->json(['Articles' =>  Article::paginate(10)], 200);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'image'         => 'required|mimes:jpg,jpeg',
            'title'         => 'required|min:4',
            'summary'       => 'required|min:4',
            'body'          => 'required|min:4',
            'channel'       => 'required',
            'subchannel'    => 'required',
            'tags'          => 'required',
        ]);

        $image = $request->file('image');
        $completeImageName = $image->getClientOriginalName();
        $imageNameOnly = pathinfo($completeImageName, PATHINFO_FILENAME);
        $extension = $image->getClientOriginalExtension();
        $datePath = date("Y") . "/" . date("m") . "/" . date('d') . "/";
        $randomized = rand();
        $documents = str_replace(' ', '', $imageNameOnly) . '-' . $randomized . '' . time() . '.' . $extension;
        $storePath = 'app/images/' . $datePath;

        $image->move(storage_path($storePath), $documents);

        try {
            $article = new Article;
            $article->slug = Str::slug($request->title, '-');
            $article->title = $request->title;
            $article->summary = $request->summary;
            $article->body = $request->body;
            $article->channel = $request->channel;
            $article->subchannel = $request->subchannel;
            $article->tags = json_encode(explode(',', $request->tags));
            $article->image_path = $datePath . $documents;
            $article->created_by = Auth::user()->id;

            $article->save();

            return response()->json(['article' => $article, 'message' => 'CREATED'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Article Create Failed!'], 409);
        }
    }

    public function update(Request $request, $id)
    {
        if ($request->file('image')) {
            $image = $request->file('image');
            $completeImageName = $image->getClientOriginalName();
            $imageNameOnly = pathinfo($completeImageName, PATHINFO_FILENAME);
            $extension = $image->getClientOriginalExtension();
            $datePath = date("Y") . "/" . date("m") . "/" . date('d') . "/";
            $randomized = rand();
            $documents = str_replace(' ', '', $imageNameOnly) . '-' . $randomized . '' . time() . '.' . $extension;
            $storePath = 'app/images/' . $datePath;

            $image->move(storage_path($storePath), $documents);
        }

        try {
            $article = Article::findOrFail($id);
            $article->title = (isset($request->title) ? $request->title : $article['title']);
            $article->summary = (isset($request->summary) ? $request->summary : $article['summary']);
            $article->body = (isset($request->body) ? $request->body : $article['body']);
            $article->channel = (isset($request->channel) ? $request->channel : $article['channel']);
            $article->subchannel = (isset($request->subchannel) ? $request->subchannel : $article['subchannel']);
            $article->tags = (isset($request->tags) ? json_encode(explode(',', $request->tags)) : $article['tags']);
            $article->image_path = (isset($datePath) ? $datePath . $documents : $article['image_path']);
            $article->updated_by = Auth::user()->id;

            $article->update();

            return response()->json(['article' => $article, 'message' => 'UPDATED'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Article Update Failed!'], 409);
        }
    }

    public function destroy($id)
    {
        try {
            Article::destroy($id);

            return response()->json(['message' => 'Article ID ' . $id . ' Deleted'], 200);
        } catch (\Exception $e) {

            return response()->json(['message' => 'Article not found!'], 404);
        }
    }

    public function comment(Request $request, $article_id)
    {
        $this->validate($request, [
            'comment'         => 'required|min:4',
        ]);

        try {
            Article::findOrFail($article_id);

            $commentExists = [];
            if (app('redis')->exists('comments')) {
                $commentExists = app('redis')->get('comments');
                $commentExists = json_decode($commentExists, true);
            }

            $commentExists[] = [
                'article_id' => (int) $article_id,
                'user_id' => Auth::user()->id,
                'comment' => $request->comment
            ];

            $commentData = json_encode($commentExists);

            app('redis')->set("comments", $commentData);

            return response()->json(['message' => 'Comment on Process'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Article not found!'], 404);
        }
    }
}
