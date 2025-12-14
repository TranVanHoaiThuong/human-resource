<?php

namespace App\Core;

use App\Core\Http\ResponseFactory;
use Doctrine\DBAL\Connection;

/** Base Controller với DI support */
class Controller
{
    public function __construct(
        protected ResponseFactory $response,
        protected Connection $db,
        protected Container $container
    ) {
        $this->response = $response;
        $this->db = $db;
        $this->container = $container;
    }
}