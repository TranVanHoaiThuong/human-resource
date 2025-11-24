<?php

namespace App\Controllers;

use App\Core\Controller;

class AuthController extends Controller {
    public function index() {
        return $this->response->view('auth/login', [], 'Login');
    }
}