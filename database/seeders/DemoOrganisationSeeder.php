<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Client;
use App\Models\Organisation;
use App\Models\Patient;
use App\Models\Price;
use App\Models\PriceList;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\Service;
use App\Models\ServiceGroup;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoOrganisationSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            //Create organisation
            $organisation = Organisation::factory()
                ->demo()
                ->create();

            //Create branches
            $branches = Branch::factory()
                ->count(rand(1, 3))
                ->for($organisation)
                ->create();

            // Create rooms for each branch
            $branches->each(function (Branch $branch) use ($organisation) {
                Room::factory()
                    ->count(3)
                    ->for($organisation)
                    ->for($branch)
                    ->create();
            });

            // Create users
            $adminBranch = $branches->random();

            $admin = User::factory()
                ->for($organisation)
                ->admin()
                ->create([
                    'email' => 'admin@org1.com',
                    'first_name' => 'Admin',
                    'last_name' => 'Admin',
                    'name' => 'admin',
                    'primary_branch_id' => $adminBranch->id,
                ]);

            $users = User::factory()
                ->count(4)
                ->for($organisation)
                ->make()
                ->each(function ($user) use ($branches, $organisation) {
                    $branch = $branches->random();
                    $user->primary_branch_id = $branch->id;
                    $user->organisation_id = $organisation->id;
                    $user->save();

                    if (method_exists($user, 'branches')) {
                        $assigned = $branches->random(rand(1, $branches->count()));
                        $user->branches()->attach($assigned->pluck('id'));
                    }
                });

            $users->each(function (User $user) use ($branches) {
                $assignedBranches = $branches->random(rand(1, $branches->count()));
                $user->update([
                    'primary_branch_id' => $assignedBranches->first()->id,
                ]);
                if (method_exists($user, 'branches')) {
                    $user->branches()->attach($assignedBranches->pluck('id'));
                }
            });

            // Create price lists
            $priceLists = PriceList::factory()
                ->count(3)
                ->for($organisation)
                ->create();

            //Link with branches
            $branches->each(function (Branch $branch) use ($priceLists) {
                $linked = $priceLists->random(rand(1, 2));
                $branch->update(['price_list_id' => $linked->first()->id]);

                if (method_exists($branch, 'priceLists')) {
                    $branch->priceLists()->attach($linked->pluck('id'));
                }
            });


            $groups = ServiceGroup::factory()
                ->count(5)
                ->for($organisation)
                ->create();

            $services = collect();
            $groups->each(function (ServiceGroup $group) use ($organisation, &$services) {
                $created = Service::factory()
                    ->count(rand(3, 6))
                    ->for($organisation)
                    ->for($group)
                    ->create();

                $services = $services->merge($created);
            });

            $services->each(function (Service $service) use ($priceLists, $organisation) {
                $primaryList = $priceLists->random();

                Price::factory()
                    ->for($organisation)
                    ->for($primaryList)
                    ->for($service, 'priceable')
                    ->create();

                if (rand(1, 100) <= 30 && $priceLists->count() > 1) {
                    $secondaryList = $priceLists->where('id', '!=', $primaryList->id)->random();
                    Price::factory()
                        ->for($organisation)
                        ->for($secondaryList)
                        ->for($service, 'priceable')
                        ->create();
                }
            });

            $clients = Client::factory()
                ->count(20)
                ->for($organisation)
                ->create();

            $patients = collect();
            $clients->each(function (Client $client) use ($organisation, &$patients) {
                $created = Patient::factory()
                    ->count(rand(1, 3))
                    ->for($organisation)
                    ->for($client)
                    ->create();
                $patients = $patients->merge($created);
            });

            $allRooms = Room::whereIn('branch_id', $branches->pluck('id'))->get();
            $vets = $users->where('service_provider', true);

            Reservation::factory()
                ->count(rand(15, 80))
                ->for($organisation)
                ->state(function () use ($clients, $patients, $branches, $allRooms, $vets, $services) {
                    $branch = $branches->random();
                    $room = $allRooms->where('branch_id', $branch->id)->random();
                    $client = $clients->random();
                    $patient = $client->patients()->inRandomOrder()->first() ?? $patients->random();
                    $vet = $vets->random();

                    return [
                        'branch_id' => $branch->id,
                        'room_id' => $room->id,
                        'client_id' => $client->id,
                        'patient_id' => $patient->id,
                        'service_id' => $services->random()->id,
                        'user_id' => $vet->id,
                        'service_provider_id' => $vet->id,
                    ];
                })
                ->create();
        });
    }
}
