<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth\Auth;
use Psr\Http\Message\ServerRequestInterface;

class AuthController extends Controller
{
    protected Auth $auth;

    public function __construct(
        \App\Core\Http\ResponseFactory $response,
        \Doctrine\DBAL\Connection $db,
        \App\Core\Container $container,
        Auth $auth
    ) {
        parent::__construct($response, $db, $container);
        $this->auth = $auth;
    }

    /**
     * Show login form
     */
    public function showLogin()
    {
        return $this->response->view('auth/login', [
            'error' => flash('error'),
            'username' => flash('old.username'),
        ], 'Login');
    }

    /**
     * Handle login
     */
    public function login(ServerRequestInterface $request)
    {
        $body = $request->getParsedBody();
        $username = trim($body['username'] ?? '');
        $password = $body['password'] ?? '';
        $remember = isset($body['remember']);

        if (empty($username) || empty($password)) {
            flash('error', 'Vui lòng nhập tài khoản và mật khẩu');
            flash('old.username', $username);
            return $this->response->redirect('/login');
        }

        if (!$this->auth->attempt($username, $password, $remember)) {
            flash('error', 'Tài khoản hoặc mật khẩu không đúng');
            flash('old.username', $username);
            return $this->response->redirect('/login');
        }

        $intended = $_SESSION['url.intended'] ?? '/dashboard';
        unset($_SESSION['url.intended']);
        return $this->response->redirect($intended);
    }

    /**
     * Handle logout
     */
    public function logout()
    {
        $this->auth->logout();
        return $this->response->redirect('/login');
    }
}