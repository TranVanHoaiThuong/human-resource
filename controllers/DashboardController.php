<?php

namespace App\Controllers;

use App\Core\Controller;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class DashboardController extends Controller
{
    public function index(ServerRequestInterface $request): ResponseInterface
    {
        // Lấy thống kê
        $stats = [
            'total_employees' => 100,
            'present_today' => 98,
            'on_leave' => 2,
        ];
        
        return $this->response->view('dashboard/index', [
            'stats' => $stats,
            'active_menu' => 'dashboard',
            'user' => $_SESSION['user'] ?? null
        ], 'Dashboard');
    }
}