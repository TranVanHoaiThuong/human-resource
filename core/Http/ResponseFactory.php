<?php

namespace App\Core\Http;

use Laminas\Diactoros\Response;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use App\Core\View\ViewFactory;

/**
 * Response Factory
 * 
 * Factory class để tạo các loại PSR-7 Response.
 * Giúp Controller dễ dàng trả về HTML, JSON, Redirect responses.
 * 
 * @example
 * // HTML Response
 * return $response->view('home/index', ['user' => $user], 'Home Page');
 * 
 * // JSON Response
 * return $response->json(['status' => 'success', 'data' => $data]);
 * 
 * // Redirect Response
 * return $response->redirect('/dashboard');
 */
class ResponseFactory
{
    /**
     * @var ViewFactory View factory instance
     */
    protected ViewFactory $viewFactory;

    /**
     * Constructor
     * 
     * @param ViewFactory $viewFactory View factory
     */
    public function __construct(ViewFactory $viewFactory)
    {
        $this->viewFactory = $viewFactory;
    }

    /**
     * Tạo HTML response từ view template
     * 
     * @param string $template Template name (vd: 'home/index')
     * @param array $data Data truyền vào view
     * @param string $title Page title (optional)
     * @param int $status HTTP status code
     * @return Response
     */
    public function view(
        string $template,
        array $data = [],
        string $title = '',
        int $status = 200
    ): Response {
        $view = $this->viewFactory->make();
        
        if (!empty($title)) {
            $view->setTitle($title);
        }
        
        $html = $view->render($template, $data);
        
        $response = new Response();
        $response->getBody()->write($html);
        
        return $response->withStatus($status);
    }

    /**
     * Tạo JSON response
     * 
     * @param mixed $data Data để encode thành JSON
     * @param int $status HTTP status code
     * @param int $encodingOptions JSON encoding options
     * @return JsonResponse
     */
    public function json(
        mixed $data,
        int $status = 200,
        int $encodingOptions = JsonResponse::DEFAULT_JSON_FLAGS
    ): JsonResponse {
        return new JsonResponse($data, $status, [], $encodingOptions);
    }

    /**
     * Tạo JSON success response
     * 
     * @param mixed $data Data
     * @param string $message Success message
     * @param int $status HTTP status code
     * @return JsonResponse
     */
    public function success(
        mixed $data = null,
        string $message = 'Success',
        int $status = 200
    ): JsonResponse {
        return $this->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    /**
     * Tạo JSON error response
     * 
     * @param string $message Error message
     * @param mixed $errors Error details (optional)
     * @param int $status HTTP status code
     * @return JsonResponse
     */
    public function error(
        string $message = 'Error',
        mixed $errors = null,
        int $status = 400
    ): JsonResponse {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return $this->json($response, $status);
    }

    /**
     * Tạo redirect response
     * 
     * @param string $uri URI để redirect
     * @param int $status HTTP status code (302 = temporary, 301 = permanent)
     * @return RedirectResponse
     */
    public function redirect(string $uri, int $status = 302): RedirectResponse
    {
        return new RedirectResponse($uri, $status);
    }

    /**
     * Tạo plain text response
     * 
     * @param string $content Text content
     * @param int $status HTTP status code
     * @return Response
     */
    public function text(string $content, int $status = 200): Response
    {
        $response = new Response();
        $response->getBody()->write($content);
        
        return $response
            ->withStatus($status)
            ->withHeader('Content-Type', 'text/plain');
    }
}

