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

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $rol_banda = null;

    #[ORM\Column(length: 20)]
    private string $estado = 'pendiente';

    #[ORM\Column]
    private bool $es_administrador = false;

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

    public function setRolBanda(?string $rol_banda): static
    {
        $this->rol_banda = $rol_banda;

        return $this;
    }

    public function getEstado(): string
    {
        return $this->estado;
    }

    public function setEstado(string $estado): static
    {
        $this->estado = $estado;

        return $this;
    }

    public function isEsAdministrador(): bool
    {
        return $this->es_administrador;
    }

    public function setEsAdministrador(bool $es_administrador): static
    {
        $this->es_administrador = $es_administrador;

        return $this;
    }
}
