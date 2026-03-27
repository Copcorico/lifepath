<?php
declare(strict_types=1);

namespace App\Helpers;

use DateTime;
use DateInterval;

class DateHelper
{
    public static function formatDateFR($date): string
    {
        if (is_string($date)) {
            $date = new DateTime($date);
        }

        $mois = [
            1 => 'janvier', 2 => 'février', 3 => 'mars', 4 => 'avril',
            5 => 'mai', 6 => 'juin', 7 => 'juillet', 8 => 'août',
            9 => 'septembre', 10 => 'octobre', 11 => 'novembre', 12 => 'décembre'
        ];

        $jour = (int)$date->format('d');
        $moisNum = (int)$date->format('m');
        $annee = $date->format('Y');

        return sprintf('%d %s %s', $jour, $mois[$moisNum], $annee);
    }

    public static function calculateDuration($dateDebut, $dateFin): string
    {
        if (is_string($dateDebut)) {
            $dateDebut = new DateTime($dateDebut);
        }
        if (is_string($dateFin)) {
            $dateFin = new DateTime($dateFin);
        }

        $interval = $dateDebut->diff($dateFin);

        // Calcul en mois si plus de 10 jours
        if ($interval->days >= 10) {
            $mois = $interval->m + ($interval->y * 12);
            if ($mois > 0) {
                return $mois === 1 ? '1 mois' : $mois . ' mois';
            }
        }

        // Calcul en semaines si plus de 7 jours
        if ($interval->days >= 7) {
            $semaines = intdiv($interval->days, 7);
            return $semaines === 1 ? '1 semaine' : $semaines . ' semaines';
        }

        // Sinon en jours
        return $interval->days === 1 ? '1 jour' : $interval->days . ' jours';
    }
}
