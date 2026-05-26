<?php

namespace App\Entity;

use App\Repository\MusicoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MusicoRepository::class)]
class Musico
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: Usuario::class, inversedBy: 'musico')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, unique: true)]
    private ?Usuario $usuario = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'El nombre no puede estar vacío')]
    #[Assert\Length(min: 2, minMessage: 'El nombre debe tener al menos 2 caracteres')]
    private ?string $nombre = null;

    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero(message: 'El teléfono debe ser un número positivo')]
    private ?int $telefono = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'La biografía no puede estar vacía')]
    #[Assert\Length(min: 10, minMessage: 'La biografía debe tener al menos 10 caracteres')]
    private ?string $biografia = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'La ubicación no puede estar vacía')]
    #[Assert\Length(min: 3, minMessage: 'La ubicación debe tener al menos 3 caracteres')]
    private ?string $ubicacion = null;

    #[ORM\Column(nullable: true)]
    private ?float $latitud = null;

    #[ORM\Column(nullable: true)]
    private ?float $longitud = null;

    #[ORM\Column]
    #[Assert\NotNull(message: 'Los años de experiencia no pueden estar vacíos')]
    #[Assert\PositiveOrZero(message: 'Los años de experiencia no pueden ser negativos')]
    #[Assert\Range(max: 100, maxMessage: 'Los años de experiencia no pueden superar 100')]
    private ?int $anyos_experiencia = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imagen_url = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $creado_en = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $actualizado_en = null;

    #[ORM\Column(nullable: true)]
    private ?bool $es_banda = null;

    /**
     * @var Collection<int, MiembroBanda>
     */
    #[ORM\OneToMany(targetEntity: MiembroBanda::class, mappedBy: 'musico')]
    private Collection $miembroBandas;

    /**
     * @var Collection<int, InstrumentoMusico>
     */
    #[ORM\OneToMany(targetEntity: InstrumentoMusico::class, mappedBy: 'musico', cascade: ['remove'])]
    private Collection $instrumentoMusicos;

    public function __construct()
    {
        $this->miembroBandas = new ArrayCollection();
        $this->instrumentoMusicos = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }
    public function getUsuario(): ?Usuario { return $this->usuario; }
    public function setUsuario(Usuario $usuario): static { $this->usuario = $usuario; return $this; }
    public function getNombre(): ?string { return $this->nombre; }
    public function setNombre(string $nombre): static { $this->nombre = $nombre; return $this; }
    public function getTelefono(): ?int { return $this->telefono; }
    public function setTelefono(?int $telefono): static { $this->telefono = $telefono; return $this; }
    public function getBiografia(): ?string { return $this->biografia; }
    public function setBiografia(string $biografia): static { $this->biografia = $biografia; return $this; }
    public function getUbicacion(): ?string { return $this->ubicacion; }
    public function setUbicacion(string $ubicacion): static { $this->ubicacion = $ubicacion; return $this; }
    public function getLatitud(): ?float { return $this->latitud; }
    public function setLatitud(?float $latitud): static { $this->latitud = $latitud; return $this; }
    public function getLongitud(): ?float { return $this->longitud; }
    public function setLongitud(?float $longitud): static { $this->longitud = $longitud; return $this; }
    public function getAnyosExperiencia(): ?int { return $this->anyos_experiencia; }
    public function setAnyosExperiencia(int $anyos_experiencia): static { $this->anyos_experiencia = $anyos_experiencia; return $this; }
    public function getImagenUrl(): ?string { return $this->imagen_url; }
    public function setImagenUrl(?string $imagen_url): static { $this->imagen_url = $imagen_url; return $this; }
    public function getCreadoEn(): ?\DateTime { return $this->creado_en; }
    public function setCreadoEn(?\DateTime $creado_en): static { $this->creado_en = $creado_en; return $this; }
    public function getActualizadoEn(): ?\DateTime { return $this->actualizado_en; }
    public function setActualizadoEn(?\DateTime $actualizado_en): static { $this->actualizado_en = $actualizado_en; return $this; }
    public function isEsBanda(): ?bool { return $this->es_banda; }
    public function setEsBanda(bool $es_banda): static { $this->es_banda = $es_banda; return $this; }

    public function getMiembroBandas(): Collection { return $this->miembroBandas; }

    /**
     * @return Collection<int, InstrumentoMusico>
     */
    public function getInstrumentoMusicos(): Collection
    {
        return $this->instrumentoMusicos;
    }

    /**
     * @return Collection<int, Instrumento>
     */
    public function getInstrumentos(): Collection
    {
        return new ArrayCollection(
            $this->instrumentoMusicos->map(fn(InstrumentoMusico $im) => $im->getInstrumento())->getValues()
        );
    }

    public function getMiembroBandasAceptadas(): Collection
    {
        return $this->miembroBandas->filter(fn(MiembroBanda $mb) => $mb->getEstado() === 'aceptado');
    }
}