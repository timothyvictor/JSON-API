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

    public function assembleCollection(Collection $collection) : array
    {
        return array_merge($this->serialize->getApiMember(), [
            'data' => $collection->map(function (Transformer $item) {
                return $this->serialize->serializeResourceObject($item);
            }),
        ], $this->serialize->serializeResourceLink($collection->first()));
    }

    public function assembleResource(Transformer $item, $includes = []) : array
    {
        return array_merge($this->serialize->getApiMember(), ['data' => $this->serialize->serializeResourceObject($item)], $this->serialize->serializeResourceLink($item), $this->include->getIncludes($item, $includes));
    }

}