<?php

namespace App\Core\View;

use League\Plates\Engine;
use stdClass;

/**
 * View Renderer
 * 
 * Service chịu trách nhiệm render views sử dụng Plates template engine.
 * Tách biệt logic rendering khỏi Controller.
 * 
 * @example
 * $view = new ViewRenderer($engine, $config);
 * $html = $view->setTitle('Home Page')
 *              ->with('user', $user)
 *              ->render('home/index');
 */
class ViewRenderer implements ViewInterface
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
     * @var string Page title
     */
    protected string $title = '';

    /**
     * @var array Dữ liệu truyền vào view
     */
    protected array $data = [];

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
     * {@inheritDoc}
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function with(string $key, mixed $value): self
    {
        $this->data[$key] = $value;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function withData(array $data): self
    {
        $this->data = array_merge($this->data, $data);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function render(string $template, array $data = []): string
    {
        // Merge data
        $viewData = array_merge($this->data, $data);

        // Prepare template data với title và config
        $templateData = $this->prepareTemplateData($viewData);

        // Render template
        return $this->engine->render($template, $templateData);
    }

    /**
     * Chuẩn bị data cho template
     * Thêm các biến global như title, wwwroot
     * 
     * @param array $data User data
     * @return array Template data
     */
    protected function prepareTemplateData(array $data): array
    {
        return [
            'title' => $this->formatTitle(),
            'wwwroot' => $this->config->wwwroot,
        ] + $data;
    }

    /**
     * Format page title
     * 
     * @return string Formatted title
     */
    protected function formatTitle(): string
    {
        if (empty($this->title)) {
            return 'HRM';
        }

        return "{$this->title} | HRM";
    }

    /**
     * Reset view state (title và data)
     * Hữu ích khi muốn tái sử dụng ViewRenderer
     * 
     * @return self
     */
    public function reset(): self
    {
        $this->title = '';
        $this->data = [];
        return $this;
    }

    /**
     * Get current title
     * 
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Get current data
     * 
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }
}

