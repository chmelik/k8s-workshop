<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Qwerty;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/qwerty', methods: ['GET'])]
class QwertyController
{
    #[Route('/{id}', methods: ['GET'])]
    public function __invoke(int $id, EntityManagerInterface $entityManager): Response
    {
        $qwerty = $entityManager->find(Qwerty::class, $id);
        if (!$qwerty) {
            throw new NotFoundHttpException();
        }

        return new JsonResponse([
            'id' => $qwerty->getId(),
            'name' => $qwerty->getName(),
        ]);
    }
}
