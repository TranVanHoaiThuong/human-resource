<?php

namespace App\Core\Auth;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\RedirectResponse;

class AuthMiddleWare implements MiddlewareInterface
{
    protected Auth $auth;
    protected string $loginUrl;

    public function __construct(Auth $auth, string $loginUrl = '/login')
    {
        $this->auth = $auth;
        $this->loginUrl = $loginUrl;
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        // Check if user is authenticated
        if ($this->auth->guest()) {
            // Store intended URL for redirect after login
            $intendedUrl = (string) $request->getUri();
            $_SESSION['url.intended'] = $intendedUrl;
            
            return new RedirectResponse($this->loginUrl);
        }

        // Add user to request attributes for easy access in controllers
        $request = $request->withAttribute('user', $this->auth->user());
        $request = $request->withAttribute('auth', $this->auth);

        return $handler->handle($request);
    }
}