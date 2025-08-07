<?php

namespace App\Entity;

use App\Repository\SortieRepository;
use App\Validator\DatesDebutEtLimiteInscription;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SortieRepository::class)]
#[DatesDebutEtLimiteInscription]
class Sortie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Merci d'indiquer un nom pour cette sortie!" )]
    #[Assert\Length(min: 3, max: 255, minMessage: "Merci de choisir un nom contenant au moins 3 caractères.",
    maxMessage: "Maximum 255 caractères autorisés")]
    private ?string $nom = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "La date limite d'inscription est obligatoire")]
    #[Assert\GreaterThan('today', message: "Il faut laisser le temps aux partcipants de s'inscrire! La date limite d'inscription doit au moins être fixée à demain")]
    private ?\DateTimeImmutable $dateLimiteInscription = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "Merci d'indiquer le nombre maximum de participants")]
    #[Assert\Range(notInRangeMessage: "Le nombre maximum d'inscrits doit être compris entre 5 et 1000.", min: 3, max: 100)]
    private ?int $nbInscriptionMax = null;

    #[ORM\Column(length: 1500)] //En cas d'annulation, le motif sera ajouté aux infos de la sortie
    #[Assert\NotBlank(message : "N'oubliez pas de compléter la description. Elles est essentielle pour donner envie aux personnes de s'incrire!")]
    #[Assert\Length(min: 5, max:950, minMessage: "C'est un peu court, au moins 5 caractères requis",
    maxMessage: "Maximum 950 caractères autorisés")]
    private ?string $infosSortie = null;

    #[ORM\Column(length: 255)]
    private ?string $etat = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "Merci d'indiquer la durée")]
    #[Assert\Range(notInRangeMessage: "La durée doit être comprise entre 15 minutes et 3 jours (4 320 minutes)", min: 15, max: 4320)]
    private ?int $duree = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "La date et l'heure de début sont obligatoires")]
    private ?\DateTimeImmutable $dateHeureDebut = null;

    #[ORM\ManyToOne(inversedBy: 'sorties')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Campus $campus = null;

    #[ORM\ManyToOne(inversedBy: 'sortiesOrganisees')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $organisateur = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'participations')]
    private Collection $participants;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message:"Merci de choisir un lieu pour cette sortie")]
    private ?Lieu $lieu = null;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
    }

    public function getDuree() : ?int {
        return $this->duree;
    }

    public function setDuree(?int $duree) : static {
        $this->duree = $duree;
        return $this;
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
        $this->nom = $nom;

        return $this;
    }

    public function getDateLimiteInscription(): ?\DateTimeImmutable
    {
        return $this->dateLimiteInscription;
    }

    public function setDateLimiteInscription(\DateTimeImmutable $dateLimiteInscription): static
    {
        $this->dateLimiteInscription = $dateLimiteInscription;

        return $this;
    }

    public function getNbInscriptionMax(): ?int
    {
        return $this->nbInscriptionMax;
    }

    public function setNbInscriptionMax(?int $nbInscriptionMax): static
    {
        $this->nbInscriptionMax = $nbInscriptionMax;

        return $this;
    }

    public function getInfosSortie(): ?string
    {
        return $this->infosSortie;
    }

    public function setInfosSortie(?string $infosSortie): static
    {
        $this->infosSortie = $infosSortie;

        return $this;
    }

    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(string $etat): static
    {
        $this->etat = $etat;

        return $this;
    }

    public function getDateHeureDebut(): ?\DateTimeImmutable
    {
        return $this->dateHeureDebut;
    }

    public function setDateHeureDebut(\DateTimeImmutable $dateHeureDebut): static
    {
        $this->dateHeureDebut = $dateHeureDebut;

        return $this;
    }

    public function getCampus(): ?Campus
    {
        return $this->campus;
    }

    public function setCampus(?Campus $campus): static
    {
        $this->campus = $campus;

        return $this;
    }

    public function getOrganisateur(): ?User
    {
        return $this->organisateur;
    }

    public function setOrganisateur(?User $organisateur): static
    {
        $this->organisateur = $organisateur;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(User $participant): static
    {
        if (!$this->participants->contains($participant)) {
            $this->participants->add($participant);
        }

        return $this;
    }

    public function removeParticipant(User $participant): static
    {
        $this->participants->removeElement($participant);

        return $this;
    }

    public function getLieu(): ?Lieu
    {
        return $this->lieu;
    }

    public function setLieu(?Lieu $lieu): static
    {
        $this->lieu = $lieu;

        return $this;
    }
}
