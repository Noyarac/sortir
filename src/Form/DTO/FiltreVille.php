<?php

namespace App\Form\DTO;

class FiltreVille
{
    public function __construct(
        private ?string $nomContient = null,
    )
    {}

    public function getNomContient(): ?string
    {
        return $this->nomContient;
    }


}
