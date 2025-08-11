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

    public function setNomContient(?string $nomContient): void
    {
        $this->nomContient = $nomContient;
    }




}
