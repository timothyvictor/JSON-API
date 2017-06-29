<?php

namespace TimothyVictor\JsonAPI\Test\Resources\Models;

use TimothyVictor\JsonAPI\Model;

class Comment extends Model
{
    // protected $visible = ['title', 'created_at', 'updated_at'];

    protected $relationMap = ['author' => 'getAuthor'];

    public function author()
    {
        return $this->belongsTo(Author::class);
    }

    public function getAuthor()
    {
        return $this->author;
    }
}
