<?php

namespace TimothyVictor\JsonAPI;

use Illuminate\Support\Collection;

class Assembler
{
    private $serialize;
    private $include;

    public function __construct(Serializer $serialize, Includer $include)
    {
        $this->serialize = $serialize;
        $this->include = $include;
    }

    private function sort($sortArray, $collection)
    {
        if (count($sortArray) > 1) {
            return;
        }

        return $collection->sortBy("attributes.{$sortArray[0]}");
    }

    public function assembleCollection(Collection $collection, array $parameters) : array
    {
        $data = $collection->map(function (Transformer $item) use ($parameters) {
            return $this->serialize->serializeResourceObject($item, $parameters);
        }
        );
        if (!empty($parameters['sort'])) {
            $data = $this->sort($parameters['sort'], $data);
        }

        return array_merge($this->serialize->getApiMember(), $this->serialize->topLevelLinksObject($collection, $parameters), ['data' => $data]);
    }

    public function assembleResource(Transformer $item, array $parameters) : array
    {
        return array_merge(
            $this->serialize->getApiMember(),
            ['data' => $this->serialize->serializeResourceObject($item, $parameters)],
            $this->include->getIncludes($item, $parameters),
            $this->serialize->topLevelLinksObject($item, $parameters)
        );
    }
}
