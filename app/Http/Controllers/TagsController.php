<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;

class TagsController extends Controller
{
    public function showAllTags(int $elemsPerPage = 10): JsonResponse
    {
        $amountOfTags = Tag::count();

        if(!$amountOfTags)
        {
            return response()->json(
                [
                    'message' => 'can`t find any tags in DB'
                ]
            );
        }

        if($amountOfTags > $elemsPerPage)
        {
            return response()->json(
                Tag::simplePaginate($elemsPerPage)
            );
        } else {
            return response()->json(
                Tag::get()
            );
        }

    }

    public function deleteAllTags(): JsonResponse
    {
        $tags = Tag::get();
        if ($tags) {
            foreach ($tags as $tag) {
                $tag->delete();
            }

            $articles = Article::get();

            foreach ($articles as $article) {
                $article->tag_ids = [];
                $article->save();
            }

            return response()->json(
                [
                    'message' => 'all tags deleted'
                ]
            );
        }

        return response()->json(
            [
                'message' => 'no tags to delete'
            ]
        );
    }

    public function searchTagById($id): JsonResponse
    {
        $tag = Tag::find($id);

        if ($tag) {
            return response()->json(
                $tag
            );
        }

        return response()->json(
            [
                'message' => sprintf(
                    'can`t find tag with id `%d`',
                    $id
                )
            ]
        );
    }

    public function searchTagByTag($requestedTag): JsonResponse
    {
        $tag = Tag::where('tag', $requestedTag)->first();

        if ($tag) {
            return response()->json(
                $tag
            );
        }

        return response()->json(
            [
                'message' => sprintf(
                    'can`t find tag `%s`',
                    $requestedTag
                )
            ]
        );
    }

}
