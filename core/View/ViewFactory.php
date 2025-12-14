<?php

namespace App\Core\View;

use League\Plates\Engine;
use stdClass;

/** Factory class để tạo ViewRenderer instances */
class ViewFactory
{
    protected Engine $engine;
    protected stdClass $config;

    public function __construct(Engine $engine, stdClass $config)
    {
        $this->engine = $engine;
        $this->config = $config;
    }

    /** Tạo ViewRenderer instance mới */
    public function make(): ViewRenderer
    {
        return new ViewRenderer($this->engine, $this->config);
    }

    /** Tạo ViewRenderer với title đã set sẵn */
    public function makeWithTitle(string $title): ViewRenderer
    {
        return $this->make()->setTitle($title);
    }

    /** Tạo ViewRenderer với data đã set sẵn */
    public function makeWithData(array $data): ViewRenderer
    {
        return $this->make()->withData($data);
    }
}

