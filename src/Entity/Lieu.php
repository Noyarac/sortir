<?php

namespace App\Entity;

use App\Repository\LieuRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: LieuRepository::class)]
class Lieu
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getSortie"])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Veuillez choisir un nom pour ce lieu")]
    #[Assert\Length(min : 2, max: 50, minMessage: "Le nom du lieu doit faire au moins 2 caractères.",
    maxMessage: "Le nom du lieu doit faire au maximum 50 caractères.")]
    #[Groups(["getSortie"])]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Veuillez indiquer la rue de ce lieu")]
    #[Assert\Length(min : 3, max: 255, minMessage: "Au moins 3 caractères sont attendus. ",
        maxMessage: "Maximum 255 caractères autorisés.")]
    #[Groups(["getSortie"])]
    private ?string $rue = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "Merci d'indiquer la latitude")]
    #[Assert\Range(notInRangeMessage: "La latitude doit être comprise entre -90° et 90°", min: -90, max: 90)]
    #[Groups(["getSortie"])]
    private ?float $latitude = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "Merci d'indiquer la longitude")]
    #[Assert\Range(notInRangeMessage: "La longitude doit être comprise entre -180° et 180°", min: -180, max: 180)]
    #[Groups(["getSortie"])]
    private ?float $longitude = null;

    #[ORM\ManyToOne(inversedBy: 'lieux')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank(message: "Merci de sélectionner une ville.")]
    #[Groups(["getSortie"])]
    private ?Ville $ville = null;

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
        $this->nom = $nom;

        return $this;
    }

    public function getRue(): ?string
    {
        return $this->rue;
    }

    public function setRue(string $rue): static
    {
        $this->rue = $rue;

        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(float $latitude): static
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(float $longitude): static
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getVille(): ?Ville
    {
        return $this->ville;
    }

    public function setVille(?Ville $ville): static
    {
        $this->ville = $ville;

        return $this;
    }
}
