<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Organisation;
use App\Models\Patient;
use App\Models\Payment;
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

            $admin->branches()->attach($adminBranch->id);

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

            // 7️⃣ Kreiraj cijene za svaku uslugu u svakom cjeniku
            $priceLists->each(function (PriceList $priceList) use ($services, $organisation) {
                $services->each(function (Service $service) use ($priceList, $organisation) {

                    // Svaka usluga ima 1–3 cijene (npr. povijesne, važeće, buduće)
                    $count = rand(1, 3);
                    $baseDate = now()->subMonths(rand(0, 6));

                    for ($i = 0; $i < $count; $i++) {
                        $price = fake()->randomFloat(2, 10, 150);
                        $vat = fake()->randomElement([13, 25]);
                        $withVat = round($price * (1 + $vat / 100), 2);

                        Price::create([
                            'organisation_id' => $organisation->id,
                            'price_list_id' => $priceList->id,
                            'priceable_id' => $service->id,
                            'priceable_type' => Service::class,
                            'price' => $price,
                            'vat_percentage' => $vat,
                            'price_with_vat' => $withVat,
                            'valid_from_at' => $baseDate->addMonths($i)->startOfMonth(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                });
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

            //Create invoices
            $clients = Client::where('organisation_id', $organisation->id)->get();
            $services = Service::where('organisation_id', $organisation->id)->get();
            $users = User::where('organisation_id', $organisation->id)->get();
            $branches = Branch::where('organisation_id', $organisation->id)->get();
            $priceLists = PriceList::where('organisation_id', $organisation->id)->get();

            $invoices = collect();

            for ($i = 0; $i < rand(10, 20); $i++) {
                $client = $clients->random();
                $branch = $branches->random();
                $user = $users->random();
                $priceList = $priceLists->random();

                $invoice = Invoice::factory()
                    ->for($organisation)
                    ->for($client)
                    ->for($user, 'user')
                    ->for($user, 'issuer')
                    ->for($branch)
                    ->for($priceList)
                    ->create();

                $items = $services->random(rand(2, 5));
                $totals = ['base' => 0, 'tax' => 0, 'total' => 0, 'discount' => 0];

                foreach ($items as $service) {
                    $qty = rand(1, 3);
                    $price = fake()->randomFloat(2, 15, 200);
                    $discount = fake()->boolean(20) ? rand(5, 20) : 0;
                    $base = $price * $qty;
                    $discountValue = round($base * $discount / 100, 2);
                    $taxRate = fake()->randomElement([13, 25]);
                    $tax = round(($base - $discountValue) * $taxRate / 100, 2);
                    $total = round($base - $discountValue + $tax, 2);

                    \App\Models\InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'organisation_id' => $organisation->id,
                        'priceable_id' => $service->id,
                        'priceable_type' => \App\Models\Service::class,
                        'name' => $service->name,
                        'description' => fake()->optional()->sentence(),
                        'quantity' => $qty,
                        'price' => $price,
                        'base_price' => $base,
                        'discount' => $discountValue,
                        'tax' => $tax,
                        'total' => $total,
                    ]);

                    $totals['base'] += $base;
                    $totals['discount'] += $discountValue;
                    $totals['tax'] += $tax;
                    $totals['total'] += $total;
                }

                $invoice->update([
                    'total_base_price' => $totals['base'],
                    'total_discount' => $totals['discount'],
                    'total_tax' => $totals['tax'],
                    'total' => $totals['total'],
                ]);

                $invoices->push($invoice);
            }

            //Create payments
            $invoices = Invoice::where('organisation_id', $organisation->id)->get();

            $payments = collect();

            foreach ($invoices as $invoice) {
                $count = rand(1, 4);

                for ($i = 0; $i < $count; $i++) {
                    $payment = Payment::factory()
                        ->forInvoice($invoice)
                        ->for($invoice->organisation)
                        ->create();

                    $payments->push($payment);
                }
            }
        });
    }
}
