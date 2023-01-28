<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/', methods: ['GET'])]
class DefaultController
{
    public function __invoke(Request $request): Response
    {
        return new JsonResponse([
            'message' => sprintf('qwerty: %s', $request->query->get('qwerty')),
        ]);
    }
}
