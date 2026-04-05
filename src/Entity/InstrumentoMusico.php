<?php

namespace App\Entity;

use App\Repository\InstrumentoMusicoRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InstrumentoMusicoRepository::class)]
class InstrumentoMusico
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'musico_id', nullable: false)]
    private ?Musico $musico = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'instrumento_id', nullable: false)]
    private ?Instrumento $instrumento = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getInstrumento(): ?Instrumento
    {
        return $this->instrumento;
    }

    public function setInstrumento(?Instrumento $instrumento): static
    {
        $this->instrumento = $instrumento;

        return $this;
    }
}
