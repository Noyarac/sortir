<?php

namespace App\Entity;

use App\Repository\VilleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: VilleRepository::class)]
class Ville
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message:"Merci d'indiquer le nom de la ville")]
    #[Assert\Length(min:2, max: 50, minMessage: "Un nom de ville doit contenir au moins 2 caractères",
        maxMessage: "Maximum 50 caractères autorisés")]
    private ?string $nom = null;

    #[ORM\Column(length: 5)]
    #[Assert\NotBlank(message:"Merci d'indiquer le code postal de la ville")]
    #[Assert\Regex(pattern: "/^\d{5}$/", message: "Le code postal doit contenir 5 chiffres")]
    private ?string $codePostal = null;

    #[ORM\OneToMany(targetEntity: Lieu::class, mappedBy: 'ville', orphanRemoval: true)]
    private Collection $lieux;

    public function __construct()
    {
        $this->lieux = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $nomFormate = ucfirst(strtolower(trim($nom)));
        $this->nom = $nomFormate;

        return $this;
    }

    public function getCodePostal(): ?string
    {
        return $this->codePostal;
    }

    public function setCodePostal(string $codePostal): static
    {
        $this->codePostal = $codePostal;

        return $this;
    }

    public function getLieux(): Collection
    {
        return $this->lieux;
    }

    public function addLieu(Lieu $lieu): static
    {
        if (!$this->lieux->contains($lieu)) {
            $this->lieux->add($lieu);
            $lieu->setVille($this);
        }

        return $this;
    }

    public function removeLieu(Lieu $lieu): static
    {
        if ($this->lieux->removeElement($lieu)) {
            // set the owning side to null (unless already changed)
            if ($lieu->getVille() === $this) {
                $lieu->setVille(null);
            }
        }

        return $this;
    }


}
