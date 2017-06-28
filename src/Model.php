<?php

namespace TimothyVictor\JsonAPI;

use Illuminate\Database\Eloquent\Model as EloquentModel;

class Model extends EloquentModel implements Transformer
{
    protected $relationMap = [];
  
    public function transformType() : string
    {
        return str_plural(lcfirst(class_basename($this)));
    }

    public function transformId() : string
    {
        return $this->getRouteKey();
    }

    public function transformAttributes() : array
    {
        // not sure about any of this here:
        $attributes = collect($this->toArray());
        return $attributes->filter(function ($value, $key) {
            // also should this be routeKey?
            return $key != $this->getKeyName() && substr($key, -3) != "_id";
        })->toArray();
    }

    public function transformSelfLink() : string
    {
        return route("{$this->transformType()}.index");
    }

    public function transformCollectionLink()
    {
        return route("{$this->transformType()}.index");
    }

    public function getRelationMap()
    {
        return $this->relationMap;
    }
}
