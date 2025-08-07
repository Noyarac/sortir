<?php

namespace App\Form\DTO;

use App\Entity\Campus;
use DateTimeImmutable;

class FiltreSortie
{
    public function __construct(
        private ?Campus $campus = null,
        private ?string $contient = null,
        private ?DateTimeImmutable $debut = null,
        private ?DateTimeImmutable $fin = null,
        private ?bool $organisateur = null,
        private ?bool $participant = null,
        private ?bool $nonParticipant = null,
        private ?bool $terminees = null
    )
    {}


    /**
     * Get the value of campus
     */ 
    public function getCampus()
    {
        return $this->campus;
    }

    /**
     * Set the value of campus
     *
     * @return  self
     */ 
    public function setCampus($campus)
    {
        $this->campus = $campus;

        return $this;
    }

    /**
     * Get the value of contient
     */ 
    public function getContient()
    {
        return $this->contient;
    }

    /**
     * Set the value of contient
     *
     * @return  self
     */ 
    public function setContient($contient)
    {
        $this->contient = $contient;

        return $this;
    }

    /**
     * Get the value of debut
     */ 
    public function getDebut()
    {
        return $this->debut;
    }

    /**
     * Set the value of debut
     *
     * @return  self
     */ 
    public function setDebut($debut)
    {
        $this->debut = $debut;

        return $this;
    }

    /**
     * Get the value of fin
     */ 
    public function getFin()
    {
        return $this->fin;
    }

    /**
     * Set the value of fin
     *
     * @return  self
     */ 
    public function setFin($fin)
    {
        $this->fin = $fin;

        return $this;
    }

    /**
     * Get the value of organisateur
     */ 
    public function getOrganisateur()
    {
        return $this->organisateur;
    }

    /**
     * Set the value of organisateur
     *
     * @return  self
     */ 
    public function setOrganisateur($organisateur)
    {
        $this->organisateur = $organisateur;

        return $this;
    }

    /**
     * Get the value of participant
     */ 
    public function getParticipant()
    {
        return $this->participant;
    }

    /**
     * Set the value of participant
     *
     * @return  self
     */ 
    public function setParticipant($participant)
    {
        $this->participant = $participant;

        return $this;
    }

    /**
     * Get the value of nonParticipant
     */ 
    public function getNonParticipant()
    {
        return $this->nonParticipant;
    }

    /**
     * Set the value of nonParticipant
     *
     * @return  self
     */ 
    public function setNonParticipant($nonParticipant)
    {
        $this->nonParticipant = $nonParticipant;

        return $this;
    }

    /**
     * Get the value of terminees
     */ 
    public function getTerminees()
    {
        return $this->terminees;
    }

    /**
     * Set the value of terminees
     *
     * @return  self
     */ 
    public function setTerminees($terminees)
    {
        $this->terminees = $terminees;

        return $this;
    }
}