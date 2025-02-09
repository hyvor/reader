<?php

namespace App\Api\App\Controller;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
class MainController
{
    #[Route('/main', methods: ['GET'])]
    public function main(): Response
    {
        return new Response('Hello, world!');
    }

}