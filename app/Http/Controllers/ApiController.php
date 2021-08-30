<?php
declare(strict_types=1);
namespace App\Http\Controllers;


use App\Http\Requests\ArticleRequest;
use App\Models\Article;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function createArticle(ArticleRequest $request): JsonResponse
    {
        $article = new Article();

        $article->title = $request->input('title');
        $article->content = $request->input('content');
        $article->save();

        ApiController::tagsProcessing($request->input('tags'), $article->id);

        return response()->json([
            'id' => $article->id
        ]);
    }

    public function showAllArticles(int $elemsPerPage): JsonResponse
    {
        $amountOfArticles = Article::count();

        if($amountOfArticles)
        {
            if($amountOfArticles > $elemsPerPage)
            {
                return response()->json(
                    Article::simplePaginate($elemsPerPage)
                );
            } else {
                return response()->json(
                    Article::get()
                );
            }
        }
        return response()->json(
            [
                'message' => 'can`t find any article'
            ]
        );
    }

    public function searchArticleByTitle(string $title, int $elemsPerPage): JsonResponse
    {
        $countOfArticles = Article::where('title', $title)->count();

        if ($countOfArticles) {
            if($countOfArticles > $elemsPerPage)
            {
                return response()->json(
                    Article::where('title', $title)->paginate($elemsPerPage)
                );
            } else {
                return response()->json(
                    Article::where('title', $title)->get()
                );
            }
        }

        return response()->json(
            [
                'message' => sprintf(
                    'cant`t find any article with a title `%s`',
                    $title
                )
            ]
        );
    }

    public function updateArticleByTitle(Request $request, string $title, int $elemsPerPage): JsonResponse
    {
        if ($request) {
            $countOfArticles = Article::where('title', $title)->count();

            if ($countOfArticles) {
                $articles = Article::where('title', $title)->get();
                $response = [];
                foreach ($articles as $article) {
                    if($request->input('title'))
                    {
                        $article->title = $request->input('title');
                    }

                    if($request->input('content'))
                    {
                        $article->content = $request->input('content');
                    }

                    if($request->input('tags'))
                    {
                        $article = ApiController::tagsProcessing($request->input('tags'), $article->id);
                    }
                    $article->save();
                    $response[] = $article;
                }

                if($countOfArticles > $elemsPerPage)
                {
                    return response()->json(
                        Article::where('title', $response[0]->title)->paginate($elemsPerPage)
                    );
                } else {
                    return response()->json(
                        $response
                    );
                }
            }

            return response()->json(
                [
                    'message' => sprintf(
                        'cant`t find any article with a title `%s`',
                        $title
                    )
                ]
            );
        }
        return response()->json(
            [
                'message' => "nothing to update"
            ]
        );
    }


    public function deleteArticleByTitle(string $title): JsonResponse
    {
        $countOfArticles = Article::where('title', $title)->count();

        if ($countOfArticles) {
            $articles = Article::where('title', $title)->get();

            foreach ($articles as $article) {
                $articleID = $article->id;
                $tagIdsArr = $article->tag_ids;
                foreach($tagIdsArr as $tagID)
                {
                    $tagIDInt = (int)$tagID;
                    ApiController::deleteArticleIDFromTagsTable($articleID, $tagIDInt);
                }

                $article->delete();
            }

            return response()->json(
                [
                    'message' => sprintf(
                        'article(s) with a title `%s` deleted',
                        $title
                    )
                ]
            );
        }

        return response()->json(
            [
                'message' => sprintf(
                    'cant`t find any article with a title `%s`',
                    $title
                )
            ]
        );
    }

    /**
     * @param array $tags
     * @param int $articleID
     * @return Article
     */
    private function tagsProcessing(array $tags, int $articleID): Article
    {
        $tagIdsArray = [];
        foreach ($tags as $tag) {
            $existedTag = Tag::where('tag', $tag)->first();

            if ($existedTag) {

                $articleIDsArr = $existedTag->article_ids;
                if(!$articleIDsArr)
                {
                    $articleIDsArr = [];
                }
                if (!in_array($articleID, $articleIDsArr)) {
                    $articleIDsArr[] = $articleID;
                    $existedTag->article_ids = $articleIDsArr;
                    $existedTag->save();
                }

                $tagIdsArray[] = $existedTag->id;
            } else {

                $newTag = new Tag();

                $newTag->tag = $tag;
                $articleIDArray = [];
                $articleIDArray[] = $articleID;
                $newTag->article_ids = $articleIDArray;
                $newTag->save();

                $tagIdsArray[] = $newTag->id;
            }
        }

        $article = Article::find($articleID);

        $newTagIds = array_unique($tagIdsArray);
        // If the article already had tags, and we want to update them (delete same or all of them),
        // so we need to find tags that the article won`t use anymore and delete `id` of this article for this tags
        // in `Tags` table.
        $oldTagIds = $article->tag_ids;
        if(!$oldTagIds)
        {
            $oldTagIds = [];
        }
        $diffTagIds = array_diff($oldTagIds, $newTagIds);
        if($diffTagIds)
        {
            foreach($diffTagIds as $tagID)
            {
                ApiController::deleteArticleIDFromTagsTable($articleID, $tagID);
            }
        }

        //In some situations $newTagIds is json, not array.
        //So I have to use construction below for saving array in DB, not json.
        $tempArray = [];
        foreach ($newTagIds as $value)
        {
            $tempArray[] = $value;
        }

        $article->tag_ids = $tempArray;
        $article->save();

        return $article;
    }

    private function deleteArticleIDFromTagsTable(int $delArticleID, int $tagID): void
    {
        $tag = Tag::find($tagID);
        $articleIdsArr = $tag->article_ids;
        $withoutDeletedID = [];
        foreach ($articleIdsArr as $articleId)
        {
            if($delArticleID !== (int)$articleId)
            {
                $withoutDeletedID[] = $articleId;
            }
        }

        $tag->article_ids = $withoutDeletedID;

        $tag->save();
    }
}
