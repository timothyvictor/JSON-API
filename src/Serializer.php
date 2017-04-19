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
        return array_merge($this->serializeId($item), $this->serializeType($item), $this->serializeAttributes($item));
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
}