<?php

namespace App\Validator;

use App\Entity\Sortie;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class DatesDebutEtLimiteInscriptionValidator extends ConstraintValidator
{
    /**
     * @inheritDoc
     */
    public function validate(mixed $value, Constraint $constraint)
    {
        if (!$value instanceof Sortie) {
            return;
        }
        $dateDebut = $value->getDateHeureDebut();
        $dateLimiteInscription = $value->getDateLimiteInscription();

        if ($dateDebut) {
            $dateDebutMinimale = (new \DateTimeImmutable('now'))->modify('+2 days');

            if ($dateDebut < $dateDebutMinimale) {
                $this->context->buildViolation("La date de début doit être au moins le " . $dateDebutMinimale->format('d/m/Y'))
                    ->atPath('dateHeureDebut')
                    ->addViolation();
            }
        }


        if ($dateDebut && $dateLimiteInscription) {
        $dateLimiteMaximum = $dateDebut->sub(new \DateInterval("P1D"));
            if ($dateLimiteInscription > $dateLimiteMaximum) {
                $this->context->buildViolation(
                    "La date limite d'inscription doit être au plus tard la veille de la sortie soit le ".$dateLimiteMaximum->format('d/m/Y').
                    " maximum pour une sortie prévue le " . $dateDebut->format('d/m/Y'))
                    ->atPath('dateLimiteInscription')
                    ->addViolation();
            }
        }
    }
}
