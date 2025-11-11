<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Organisation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        $isMale = $this->faker->boolean(50);
        $firstName = $this->faker->firstName($isMale ? 'male' : 'female');
        $lastName = $this->faker->lastName;
        $fullName = "{$firstName} {$lastName}";

        return [
            'organisation_id' => Organisation::factory(),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'phone' => $this->faker->optional()->phoneNumber(),
            'email' => $this->faker->optional()->safeEmail(),
            'password' => 'password',
            'address' => $this->faker->optional()->streetAddress(),
            'city' => $this->faker->city(),
            'active' => true,
            'zip_code' => $this->faker->postcode(),
            'country_id' => 1,
            'gender_id' => $isMale ? 1 : 2,
            'date_of_birth' => $this->faker->optional()->dateTimeBetween('-70 years', '-18 years'),
            'language' => $this->faker->randomElement(['hr', 'en', 'de', 'it']),
            'how_did_you_hear' => $this->faker->optional()->numberBetween(1, 5),
            'oib' => $this->faker->optional()->numerify('###########'),
            'last_login_at' => $this->faker->optional()->dateTimeBetween('-3 months', 'now'),
            'remember_token' => Str::random(10),
            'email_verified_at' => now(),
        ];
    }

    /**
     * State: mark client as inactive
     */
    public function inactive(): static
    {
        return $this->state(fn() => ['active' => false]);
    }

    /**
     * State: demo client for predictable seeding
     */
    public function demo(): static
    {
        return $this->state(fn() => [
            'first_name' => 'Marko',
            'last_name' => 'Kovačić',
            'email' => 'marko.kovacic@example.com',
            'phone' => '+385911234567',
            'city' => 'Zagreb',
            'active' => true,
            'language' => 'hr',
        ]);
    }
}
