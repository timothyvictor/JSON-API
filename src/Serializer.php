<?php

namespace TimothyVictor\JsonAPI;

use Illuminate\Support\Collection;

class Serializer
{

    private $apiMember = ['jsonapi' => [ "version" => "1.0" ]];


    public function serializeResourceObject($item, $parameters) : array
    {
        return array_merge($this->serializeId($item), $this->serializeType($item), $this->serializeAttributes($item, $parameters), $this->serializeRelationships($item));
    }

    private function serializeType($item) : array
    {
        return ['type' => $item->transformType()];
    }

    private function serializeId($item) : array
    {
        return ['id' => (string) $item->transformId()];
    }

    private function filterAttributes($attributes, $fields){
        $fieldsArray = explode(',', $fields);
        return array_intersect_key($attributes, array_flip($fieldsArray));
    }

    private function serializeAttributes($item, $parameters) : array
    {
        $fields = $parameters['fields'];
        $attributes = $item->transformAttributes();
        if (!empty($fields) && array_key_exists($item->transformType(), $fields)){
            $attributes = $this->filterAttributes($attributes, $fields[$item->transformType()]);
        }
        return ['attributes' => $attributes];
    }

    public function topLevelLinksObject($items, array $parameters)
    {
        $links = [
             'links' => [
                'self' => ($items instanceof Collection) ? url()->full() : $items->transformSelfLink() . "/{$items->id}",
            ]
        ];
        if(!empty($parameters['pagination'])){
            $links['links']['pagination'] = $parameters['pagination'];
        }
        // exit(dump($links));
        return $links;
    }

    private function serializeManyRelationship(Collection $relations, $include = false) : array
    {
        return ['data' => $relations->map(function(Transformer $item, $key){
            return (array_merge($this->serializeType($item), $this->serializeId($item)));
        })->all()];
    }

    private function serializeSingleRelationship($relation, $include = false) : array
    {
        return ['data' => ($relation instanceof Transformer) ? (array_merge($this->serializeType($relation), $this->serializeId($relation))) : NULL];
    }

    private function serializeCorrectRelationshipType($relation, $include = false)
    {
        return ($relation instanceof Collection) ? $this->serializeManyRelationship($relation, $include) : $this->serializeSingleRelationship($relation, $include);
         
    }

    public function serializeRelationships(Transformer $item) : array
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

    public function getApiMember()
    {
        return $this->apiMember;
    }
}