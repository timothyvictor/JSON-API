<?php

namespace TimothyVictor\JsonAPI;

use Illuminate\Support\Collection;

class Serializer
{

    private $apiMember = ['jsonapi' => [ "version" => "1.0" ]];

    public function serializeCollection(Collection $collection) : array
    {
        return array_merge($this->apiMember, [
            'data' => $collection->map(function (Transformer $item) {
                return $this->serializeResourceObject($item);
            }),
        ], $this->serializeResourceLink($collection->first()));
    }

    public function serializeResource(Transformer $item) : array
    {
        return array_merge($this->apiMember, ['data' => $this->serializeResourceObject($item)]);
    }

    private function serializeResourceObject($item) : array
    {
        return array_merge($this->serializeId($item), $this->serializeType($item), $this->serializeAttributes($item), $this->serializeRelationships($item));
    }

    private function serializeType($item) : array
    {
        return ['type' => $item->transformType()];
    }

    private function serializeId($item) : array
    {
        return ['id' => (string) $item->transformId()];
    }

    private function serializeAttributes($item) : array
    {
        return ['attributes' => $item->transformAttributes()];
    }

    private function serializeResourceLink($item) : array
    {
        return [
            'links' => [
                'self' => $item->transfromSelfLink()
            ]
        ];
    }

    private function serializeManyRelationship(Collection $relations) : array
    {
        $relations_array = $relations->map(function(Transformer $item, $key){
            return (array_merge($this->serializeType($item), $this->serializeId($item)));
        })->all();
        return ['data' => $relations_array];
    }

    public function serializeRelationships(Transformer $item) : array
    {
        $relationshipMethods = collect($item->getRelationshipMethods());
        // dump($relationshipMethods);
        $relations = [];
        if($relationshipMethods->isNotEmpty()) {
            $relationshipMethods->each(function($method) use($item, &$relations){
                $relation = $item->{$method}();
                if ($relation instanceof Collection && $relation->isNotEmpty()) {
                    $relations[$relation->first()->transformType()] = $this->serializeManyRelationship($relation);
                } else if ($relation instanceof Transformer){
                    $relations[$relation->transformType()] = $this->transformSingleRelationship($relation);
                }
            });
        }
        if (count($relations)){
            return [
                'relationships' => $relations
            ];
        } else return [];
    }
}