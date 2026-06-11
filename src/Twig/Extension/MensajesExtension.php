<?php

namespace App\Twig\Extension;

use App\Twig\Runtime\MensajesExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class MensajesExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'mensajes_no_leidos',
                [MensajesExtensionRuntime::class, 'contarMensajesNoLeidos']
            ),
        ];
    }
}