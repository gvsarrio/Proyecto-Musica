<?php

namespace App\Entity;

use App\Repository\MusicoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MusicoRepository::class)]
class Musico
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'user_id', nullable: false)]
    private ?Usuario $user_id = null;

    #[ORM\Column(length: 100)]
    private ?string $nombre = null;

    #[ORM\Column(nullable: true)]
    private ?int $telefono = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $biografia = null;

    #[ORM\Column(length: 255)]
    private ?string $ubicacion = null;

    #[ORM\Column]
    private ?int $anyos_experiencia = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imagen_url = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $creado_en = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $actualizado_en = null;

    #[ORM\Column]
    private ?bool $es_banda = null;

    /**
     * @var Collection<int, MiembroBanda>
     */
    #[ORM\OneToMany(targetEntity: MiembroBanda::class, mappedBy: 'musico')]
    private Collection $miembroBandas;

    public function __construct()
    {
        $this->miembroBandas = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): ?Usuario
    {
        return $this->user_id;
    }

    public function setUserId(Usuario $user_id): static
    {
        $this->user_id = $user_id;

        return $this;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): static
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getTelefono(): ?int
    {
        return $this->telefono;
    }

    public function setTelefono(?int $telefono): static
    {
        $this->telefono = $telefono;

        return $this;
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

    public function getUbicacion(): ?string
    {
        return $this->ubicacion;
    }

    public function setUbicacion(string $ubicacion): static
    {
        $this->ubicacion = $ubicacion;

        return $this;
    }

    public function getAnyosExperiencia(): ?int
    {
        return $this->anyos_experiencia;
    }

    public function setAnyosExperiencia(int $anyos_experiencia): static
    {
        $this->anyos_experiencia = $anyos_experiencia;

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

    public function getCreadoEn(): ?\DateTime
    {
        return $this->creado_en;
    }

    public function setCreadoEn(?\DateTime $creado_en): static
    {
        $this->creado_en = $creado_en;

        return $this;
    }

    public function getActualizadoEn(): ?\DateTime
    {
        return $this->actualizado_en;
    }

    public function setActualizadoEn(?\DateTime $actualizado_en): static
    {
        $this->actualizado_en = $actualizado_en;

        return $this;
    }

    public function isEsBanda(): ?bool
    {
        return $this->es_banda;
    }

    public function setEsBanda(bool $es_banda): static
    {
        $this->es_banda = $es_banda;

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
            $miembroBanda->setMusico($this);
        }

        return $this;
    }

    public function removeMiembroBanda(MiembroBanda $miembroBanda): static
    {
        if ($this->miembroBandas->removeElement($miembroBanda)) {
            // set the owning side to null (unless already changed)
            if ($miembroBanda->getMusico() === $this) {
                $miembroBanda->setMusico(null);
            }
        }

        return $this;
    }
}
