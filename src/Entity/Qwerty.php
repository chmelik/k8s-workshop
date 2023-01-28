<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'qwerty')]
class Qwerty
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'integer')]
        private readonly int $id,

        #[ORM\Column(length: 255)]
        private string $name
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
