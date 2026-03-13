<?php

namespace App\Models;

class routeurModel
{
    public function __construct()
    {
        // Router pages currently do not require model data.
    }

    public function getHomeOffers(): array
    {
        return [
            [
                'titre' => 'Developpement application web interne',
                'poste' => 'Developpeur Web',
                'entreprise' => 'TechSolutions',
                'lieu' => 'Paris',
                'niveau' => 'Bac +5',
            ],
            [
                'titre' => 'Creation plateforme e-commerce',
                'poste' => 'Developpeur Web',
                'entreprise' => 'DigitalMarket',
                'lieu' => 'Lyon',
                'niveau' => 'Bac +4',
            ],
            [
                'titre' => 'Conception API mobile',
                'poste' => 'Developpeur Backend',
                'entreprise' => 'CodeFactory',
                'lieu' => 'Toulouse',
                'niveau' => 'Bac +5',
            ],
            [
                'titre' => 'Optimisation site web existant',
                'poste' => 'Developpeur Web',
                'entreprise' => 'NextTech',
                'lieu' => 'Strasbourg',
                'niveau' => 'Bac +3',
            ],
            [
                'titre' => 'Developpement tableau de bord analytique',
                'poste' => 'Developpeur Full Stack',
                'entreprise' => 'DataVision',
                'lieu' => 'Lille',
                'niveau' => 'Bac +5',
            ],
        ];
    }
}
