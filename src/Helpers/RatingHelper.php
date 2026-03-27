<?php
declare(strict_types=1);

namespace App\Helpers;

class RatingHelper
{
    public static function convertRatingToStars($rating): string
    {
        $rating = (float) $rating;
        $rating = max(0, min(5, $rating)); // Limiter entre 0 et 5

        // Calculer le pourcentage de remplissage (0 à 100%)
        $percentageFill = ($rating / 5) * 100;

        // Retourner un span avec style inline pour le gradient
        return sprintf(
            '<span class="stars" style="--rating-percent: %.1f%%">★★★★★</span>',
            $percentageFill
        );
    }
}
