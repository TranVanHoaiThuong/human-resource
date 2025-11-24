<?php

namespace App\Core\View;

use League\Plates\Engine;
use stdClass;

/**
 * View Factory
 * 
 * Factory class để tạo ViewRenderer instances.
 * Giúp tạo view renderer một cách dễ dàng và nhất quán.
 * 
 * @example
 * $factory = new ViewFactory($engine, $config);
 * $view = $factory->make();
 * $view->setTitle('Home')->render('home/index');
 */
class ViewFactory
{
    /**
     * @var Engine Plates template engine
     */
    protected Engine $engine;

    /**
     * @var stdClass Application config
     */
    protected stdClass $config;

    /**
     * Constructor
     * 
     * @param Engine $engine Plates template engine
     * @param stdClass $config Application config
     */
    public function __construct(Engine $engine, stdClass $config)
    {
        $this->engine = $engine;
        $this->config = $config;
    }

    /**
     * Tạo ViewRenderer instance mới
     * 
     * @return ViewRenderer
     */
    public function make(): ViewRenderer
    {
        return new ViewRenderer($this->engine, $this->config);
    }

    /**
     * Tạo ViewRenderer với title đã set sẵn
     * 
     * @param string $title Page title
     * @return ViewRenderer
     */
    public function makeWithTitle(string $title): ViewRenderer
    {
        return $this->make()->setTitle($title);
    }

    /**
     * Tạo ViewRenderer với data đã set sẵn
     * 
     * @param array $data View data
     * @return ViewRenderer
     */
    public function makeWithData(array $data): ViewRenderer
    {
        return $this->make()->withData($data);
    }
}

