<?php

use App\Http\Controllers\ApiController;
use App\Http\Controllers\TagsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/articles', [ApiController::class, 'createArticle']);

Route::get('/articles/all/{perPage?}', [ApiController::class, 'showAllArticles']);

Route::get('/articles/search/title/{title}/{perPage?}', [ApiController::class, 'searchArticleByTitle']);

Route::get('/articles/search/tags', [ApiController::class, 'searchArticleByTags']);

Route::put('/articles/update/title/{title}/{perPage?}', [ApiController::class, 'updateArticleByTitle']);

Route::delete('/articles/delete/title/{title}', [ApiController::class, 'deleteArticleByTitle']);


Route::get('tags/all/{perPage?}', [TagsController::class, 'showAllTags']);

Route::delete('tags/delete/all', [TagsController::class, 'deleteAllTags']);

Route::get('tags/id/{id}', [TagsController::class, 'searchTagById']);

Route::get('tags/tag/{tag}', [TagsController::class, 'searchTagByTag']);
