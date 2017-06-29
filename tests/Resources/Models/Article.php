<?php

namespace TimothyVictor\JsonAPI\Test\Resources\Models;

use TimothyVictor\JsonAPI\Model;

class Article extends Model
{
    protected $visible = ['title', 'created_at', 'updated_at'];

    protected $relationMap = ['category' => 'getCategory', 'author' => 'getAuthor', 'comments' => 'getComments'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function author()
    {
        return $this->belongsTo(Author::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function getCategory()
    {
        // return $this->articles()->getResults();
        return $this->category;
    }

    public function getAuthor()
    {
        // return $this->articles()->getResults();
        return $this->author;
    }

    public function getComments()
    {
        // return $this->articles()->getResults();
        return $this->comments;
    }
}
