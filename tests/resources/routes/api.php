<?php

$controller_namespace = 'TimothyVictor\JsonAPI\Test\Resources\Controllers\\';

// Route::get('/categories', $controller_namespace . 'CategoriesController@index');
// Route::get('/categories/{id}', $controller_namespace . 'CategoriesController@show');
Route::resource('categories', $controller_namespace . 'CategoriesController');
Route::resource('articles', $controller_namespace . 'ArticlesController');
Route::resource('comments', $controller_namespace . 'CommentsController');
