<?php

namespace Database\Factories;

use App\Models\Organisation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        $isMale = $this->faker->boolean(50);
        $firstName = $this->faker->firstName($isMale ? 'male' : 'female');
        $lastName = $this->faker->lastName;
        $fullName = "{$firstName} {$lastName}";

        return [
            'organisation_id' => Organisation::factory(),
            'primary_branch_id' => null,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'title' => $this->faker->optional()->randomElement(['Dr. Vet. Med.', 'BSc. Vet.', null]),
            'code' => strtoupper(Str::random(4)),
            'gender_id' => $isMale ? 1 : 2,
            'date_of_birth' => $this->faker->optional()->dateTimeBetween('-50 years', '-22 years'),
            'name' => $fullName,
            'email' => strtolower(Str::slug($fullName)) . '@' . $this->faker->safeEmailDomain(),
            'email_verified_at' => now(),
            'password' => 'password',
            'phone' => $this->faker->optional()->phoneNumber,
            'oib' => $this->faker->optional()->numerify('###########'),
            'fiscalization_enabled' => $this->faker->boolean(30),
            'signature_path' => null,
            'active' => true,
            'administrator' => false,
            'service_provider' => $this->faker->boolean(70),
            'color' => $this->faker->safeHexColor(),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Mark user as inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn() => ['active' => false]);
    }

    /**
     * Mark user as administrator.
     */
    public function admin(): static
    {
        return $this->state(fn() => [
            'administrator' => true,
            'service_provider' => false,
            'title' => 'Administrator',
            'fiscalization_enabled' => false,
        ]);
    }

    /**
     * Mark user as veterinarian.
     */
    public function veterinarian(): static
    {
        return $this->state(fn() => [
            'administrator' => false,
            'service_provider' => true,
            'title' => 'Dr. Vet. Med.',
            'fiscalization_enabled' => true,
        ]);
    }

    /**
     * Demo user for demo organisation.
     */
    public function demo(): static
    {
        return $this->state(fn() => [
            'first_name' => 'Demo',
            'last_name' => 'Demo',
            'name' => 'demo',
            'email' => 'admin@vetlio.app',
            'administrator' => true,
            'service_provider' => true,
            'active' => true,
        ]);
    }
}
