<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Client;
use App\Models\Organisation;
use App\Models\Patient;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reservation>
 */
class ReservationFactory extends Factory
{
    protected $model = Reservation::class;

    public function definition(): array
    {
        $start = Carbon::now()
            ->addDays(rand(0, 10))
            ->setTime(rand(8, 16), [0, 15, 30, 45][array_rand([0, 1, 2, 3])]);
        $end = (clone $start)->addMinutes(30);

        $status = $this->faker->randomElement([1, 2, 3, 4]); // e.g. draft, scheduled, completed, canceled

        return [
            'uuid' => Str::uuid()->toString(),
            'organisation_id' => Organisation::factory(),
            'client_id' => Client::factory(),
            'patient_id' => Patient::factory(),
            'branch_id' => Branch::factory(),
            'room_id' => Room::factory(),
            'service_id' => Service::factory(),
            'user_id' => User::factory(), // created/assigned by user
            'service_provider_id' => User::factory(), // the vet performing the service

            'status_id' => $status,
            'note' => $this->faker->optional()->sentence(),
            'reason_for_coming' => $this->faker->optional()->sentence(3),

            'date' => $start,
            'from' => $start,
            'to' => $end,

            'canceled_at' => null,
            'waiting_room_at' => null,
            'in_process_at' => null,
            'completed_at' => null,
            'confirmed_status_id' => null,
            'confirmed_at' => null,
            'confirmed_note' => null,
        ];
    }

    /**
     * State: canceled reservation
     */
    public function canceled(): static
    {
        return $this->state(function () {
            return [
                'status_id' => 4,
                'canceled_at' => now(),
            ];
        });
    }

    /**
     * State: completed reservation
     */
    public function completed(): static
    {
        return $this->state(function () {
            $start = now()->subHours(rand(2, 4));
            return [
                'status_id' => 3,
                'date' => $start,
                'from' => $start,
                'to' => (clone $start)->addMinutes(30),
                'in_process_at' => $start->copy()->addMinutes(5),
                'completed_at' => $start->copy()->addMinutes(35),
            ];
        });
    }

    /**
     * State: confirmed reservation
     */
    public function confirmed(): static
    {
        return $this->state(fn () => [
            'confirmed_status_id' => 1,
            'confirmed_at' => now(),
            'confirmed_note' => 'Confirmed by client via portal.',
        ]);
    }

    /**
     * State: demo predictable reservation
     */
    public function demo(): static
    {
        $start = Carbon::now()->setTime(10, 0);
        $end = (clone $start)->addMinutes(30);

        return $this->state(fn () => [
            'uuid' => Str::uuid()->toString(),
            'date' => $start,
            'from' => $start,
            'to' => $end,
            'status_id' => 2, // scheduled
            'note' => 'Routine check-up.',
            'reason_for_coming' => 'Vaccination booster',
            'confirmed_status_id' => 1,
            'confirmed_at' => now(),
            'confirmed_note' => 'Confirmed via client portal',
        ]);
    }
}
