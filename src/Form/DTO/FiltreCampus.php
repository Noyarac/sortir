<?php

namespace App\Form\DTO;

class FiltreCampus
{
    public function __construct(
        private ?string $contient = null,
    )
    {}

    public function getContient(): ?string
    {
        return $this->contient;
    }

    public function setContient(?string $contient): void
    {
        $this->contient = $contient;
    }

}
