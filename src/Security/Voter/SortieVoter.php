<?php

namespace App\Security\Voter;

use App\Entity\Etat;
use App\Entity\Sortie;
use App\Entity\User;
use DateTimeImmutable;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class SortieVoter extends Voter
{
    public const INSCRIPTION = 'sortie_inscription';
    public const DESISTEMENT = 'sortie_desistement';
    public const MODIFICATION = 'sortie_modification';

    public const ANNULATION = 'sortie_annulation';

    public const BROUILLON = 'sortie_brouillon';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::INSCRIPTION, self::DESISTEMENT, self::MODIFICATION, self::ANNULATION, self::BROUILLON])
            && $subject instanceof \App\Entity\Sortie;
    }

    protected function voteOnAttribute(string $attribute, mixed $sortie, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case self::INSCRIPTION:
                return $this->inscription($sortie, $user);
                break;

            case self::DESISTEMENT:
                return $this->desistement($sortie, $user);
                break;

            case self::MODIFICATION:
                return $this->modification($sortie, $user);
                break;
            case self::ANNULATION:
                return $this->annulation($sortie, $user);
                break;
            case self::BROUILLON:
                return $this->brouillon($sortie, $user);
                break;
        }
        return false;
    }

    private function inscription(Sortie $sortie, User $user) : bool {
        return
            !in_array($user, $sortie->getParticipants()->getValues())
            && $sortie->getDateLimiteInscription() >= new DateTimeImmutable()
            && $sortie->getEtat() == Etat::OUVERTE->value
            && sizeof($sortie->getParticipants()) < $sortie->getNbInscriptionMax();
    }

    private function desistement(Sortie $sortie, User $user) : bool {
        return
            in_array($user, $sortie->getParticipants()->getValues())
            && in_array($sortie->getEtat(), [Etat::OUVERTE->value, Etat::CLOTUREE->value])
            && $sortie->getDateHeureDebut() > new DateTimeImmutable();
    }

    private function modification(Sortie $sortie, User $user) : bool {
        return
            $user === $sortie->getOrganisateur()
            && $sortie->getEtat() == Etat::EN_CREATION->value;
    }

    private function brouillon(Sortie $sortie, User $user) : bool {
        return
            !($user != $sortie->getOrganisateur()
            && $sortie->getEtat() == Etat::EN_CREATION->value);
    }

    private function annulation(Sortie $sortie, User $user) : bool {
        return
            ($user === $sortie->getOrganisateur() || in_array('ROLE_ADMIN', $user->getRoles()))
            && ($sortie->getEtat() == Etat::OUVERTE->value || $sortie->getEtat() == Etat::CLOTUREE->value);
    }
}
