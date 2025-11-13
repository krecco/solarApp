<?php

namespace Database\Seeders;

use App\Models\Investment;
use App\Models\InvestmentRepayment;
use App\Models\SolarPlant;
use App\Models\User;
use App\Services\RepaymentCalculatorService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    protected RepaymentCalculatorService $repaymentCalculator;

    public function __construct(RepaymentCalculatorService $repaymentCalculator)
    {
        $this->repaymentCalculator = $repaymentCalculator;
    }

    /**
     * Run the database seeds.
     * Creates demo solar plants, investments, and repayments
     */
    public function run(): void
    {
        // Only run in development/local
        // Environment check temporarily disabled for demo purposes
        // if (!app()->environment(['local', 'development'])) {
        //     $this->command->warn('Demo data seeder only runs in local/development environment.');
        //     return;
        // }

        $this->command->info('Creating demo data...');

        // Get or create demo users
        $owner = $this->getOrCreateUser('plant-owner@example.com', 'Plant Owner', 'customer');
        $investor1 = $this->getOrCreateUser('investor1@example.com', 'Investor One', 'customer');
        $investor2 = $this->getOrCreateUser('investor2@example.com', 'Investor Two', 'customer');
        $investor3 = $this->getOrCreateUser('investor3@example.com', 'Investor Three', 'customer');

        // Get or create manager
        $manager = User::where('email', 'manager@example.com')->first();
        if (!$manager) {
            $manager = $this->getOrCreateUser('manager@example.com', 'Demo Manager', 'manager');
            $this->command->info('✓ Created manager user: manager@example.com');
        }

        // Create demo solar plants
        $plants = $this->createSolarPlants($owner, $manager);
        $this->command->info('✓ Created ' . count($plants) . ' solar plants (owner_id: ' . $owner->id . ', manager_id: ' . $manager->id . ')');

        // Create demo investments
        $investments = $this->createInvestments($plants, [$investor1, $investor2, $investor3], $manager);
        $this->command->info('✓ Created ' . count($investments) . ' investments');

        // Generate repayment schedules for verified investments
        $repaymentCount = $this->generateRepaymentSchedules($investments);
        $this->command->info('✓ Generated ' . $repaymentCount . ' repayment schedules');

        // Mark some repayments as paid
        $paidCount = $this->markSomeRepaymentsAsPaid();
        $this->command->info('✓ Marked ' . $paidCount . ' repayments as paid');

        $this->command->info('');
        $this->command->info('Demo data created successfully!');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('Demo Users:');
        $this->command->info('  Plant Owner: plant-owner@example.com / password (ID: ' . $owner->id . ')');
        $this->command->info('  Investor 1:  investor1@example.com / password (ID: ' . $investor1->id . ')');
        $this->command->info('  Investor 2:  investor2@example.com / password (ID: ' . $investor2->id . ')');
        $this->command->info('  Investor 3:  investor3@example.com / password (ID: ' . $investor3->id . ')');
        $this->command->info('  Manager:     manager@example.com / password (ID: ' . $manager->id . ')');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('Login as admin (john@example.com) or manager (manager@example.com) to view all data');
        $this->command->info('Or login as an investor to see their own investments only');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
    }

    /**
     * Get or create a user
     */
    protected function getOrCreateUser(string $email, string $name, string $role): User
    {
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // Create customer profile for customer roles
        if ($role === 'customer') {
            if (!$user->customerProfile) {
                $user->customerProfile()->create([
                    'customer_type' => 'investor',
                    'customer_no' => 'CUST-' . str_pad($user->id, 6, '0', STR_PAD_LEFT),
                ]);
            }
        }

        // Assign role to both web and sanctum guards
        if (!$user->hasRole($role)) {
            $user->assignRole($role); // web guard
        }
        if (!$user->hasRole($role, 'sanctum')) {
            $user->roles()->attach(\Spatie\Permission\Models\Role::where('name', $role)->where('guard_name', 'sanctum')->first());
        }

        return $user;
    }

    /**
     * Create demo solar plants
     */
    protected function createSolarPlants(User $owner, User $manager): array
    {
        $plants = [];

        // Plant 1: Operational plant (fully funded)
        $plants[] = SolarPlant::create([
            'user_id' => $owner->id,
            'manager_id' => $manager->id,
            'title' => 'Solaranlage Berlin Mitte',
            'description' => 'Große Photovoltaikanlage auf Gewerbedach im Herzen Berlins',
            'address' => 'Friedrichstraße 123',
            'postal_code' => '10117',
            'city' => 'Berlin',
            'country' => 'Deutschland',
            'nominal_power' => 150.0,
            'annual_production' => 150000,
            'consumption' => 120000,
            'start_date' => Carbon::now()->subMonths(6),
            'operational_date' => Carbon::now()->subMonths(5),
            'total_cost' => 180000,
            'investment_needed' => 180000,
            'kwh_price' => 0.28,
            'status' => 'operational',
        ]);

        // Plant 2: Active plant (partially funded)
        $plants[] = SolarPlant::create([
            'user_id' => $owner->id,
            'manager_id' => $manager->id,
            'title' => 'Solaranlage Hamburg Hafen',
            'description' => 'Moderne Solaranlage auf Lagerhalle mit Speichersystem',
            'address' => 'Hafenstraße 45',
            'postal_code' => '20457',
            'city' => 'Hamburg',
            'country' => 'Deutschland',
            'nominal_power' => 200.0,
            'annual_production' => 200000,
            'consumption' => 150000,
            'start_date' => Carbon::now()->subMonths(3),
            'total_cost' => 240000,
            'investment_needed' => 240000,
            'kwh_price' => 0.30,
            'status' => 'active',
        ]);

        // Plant 3: Draft plant (no funding yet)
        $plants[] = SolarPlant::create([
            'user_id' => $owner->id,
            'manager_id' => $manager->id,
            'title' => 'Solaranlage München Süd',
            'description' => 'Geplante Anlage auf Mehrfamilienhaus',
            'address' => 'Rosenheimer Straße 78',
            'postal_code' => '81667',
            'city' => 'München',
            'country' => 'Deutschland',
            'nominal_power' => 100.0,
            'annual_production' => 110000,
            'consumption' => 80000,
            'total_cost' => 120000,
            'investment_needed' => 120000,
            'kwh_price' => 0.29,
            'status' => 'draft',
        ]);

        // Plant 4: Active plant (seeking funding)
        $plants[] = SolarPlant::create([
            'user_id' => $owner->id,
            'manager_id' => $manager->id,
            'title' => 'Solaranlage Köln West',
            'description' => 'Industriedachanlage mit Ost-West-Ausrichtung',
            'address' => 'Industriestraße 12',
            'postal_code' => '50825',
            'city' => 'Köln',
            'country' => 'Deutschland',
            'nominal_power' => 175.0,
            'annual_production' => 170000,
            'consumption' => 140000,
            'total_cost' => 210000,
            'investment_needed' => 210000,
            'kwh_price' => 0.27,
            'status' => 'active',
        ]);

        return $plants;
    }

    /**
     * Create demo investments
     */
    protected function createInvestments(array $plants, array $investors, User $verifier): array
    {
        $investments = [];

        // Investments for Plant 1 (Operational - fully funded)
        $investments[] = $this->createInvestment($plants[0], $investors[0], 80000, 4.5, 120, 'monthly', 'verified', $verifier, -5);
        $investments[] = $this->createInvestment($plants[0], $investors[1], 60000, 4.5, 120, 'monthly', 'verified', $verifier, -4);
        $investments[] = $this->createInvestment($plants[0], $investors[2], 40000, 4.5, 120, 'quarterly', 'verified', $verifier, -4);

        // Investments for Plant 2 (Active - partially funded)
        $investments[] = $this->createInvestment($plants[1], $investors[0], 100000, 5.0, 120, 'monthly', 'verified', $verifier, -2);
        $investments[] = $this->createInvestment($plants[1], $investors[1], 75000, 5.0, 120, 'quarterly', 'verified', $verifier, -1);
        $investments[] = $this->createInvestment($plants[1], $investors[2], 30000, 5.0, 60, 'monthly', 'pending', null, 0);

        // Investments for Plant 4 (Active - seeking funding)
        $investments[] = $this->createInvestment($plants[3], $investors[0], 50000, 4.8, 120, 'monthly', 'pending', null, 0);
        $investments[] = $this->createInvestment($plants[3], $investors[1], 25000, 4.8, 60, 'quarterly', 'pending', null, 0);

        return $investments;
    }

    /**
     * Create a single investment
     */
    protected function createInvestment(
        SolarPlant $plant,
        User $investor,
        float $amount,
        float $interestRate,
        int $durationMonths,
        string $interval,
        string $status,
        ?User $verifier,
        int $monthsAgo
    ): Investment {
        $investment = Investment::create([
            'user_id' => $investor->id,
            'solar_plant_id' => $plant->id,
            'amount' => $amount,
            'duration_months' => $durationMonths,
            'interest_rate' => $interestRate,
            'repayment_interval' => $interval,
            'status' => $status === 'verified' ? 'active' : $status,
            'verified' => $status === 'verified',
            'verified_at' => $status === 'verified' ? Carbon::now()->subMonths(abs($monthsAgo)) : null,
            'verified_by' => $status === 'verified' ? $verifier->id : null,
            'start_date' => $status === 'verified' ? Carbon::now()->subMonths(abs($monthsAgo)) : null,
            'end_date' => $status === 'verified' ? Carbon::now()->subMonths(abs($monthsAgo))->addMonths($durationMonths) : null,
            'notes' => 'Demo investment for testing purposes',
        ]);

        // Calculate totals for verified investments
        if ($status === 'verified') {
            $calculations = $this->repaymentCalculator->calculateTotalRepayment(
                $amount,
                $interestRate,
                $durationMonths
            );

            $investment->update([
                'total_repayment' => $calculations['total'],
                'total_interest' => $calculations['interest'],
            ]);
        }

        return $investment;
    }

    /**
     * Generate repayment schedules for verified investments
     */
    protected function generateRepaymentSchedules(array $investments): int
    {
        $count = 0;

        foreach ($investments as $investment) {
            if ($investment->verified && $investment->start_date) {
                $this->repaymentCalculator->createRepaymentSchedule($investment);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Mark some repayments as paid (for realistic demo data)
     */
    protected function markSomeRepaymentsAsPaid(): int
    {
        $count = 0;

        // Get all repayments that are due in the past or within last 30 days
        $repayments = InvestmentRepayment::where('status', 'pending')
            ->where('due_date', '<=', Carbon::now()->addDays(30))
            ->orderBy('due_date', 'asc')
            ->get();

        foreach ($repayments as $repayment) {
            // Mark as paid if due date is in the past (80% chance)
            if ($repayment->due_date < Carbon::now() && rand(1, 100) <= 80) {
                $repayment->update([
                    'status' => 'paid',
                    'paid_date' => $repayment->due_date->addDays(rand(1, 5)),
                    'payment_method' => 'sepa',
                    'reference_number' => 'DEMO-' . strtoupper(substr(md5(uniqid()), 0, 10)),
                ]);

                // Update investment paid amount
                $investment = $repayment->investment;
                $investment->increment('paid_amount', $repayment->amount);

                $count++;
            }
        }

        return $count;
    }
}
