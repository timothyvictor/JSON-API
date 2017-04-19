<?php

namespace TimothyVictor\JsonAPI;

interface Transformer
{
    public function transformAttributes();

    public function transformId();

    public function transformType();

    public function transfromSelfLink()
;}
