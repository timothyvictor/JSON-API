<?php

namespace TimothyVictor\JsonAPI;

use Illuminate\Support\Collection;

class Serializer
{
    private $serialize;

    public function __construct(Serialize $serialize)
    {
        $this->serialize = $serialize;

    } 

    private function serializeManyRelationship(Collection $relations, $include = false) : array
    {
        return ['data' => $relations->map(function(Transformer $item, $key){
            return (array_merge($this->serialize->serializeType($item), $this->serialize->serializeId($item)));
        })->all()];
        // return ['data' => $relations_array];
    }

    private function serializeSingleRelationship($relation, $include = false) : array
    {
        return ['data' => ($relation instanceof Transformer) ? (array_merge($this->serialize->serializeType($relation), $this->serialize->serializeId($relation))) : NULL];
    }

    private function serializeCorrectRelationshipType($relation, $include = false)
    {
        return ($relation instanceof Collection) ? $this->serializeManyRelationship($relation, $include) : $this->serializeSingleRelationship($relation, $include);
         
    }

    public function getRelationships(Transformer $item) : array
    {
        $relationMap = collect($item->getRelationMap());
        $relations = [];
        if($relationMap->isNotEmpty()) {
            $relationMap->each(function($method, $type) use($item, &$relations){
                $relation = $item->{$method}();
                $selfLink = $item->transformSelfLink() . '/' . $item->transformId() . '/' . $type;
                $links = ['links' => ['self' => $selfLink]];
                $relations[$type] = array_merge($this->serializeCorrectRelationshipType($relation, false), $links);
            });
        }
        return (count($relations)) ? ['relationships' => $relations] : [];
    }
}