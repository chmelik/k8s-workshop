<?php

declare(strict_types=1);

namespace App\Controller;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/redis')]
class RedisController
{
    #[Route('/{key}', methods: ['GET'])]
    public function getItem(string $key, CacheItemPoolInterface $someCache): Response
    {
        $data = $someCache->getItem($key);
        if (!$data->isHit()) {
            throw new NotFoundHttpException();
        }

        return new JsonResponse($data->get());
    }

    #[Route(methods: ['POST'])]
    public function createItem(Request $request, CacheItemPoolInterface $someCache): Response
    {
        $item = $someCache->getItem($request->query->get('key'));
        $item->expiresAfter(60);
        $data = [
            'name' => $request->query->get('name'),
        ];
        $someCache->save($item->set($data));

        return new JsonResponse($data);
    }
}
