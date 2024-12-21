<?php
declare(strict_types=1);

namespace App\Endpoint\Web;

use Spiral\Router\Annotation\Route;

final class AppController
{

    #[Route('/', 'init', methods: 'GET')]
    public function init(): string
    {
        return 'Hello, World!';
    }

}
