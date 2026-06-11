<?php

namespace App\Entity;

use App\Repository\GeneroRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GeneroRepository::class)]
#[ORM\Table(name: 'genero')]
class Genero
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    private ?string $nombre = null;

    public function getId(): ?int { return $this->id; }

    public function getNombre(): ?string { return $this->nombre; }

    public function setNombre(string $nombre): static
    {
        $this->nombre = $nombre;
        return $this;
    }
}
