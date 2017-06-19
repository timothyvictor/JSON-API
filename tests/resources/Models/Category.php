<?php

namespace TimothyVictor\JsonAPI\Test\Resources\Models;

use TimothyVictor\JsonAPI\Model;

class Category extends Model
{
    protected $fillable = ['title', 'description'];
    protected $relationMap = ['articles' => 'getArticles'];

    public function articles()
    {
        return $this->hasMany(Article::class);
    }
    // public function author()
    // {
    //     return $this->belongsTo(Author::class);
    // }
    public function getArticles()
    {
        // return $this->articles()->getResults();
        return $this->articles;
    }
    // public function getAuthor()
    // {
    //     // return $this->articles()->getResults();
    //     return $this->author;
    // }
}