<?php

namespace App\Twig\Runtime;

use App\Entity\Usuario;
use App\Repository\MensajeRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Extension\RuntimeExtensionInterface;

class MensajesExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(private MensajeRepository $mensajeRepository, private Security $security)
    {
    }

    public function contarMensajesNoLeidos(): int
    {
        $usuario = $this->security->getUser();

        if (!$usuario instanceof Usuario) {
            return 0;
        }

        if ($usuario->getMusico() === null) {
            return 0;
        }

        return $this->mensajeRepository
            ->contarNoLeidosDeUsuario($usuario);
    }
}