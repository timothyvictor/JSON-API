<?php

namespace TimothyVictor\JsonAPI\Test\Resources\Models;

use TimothyVictor\JsonAPI\Model;

class Author extends Model
{
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
