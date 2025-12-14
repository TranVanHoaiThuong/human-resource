<?php

namespace App\Core\Auth;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\RedirectResponse;

/** Middleware kiểm tra authentication */
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
        if ($this->auth->guest()) {
            $intendedUrl = (string) $request->getUri();
            $_SESSION['url.intended'] = $intendedUrl;

            return new RedirectResponse($this->loginUrl);
        }

        $request = $request->withAttribute('user', $this->auth->user());
        $request = $request->withAttribute('auth', $this->auth);

        return $handler->handle($request);
    }
}