<?php

namespace App\Entity;

enum Etat: string
{
    case EN_CREATION = "en_creation";
    case OUVERTE = "ouverte";
    case CLOTUREE = "cloturee";
    case EN_COURS = "en_cours";
    case TERMINEE = "terminee";
    case ANNULEE = "annulee";
    case HISTORISEE = "historisee";

    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}
