<?php

namespace TimothyVictor\JsonAPI;

interface Transformer
{
    public function transformAttributes() : array;

    public function transformId();

    public function transformType() : string;

    public function transformSelfLink() : string;

    public function getRelationMap();
}
