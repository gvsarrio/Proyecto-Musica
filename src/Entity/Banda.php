<?php

namespace App\Entity;

use App\Repository\BandaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BandaRepository::class)]
class Banda
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $biografia = null;

    #[ORM\Column(length: 200, nullable: true)]
    private ?string $generos = null;

    #[ORM\Column]
    private ?int $anyo_formacion = null;

    #[ORM\Column(length: 255)]
    private ?string $ubicacion = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imagen_url = null;

    /**
     * @var Collection<int, MiembroBanda>
     */
    #[ORM\OneToMany(targetEntity: MiembroBanda::class, mappedBy: 'banda')]
    private Collection $miembroBandas;

    public function __construct()
    {
        $this->miembroBandas = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBiografia(): ?string
    {
        return $this->biografia;
    }

    public function setBiografia(string $biografia): static
    {
        $this->biografia = $biografia;

        return $this;
    }

    public function getGeneros(): ?string
    {
        return $this->generos;
    }

    public function setGeneros(?string $generos): static
    {
        $this->generos = $generos;

        return $this;
    }

    public function getAnyoFormacion(): ?int
    {
        return $this->anyo_formacion;
    }

    public function setAnyoFormacion(int $anyo_formacion): static
    {
        $this->anyo_formacion = $anyo_formacion;

        return $this;
    }

    public function getUbicacion(): ?string
    {
        return $this->ubicacion;
    }

    public function setUbicacion(string $ubicacion): static
    {
        $this->ubicacion = $ubicacion;

        return $this;
    }

    public function getImagenUrl(): ?string
    {
        return $this->imagen_url;
    }

    public function setImagenUrl(?string $imagen_url): static
    {
        $this->imagen_url = $imagen_url;

        return $this;
    }

    /**
     * @return Collection<int, MiembroBanda>
     */
    public function getMiembroBandas(): Collection
    {
        return $this->miembroBandas;
    }

    public function addMiembroBanda(MiembroBanda $miembroBanda): static
    {
        if (!$this->miembroBandas->contains($miembroBanda)) {
            $this->miembroBandas->add($miembroBanda);
            $miembroBanda->setBanda($this);
        }

        return $this;
    }

    public function removeMiembroBanda(MiembroBanda $miembroBanda): static
    {
        if ($this->miembroBandas->removeElement($miembroBanda)) {
            // set the owning side to null (unless already changed)
            if ($miembroBanda->getBanda() === $this) {
                $miembroBanda->setBanda(null);
            }
        }

        return $this;
    }
}
