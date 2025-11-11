<?php

namespace Database\Factories;

use App\Models\Breed;
use App\Models\Client;
use App\Models\Organisation;
use App\Models\Patient;
use App\Models\Species;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Patient>
 */
class PatientFactory extends Factory
{
    protected $model = Patient::class;

    public function definition(): array
    {
        $isMale = $this->faker->boolean(50);

        // Ako nema podataka u bazi, napravi osnovne unose
        if (Species::count() === 0) {
            $dog = Species::create(['name' => 'Dog']);
            Breed::create(['species_id' => $dog->id, 'name' => 'Labrador Retriever']);
        }

        // Dohvati random species i breed koji mu pripada
        $species = Species::inRandomOrder()->first();
        $breed = Breed::where('species_id', $species->id)
            ->inRandomOrder()
            ->first() ?? Breed::create([
            'species_id' => $species->id,
            'name' => 'Mixed',
        ]);

        return [
            'organisation_id' => Organisation::factory(),
            'client_id' => Client::factory(),
            'name' => $this->faker->name(),
            'color' => $this->faker->safeColorName(),
            'date_of_birth' => $this->faker->optional()->dateTimeBetween('-15 years', 'now'),
            'gender_id' => $isMale ? 1 : 2,
            'species_id' => $species->id,
            'breed_id' => $breed->id,
            'dangerous' => $this->faker->boolean(5),
            'dangerous_note' => $this->faker->boolean(5) ? 'Bites when anxious' : null,
            'remarks' => $this->faker->optional()->sentence(8),
            'allergies' => $this->faker->optional()->word(),
            'archived_at' => null,
            'archived_note' => null,
            'archived_by' => null,
        ];
    }

    /**
     * Mark as archived patient
     */
    public function archived(): static
    {
        return $this->state(fn () => [
            'archived_at' => now(),
            'archived_note' => 'Patient record archived due to inactivity.',
        ]);
    }

    /**
     * Demo predictable patient
     */
    public function demo(): static
    {
        $species = Species::firstOrCreate(['name' => 'Dog']);
        $breed = Breed::firstOrCreate(['species_id' => $species->id, 'name' => 'Labrador Retriever']);

        return $this->state(fn () => [
            'name' => 'Bono',
            'photo' => 'https://place-puppy.com/400x400',
            'color' => 'brown',
            'gender_id' => 1,
            'species_id' => $species->id,
            'breed_id' => $breed->id,
            'dangerous' => false,
            'remarks' => 'Healthy and active dog, regular checkups.',
        ]);
    }
}
