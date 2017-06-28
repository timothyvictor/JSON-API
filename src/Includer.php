<?php

namespace TimothyVictor\JsonAPI;

use Illuminate\Support\Collection;

class Includer
{
    private $serialize;
    // private $requestedIncludes = [];
    private $includes = [];

    public function __construct(Serializer $serialize)
    {
        $this->serialize = $serialize;
    }

    private function includeItem(Transformer $item, $includes, $parameters)
    {
        if (isset($item)) {
            $this->includes =  array_unique(array_merge($this->includes, [$this->serialize->serializeResourceObject($item, $parameters)]), SORT_REGULAR);
        }
        if (!empty($includes)) {
            return $this->includeResources($item, $includes);
        }
        return;
    }

    private function includeCollection($items, $includes, $parameters)
    {
        $type = $items->first()->transformType();
        $items_to_include = $items->map(function ($item, $key) use ($parameters) {
            return $this->serialize->serializeResourceObject($item, $parameters);
        })->toArray();
        if (!empty($includes)) {
            $items->each(function ($item) use ($includes, $parameters) {
                $this->includeResources($item, $includes, $parameters);
            });
        }
        return $this->includes = array_unique(array_merge($this->includes, $items_to_include), SORT_REGULAR);
    }

    private function serializeCorrectIncludeType($item, $includes, $parameters)
    {
        return ($item instanceof Collection) ? $this->includeCollection($item, $includes, $parameters) : $this->includeItem($item, $includes, $parameters);
    }

    private function includeResources($item, $includes, $parameters)
    {
        $relationMap = $item->getRelationMap();
        $include = array_shift($includes);
        if (array_key_exists($include, $relationMap)) {
            $relation = $item->{$relationMap[$include]}();
            return $this->serializeCorrectIncludeType($relation, $includes, $parameters);
        }
        throw new InvalidIncludeException("{$include} is not a valid include for {$item->transformType()}");
    }
    private function populateIncludes($item, $parameters)
    {
        collect($parameters['includes'])->each(function ($include, $key) use ($item, $parameters) {
            $include_collection = Helper::dot_to_array($include);
            $this->includeResources($item, $include_collection, $parameters);
        });
    }

    public function getIncludes($item, $parameters) : array
    {
        if (empty($parameters['includes'])) {
            return [];
        }
        $this->populateIncludes($item, $parameters);

        return ['included' => $this->includes];
    }
}
