<?php

namespace TimothyVictor\JsonAPI;

use Illuminate\Database\Eloquent\Model as EloquentModel;

class Model extends EloquentModel implements Transformer
{
  
  public function transformType()
  {
    return str_plural(lcfirst(class_basename($this)));
  }

  public function transformId()
  {
    return $this->getKey();
  }

  public function transformAttributes()
  {
    $attributes = collect($this->getAttributes());
    return $attributes->filter(function ($value, $key) {
        return $key != $this->getKeyName();
      })->toArray();
  }

  public function transfromSelfLink()
  { 
    return route("{$this->transformType()}.index");
  }
}