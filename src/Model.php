<?php

namespace TimothyVictor\JsonAPI;

use Illuminate\Database\Eloquent\Model as EloquentModel;

class Model extends EloquentModel implements Transformer
{
  protected $relations = [];
  
  public function transformType() : string
  {
    return str_plural(lcfirst(class_basename($this)));
  }

  public function transformId() : string
  {
    return $this->getKey();
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

  public function getRelationshipMethods()
  {
    return $this->relations;
  }
}