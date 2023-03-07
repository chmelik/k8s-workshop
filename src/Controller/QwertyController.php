<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Qwerty;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/qwerty')]
class QwertyController
{
    #[Route('/{id}', methods: ['GET'])]
    public function getItem(int $id, EntityManagerInterface $entityManager): Response
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

    #[Route(methods: ['POST'])]
    public function createItem(Request $request, EntityManagerInterface $entityManager): Response
    {
        $qwerty = new Qwerty(
            name: sprintf('Name %s', $request->query->name ?? '[none]')
        );

        $entityManager->persist($qwerty);
        $entityManager->flush();

        return new JsonResponse([
            'id' => $qwerty->getId(),
            'name' => $qwerty->getName(),
        ]);
    }
}
