<?php

namespace App\Core\View;

use League\Plates\Engine;
use stdClass;

/** View Renderer sử dụng Plates template engine */
class ViewRenderer implements ViewInterface
{
    protected Engine $engine;
    protected stdClass $config;
    protected string $title = '';
    protected array $data = [];

    public function __construct(Engine $engine, stdClass $config)
    {
        $this->engine = $engine;
        $this->config = $config;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function with(string $key, mixed $value): self
    {
        $this->data[$key] = $value;
        return $this;
    }

    public function withData(array $data): self
    {
        $this->data = array_merge($this->data, $data);
        return $this;
    }

    public function render(string $template, array $data = []): string
    {
        $viewData = array_merge($this->data, $data);
        $templateData = $this->prepareTemplateData($viewData);
        return $this->engine->render($template, $templateData);
    }

    protected function prepareTemplateData(array $data): array
    {
        return [
            'title' => $this->formatTitle(),
            'wwwroot' => $this->config->wwwroot,
        ] + $data;
    }

    protected function formatTitle(): string
    {
        if (empty($this->title)) {
            return 'HRM';
        }
        return "{$this->title} | HRM";
    }

    /** Reset view state */
    public function reset(): self
    {
        $this->title = '';
        $this->data = [];
        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getData(): array
    {
        return $this->data;
    }
}

