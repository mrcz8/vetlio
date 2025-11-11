<?php

namespace Database\Factories;

use App\Models\Organisation;
use App\Models\ServiceGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ServiceGroup>
 */
class ServiceGroupFactory extends Factory
{
    protected $model = ServiceGroup::class;

    public function definition(): array
    {
        return [
            'organisation_id' => Organisation::factory(),
            'name' => $this->faker->randomElement([
                'General Examination',
                'Surgery',
                'Vaccination',
                'Laboratory',
                'Diagnostics',
                'Grooming',
                'Dental Care',
                'Emergency Services',
            ]),
            'color' => $this->faker->safeHexColor(),
        ];
    }

    /**
     * State: demo group for predictable seeding.
     */
    public function demo(string $name = 'General Examination'): static
    {
        return $this->state(fn() => [
            'name' => $name,
            'color' => '#22c55e', // Tailwind green-500
        ]);
    }
}
