<?php

namespace App\Entity;

use App\Repository\InstrumentoSistemaRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InstrumentoSistemaRepository::class)]
#[ORM\Table(name: 'instrumento_sistema')]
class InstrumentoSistema
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $nombre = null;

    public function getId(): ?int { return $this->id; }

    public function getNombre(): ?string { return $this->nombre; }

    public function setNombre(string $nombre): static
    {
        $this->nombre = $nombre;
        return $this;
    }
}
