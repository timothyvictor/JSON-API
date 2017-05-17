<?php

namespace TimothyVictor\JsonAPI;

use Illuminate\Support\Collection;

Class Includer
{
    private $serialize;
    // private $requestedIncludes = [];
    private $includes = [];

    public function __construct(Serializer $serialize)
    {
        $this->serialize = $serialize;
    }

    private function includeItem(Transformer $item, $includes)
    {
        // $type = $item->transformType();
        if(isset($item)){
            $this->includes =  array_unique(array_merge($this->includes, [$this->serialize->serializeResourceObject($item)]), SORT_REGULAR);
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
            return $this->serialize->serializeResourceObject($item);
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
        collect($includes)->each(function($include, $key) use($item){
            $include_collection = Helper::dot_to_array($include);
            $this->includeResources($item, $include_collection);
        });
    }

    public function getIncludes($item, $includes) : array
    {
        if (empty($includes)){
            return [];
        }
        $this->populateIncludes($item, $includes);

        return ['included' => $this->includes];
    }

}
