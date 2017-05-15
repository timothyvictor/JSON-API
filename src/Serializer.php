<?php

namespace TimothyVictor\JsonAPI;

use Illuminate\Support\Collection;

class Serializer
{

    private $apiMember = ['jsonapi' => [ "version" => "1.0" ]];
    private $requestedIncludes = [];
    private $includes = [];

    public function serializeCollection(Collection $collection) : array
    {
        return array_merge($this->apiMember, [
            'data' => $collection->map(function (Transformer $item) {
                return $this->serializeResourceObject($item);
            }),
        ], $this->serializeResourceLink($collection->first()));
    }

    public function serializeResource(Transformer $item, $includes = []) : array
    {
        // dd($includes);
        $this->requestedIncludes = $includes;
        // dump($this->requestedIncludes);
        return array_merge($this->apiMember, ['data' => $this->serializeResourceObject($item)], $this->serializeResourceLink($item), $this->getIncludes($item, $this->requestedIncludes));
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

    private function serializeManyRelationship(Collection $relations, $include = false) : array
    {
        return ['data' => $relations->map(function(Transformer $item, $key){
            return (array_merge($this->serializeType($item), $this->serializeId($item)));
        })->all()];
        // return ['data' => $relations_array];
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
                $selfLink = $item->transfromSelfLink() . '/' . $item->transformId() . '/' . $type;
                $links = ['links' => ['self' => $selfLink]];
                // $this->processIncludes($item, $relation);
                $relations[$type] = array_merge($this->serializeCorrectRelationshipType($relation, false), $links);
            });
        }
        return (count($relations)) ? ['relationships' => $relations] : [];
    }

    // public static function getIncludeMap($includes, $relationMap)
    // {
        // need to check if requested includes are valid and then provide them. 
        // $includeFirstRelationMap = [];
        // collect($includes)->map(function($item, $key){
        //     return $item[0];
        // })
        // ->each(function($item, $key) use(&$includeFirstRelationMap, $relationMap) {
        //     if (array_key_exists($item, $relationMap)){
        //         $includeFirstRelationMap = array_merge($includeFirstRelationMap, [$item => $relationMap[$item]]);
        //     }
        //     //else throw exception
        // });
        // return $includeFirstRelationMap;
        // return collect($includeFirstRelationMap);

    // }
    private function includeItem($item, $includes)
    {
        // $type = $item->transformType();
        if(isset($item)){
            $this->includes =  array_unique(array_merge($this->includes, [$this->serializeResourceObject($item)]), SORT_REGULAR);
        }
        if (!empty($includes)){
            return $this->includeResources($item, $includes);
        }
        return;
    }

    private function includeCollection($items, $includes)
    {
        $type = $items->first()->transformType();
        $items_to_include = $items->map(function($item, $key){
            return $this->serializeResourceObject($item);
        })->toArray();
        if (!empty($includes)){
            $items->each(function($item) use($includes){
                $this->includeResources($item, $includes);
            });
        }
        return $this->includes = array_unique(array_merge($this->includes, $items_to_include), SORT_REGULAR);
    }

    private function serializeCorrectIncludeType($item, $includes)
    {
        return ($item instanceof Collection) ? $this->includeCollection($item, $includes) : $this->includeItem($item, $includes);
    }

    private function includeResources($item, $includes){
        // dump("item:", $item->transformType(), "includes:", $includes, "included:", $this->includes);
        $relationMap = $item->getRelationMap();
        $include = array_shift($includes);
        if(array_key_exists($include, $relationMap)){
            $relation = $item->{$relationMap[$include]}();
            return $this->serializeCorrectIncludeType($relation, $includes);
        }
        throw new InvalidIncludeException("{$include} is not a valid include for {$item->transformType()}");
    }
    private function populateIncludes($item, $includes)
    {   
        // dump($includes);
        collect($includes)->each(function($include, $key) use($item){
            $include_collection = Helper::dot_to_array($include);
            $this->includeResources($item, $include_collection);
            // $this->includeResources($item, $include);
        });
    }


    public function getIncludes($item, $includes)
    {
        if (empty($includes)){
            return [];
        }
        $this->populateIncludes($item, $includes);
        // dump($this->includes);
        // return $this->includes;

        return ['included' => $this->includes];
    }


    // public static function getNotIncludedRelationships($relationsMap, $includes)
    // {
    //     collect($includes)->map(function ($item, $key) {
    //         return $item[0];
    //     })->each(function ($item, $key) use(&$relationsMap){
    //         unset($relationsMap[$item]);
    //     })->toArray();
        
    //     return $relationsMap;
    // }
}