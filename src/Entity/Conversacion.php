<?php

namespace App\Entity;

use App\Repository\ConversacionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConversacionRepository::class)]
class Conversacion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'conversacionesComoUsuarioUno')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Usuario $usuarioUno = null;

    #[ORM\ManyToOne(inversedBy: 'conversacionesComoUsuarioDos')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Usuario $usuarioDos = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $fechaCreacion = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $fechaUltimoMensaje = null;

    /**
     * @var Collection<int, Mensaje>
     */
    #[ORM\OneToMany(targetEntity: Mensaje::class, mappedBy: 'conversacion')]
    private Collection $mensajes;

    public function __construct()
    {
        $this->mensajes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsuarioUno(): ?Usuario
    {
        return $this->usuarioUno;
    }

    public function setUsuarioUno(?Usuario $usuarioUno): static
    {
        $this->usuarioUno = $usuarioUno;

        return $this;
    }

    public function getUsuarioDos(): ?Usuario
    {
        return $this->usuarioDos;
    }

    public function setUsuarioDos(?Usuario $usuarioDos): static
    {
        $this->usuarioDos = $usuarioDos;

        return $this;
    }

    public function getFechaCreacion(): ?\DateTimeImmutable
    {
        return $this->fechaCreacion;
    }

    public function setFechaCreacion(\DateTimeImmutable $fechaCreacion): static
    {
        $this->fechaCreacion = $fechaCreacion;

        return $this;
    }

    public function getFechaUltimoMensaje(): ?\DateTimeImmutable
    {
        return $this->fechaUltimoMensaje;
    }

    public function setFechaUltimoMensaje(?\DateTimeImmutable $fechaUltimoMensaje): static
    {
        $this->fechaUltimoMensaje = $fechaUltimoMensaje;

        return $this;
    }

    /**
     * @return Collection<int, Mensaje>
     */
    public function getMensajes(): Collection
    {
        return $this->mensajes;
    }

    public function addMensaje(Mensaje $mensaje): static
    {
        if (!$this->mensajes->contains($mensaje)) {
            $this->mensajes->add($mensaje);
            $mensaje->setConversacion($this);
        }

        return $this;
    }

    public function removeMensaje(Mensaje $mensaje): static
    {
        if ($this->mensajes->removeElement($mensaje)) {
            // set the owning side to null (unless already changed)
            if ($mensaje->getConversacion() === $this) {
                $mensaje->setConversacion(null);
            }
        }

        return $this;
    }
}
