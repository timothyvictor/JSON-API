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
    $attributes = collect($this->getAttributes());
    return $attributes->filter(function ($value, $key) {
        return $key != $this->getKeyName();
      })->toArray();
  }

  public function transfromSelfLink() : string
  { 
    return route("{$this->transformType()}.index");
  }

  public function getRelationMap()
  {
    return $this->relationMap;
  }
}