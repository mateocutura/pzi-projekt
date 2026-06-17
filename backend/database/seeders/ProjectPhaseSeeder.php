<?php

namespace Database\Seeders;

use App\Models\ProjectPhase;
use Illuminate\Database\Seeder;

class ProjectPhaseSeeder extends Seeder
{
    public function run(): void
    {
        $phases = [
            [
                'step_number' => 1,
                'name' => 'Prijava teme',
                'description' => 'Predaj naslov projekta, kratki opis i viziju što želiš postići.',
            ],
            [
                'step_number' => 2,
                'name' => 'Pregled literature',
                'description' => 'Istraži i dokumentiraj postojeća rješenja i relevantnu literaturu.',
            ],
            [
                'step_number' => 3,
                'name' => 'Tehnička specifikacija',
                'description' => 'Definiraj funkcionalne i nefunkcionalne zahtjeve sustava.',
            ],
            [
                'step_number' => 4,
                'name' => 'Dizajn arhitekture',
                'description' => 'Nacrtaj arhitekturu sustava - komponente, servisi, komunikacija.',
            ],
            [
                'step_number' => 5,
                'name' => 'Dizajn baze podataka',
                'description' => 'Kreiraj ER dijagram i definiraj strukturu baze podataka.',
            ],
            [
                'step_number' => 6,
                'name' => 'Implementacija backenda',
                'description' => 'Razvij serversku logiku, API endpointove i poslovnu logiku.',
            ],
            [
                'step_number' => 7,
                'name' => 'Implementacija frontenda',
                'description' => 'Razvij korisničko sučelje i poveži ga s backendom.',
            ],
            [
                'step_number' => 8,
                'name' => 'Testiranje',
                'description' => 'Napiši i pokreni testove (unit, integracija, end-to-end).',
            ],
            [
                'step_number' => 9,
                'name' => 'Dokumentacija',
                'description' => 'Napiši tehničku dokumentaciju i korisničke upute.',
            ],
            [
                'step_number' => 10,
                'name' => 'Finalna predaja',
                'description' => 'Predaj završni rad s demo verzijom i prezentacijom.',
            ],
        ];

        foreach ($phases as $phase) {
            ProjectPhase::firstOrCreate(
                ['step_number' => $phase['step_number']],
                $phase
            );
        }
    }
}
