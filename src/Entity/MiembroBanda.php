<?php

namespace App\Entity;

use App\Repository\MiembroBandaRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MiembroBandaRepository::class)]
class MiembroBanda
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'miembroBandas')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Banda $banda = null;

    #[ORM\ManyToOne(inversedBy: 'miembroBandas')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Musico $musico = null;

    #[ORM\Column(length: 100)]
    private ?string $rol_banda = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBanda(): ?Banda
    {
        return $this->banda;
    }

    public function setBanda(?Banda $banda): static
    {
        $this->banda = $banda;

        return $this;
    }

    public function getMusico(): ?Musico
    {
        return $this->musico;
    }

    public function setMusico(?Musico $musico): static
    {
        $this->musico = $musico;

        return $this;
    }

    public function getRolBanda(): ?string
    {
        return $this->rol_banda;
    }

    public function setRolBanda(string $rol_banda): static
    {
        $this->rol_banda = $rol_banda;

        return $this;
    }
}
