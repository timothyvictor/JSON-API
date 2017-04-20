<?php

namespace TimothyVictor\JsonAPI\Test\Resources\Models;

use TimothyVictor\JsonAPI\Model;

class Category extends Model
{
    protected $relations = ['getArticles'];

    public function articles()
    {
        return $this->hasMany(Article::class);
    }
    public function getArticles()
    {
        // return $this->articles()->getResults();
        return $this->articles;
    }
}