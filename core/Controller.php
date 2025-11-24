<?php

namespace App\Core;

use App\Core\Http\ResponseFactory;
use Doctrine\DBAL\Connection;

/**
 * Base Controller
 *
 * Controller cơ sở cho tất cả controllers trong ứng dụng.
 * Sử dụng Dependency Injection để inject các services cần thiết.
 *
 * Các controller con có thể:
 * - Sử dụng $this->response để tạo responses (HTML, JSON, Redirect)
 * - Sử dụng $this->db để truy cập database
 * - Override constructor để inject thêm dependencies
 *
 * @example
 * class UserController extends Controller {
 *     public function index($request) {
 *         $users = $this->db->fetchAllAssociative('SELECT * FROM users');
 *         return $this->response->view('users/index', ['users' => $users], 'Users');
 *     }
 *
 *     public function api($request) {
 *         return $this->response->json(['status' => 'ok']);
 *     }
 * }
 */
class Controller
{
    /**
     * @var ResponseFactory Response factory để tạo responses
     */
    protected ResponseFactory $response;

    /**
     * @var Connection Database connection
     */
    protected Connection $db;

    /**
     * Constructor
     *
     * Inject các dependencies cơ bản mà hầu hết controllers cần.
     * Controllers con có thể override và inject thêm dependencies.
     *
     * @param ResponseFactory $response Response factory
     * @param Connection $db Database connection
     */
    public function __construct(
        ResponseFactory $response,
        Connection $db
    ) {
        $this->response = $response;
        $this->db = $db;
    }
}