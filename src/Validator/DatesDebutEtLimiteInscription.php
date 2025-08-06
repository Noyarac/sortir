<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute] class DatesDebutEtLimiteInscription extends Constraint
{
    public function validatedBy(): string
    {
        return DatesDebutEtLimiteInscriptionValidator::class;
    }

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }

}
