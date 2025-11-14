<?php

namespace App\Modules\CarRentals\Seeders;

use App\Models\User;
use App\Modules\CarRentals\Enums\FuelType;
use App\Modules\CarRentals\Enums\TransmissionType;
use App\Modules\CarRentals\Enums\VehicleCategory;
use App\Modules\CarRentals\Enums\VehicleStatus;
use App\Modules\CarRentals\Models\Vehicle;
use Illuminate\Database\Seeder;

class VehicleSeeder extends Seeder
{
    public function run(): void
    {
        $manager = User::where('email', 'manager@example.com')->first();
        $owner = User::where('email', 'owner@example.com')->first();

        $vehicles = [
            [
                'vin' => 'WBADT43452G296859',
                'make' => 'Volkswagen',
                'model' => 'Golf',
                'year' => 2023,
                'color' => 'Silver',
                'license_plate' => 'B-VW-1234',
                'category' => VehicleCategory::COMPACT,
                'transmission' => TransmissionType::MANUAL,
                'fuel_type' => FuelType::GASOLINE,
                'seats' => 5,
                'doors' => 5,
                'mileage' => 15000,
                'features' => ['Air Conditioning', 'Bluetooth', 'USB Port', 'ABS'],
                'daily_rate' => 35.00,
                'weekly_rate' => 210.00,
                'monthly_rate' => 800.00,
                'security_deposit' => 500.00,
                'status' => VehicleStatus::AVAILABLE,
                'location' => 'Berlin',
                'owner_id' => $owner?->id,
                'manager_id' => $manager?->id,
                'description' => 'Reliable and economical compact car, perfect for city driving',
                'multilang_data' => [
                    'en' => [
                        'description' => 'Reliable and economical compact car, perfect for city driving',
                    ],
                    'de' => [
                        'description' => 'Zuverlässiges und wirtschaftliches Kompaktauto, perfekt für Stadtfahrten',
                    ],
                    'fr' => [
                        'description' => 'Voiture compacte fiable et économique, parfaite pour la conduite en ville',
                    ],
                ],
            ],
            [
                'vin' => 'WBADT43452G296860',
                'make' => 'BMW',
                'model' => '320i',
                'year' => 2024,
                'color' => 'Black',
                'license_plate' => 'M-BMW-5678',
                'category' => VehicleCategory::LUXURY,
                'transmission' => TransmissionType::AUTOMATIC,
                'fuel_type' => FuelType::GASOLINE,
                'seats' => 5,
                'doors' => 4,
                'mileage' => 5000,
                'features' => ['Leather Seats', 'Navigation', 'Premium Sound', 'Sunroof', 'Parking Sensors'],
                'daily_rate' => 85.00,
                'weekly_rate' => 510.00,
                'monthly_rate' => 2000.00,
                'security_deposit' => 1500.00,
                'status' => VehicleStatus::AVAILABLE,
                'location' => 'Munich',
                'owner_id' => $owner?->id,
                'manager_id' => $manager?->id,
                'description' => 'Luxury sedan with premium features and exceptional comfort',
                'multilang_data' => [
                    'en' => [
                        'description' => 'Luxury sedan with premium features and exceptional comfort',
                    ],
                    'de' => [
                        'description' => 'Luxuslimousine mit Premium-Ausstattung und außergewöhnlichem Komfort',
                    ],
                ],
            ],
            [
                'vin' => 'WBADT43452G296861',
                'make' => 'Mercedes-Benz',
                'model' => 'Sprinter',
                'year' => 2023,
                'color' => 'White',
                'license_plate' => 'HH-MB-9012',
                'category' => VehicleCategory::VAN,
                'transmission' => TransmissionType::AUTOMATIC,
                'fuel_type' => FuelType::DIESEL,
                'seats' => 9,
                'doors' => 5,
                'mileage' => 25000,
                'features' => ['Air Conditioning', 'Cruise Control', 'Rear Camera', 'Large Cargo Space'],
                'daily_rate' => 95.00,
                'weekly_rate' => 570.00,
                'monthly_rate' => 2200.00,
                'security_deposit' => 2000.00,
                'status' => VehicleStatus::AVAILABLE,
                'location' => 'Hamburg',
                'owner_id' => $owner?->id,
                'manager_id' => $manager?->id,
                'description' => 'Spacious van perfect for group travel or cargo transport',
                'multilang_data' => [
                    'en' => [
                        'description' => 'Spacious van perfect for group travel or cargo transport',
                    ],
                    'de' => [
                        'description' => 'Geräumiger Van perfekt für Gruppenreisen oder Warentransport',
                    ],
                ],
            ],
            [
                'vin' => 'WBADT43452G296862',
                'make' => 'Tesla',
                'model' => 'Model 3',
                'year' => 2024,
                'color' => 'Blue',
                'license_plate' => 'F-TES-3456',
                'category' => VehicleCategory::LUXURY,
                'transmission' => TransmissionType::AUTOMATIC,
                'fuel_type' => FuelType::ELECTRIC,
                'seats' => 5,
                'doors' => 4,
                'mileage' => 8000,
                'features' => ['Autopilot', 'Premium Audio', 'Glass Roof', 'Fast Charging', 'Tech Package'],
                'daily_rate' => 120.00,
                'weekly_rate' => 720.00,
                'monthly_rate' => 2800.00,
                'security_deposit' => 2500.00,
                'status' => VehicleStatus::AVAILABLE,
                'location' => 'Frankfurt',
                'owner_id' => $owner?->id,
                'manager_id' => $manager?->id,
                'description' => 'Electric luxury sedan with cutting-edge technology',
                'multilang_data' => [
                    'en' => [
                        'description' => 'Electric luxury sedan with cutting-edge technology',
                    ],
                    'de' => [
                        'description' => 'Elektrische Luxuslimousine mit modernster Technologie',
                    ],
                ],
            ],
            [
                'vin' => 'WBADT43452G296863',
                'make' => 'Audi',
                'model' => 'Q5',
                'year' => 2023,
                'color' => 'Grey',
                'license_plate' => 'B-AUD-7890',
                'category' => VehicleCategory::SUV,
                'transmission' => TransmissionType::AUTOMATIC,
                'fuel_type' => FuelType::HYBRID,
                'seats' => 5,
                'doors' => 5,
                'mileage' => 12000,
                'features' => ['Quattro AWD', 'Virtual Cockpit', 'Panoramic Roof', 'LED Headlights'],
                'daily_rate' => 100.00,
                'weekly_rate' => 600.00,
                'monthly_rate' => 2400.00,
                'security_deposit' => 1800.00,
                'status' => VehicleStatus::AVAILABLE,
                'location' => 'Berlin',
                'owner_id' => $owner?->id,
                'manager_id' => $manager?->id,
                'description' => 'Hybrid SUV combining efficiency with luxury',
                'multilang_data' => [
                    'en' => [
                        'description' => 'Hybrid SUV combining efficiency with luxury',
                    ],
                    'de' => [
                        'description' => 'Hybrid-SUV, der Effizienz mit Luxus verbindet',
                    ],
                ],
            ],
            [
                'vin' => 'WBADT43452G296864',
                'make' => 'Ford',
                'model' => 'Fiesta',
                'year' => 2022,
                'color' => 'Red',
                'license_plate' => 'M-FOR-2345',
                'category' => VehicleCategory::ECONOMY,
                'transmission' => TransmissionType::MANUAL,
                'fuel_type' => FuelType::GASOLINE,
                'seats' => 5,
                'doors' => 5,
                'mileage' => 35000,
                'features' => ['Air Conditioning', 'Radio', 'Power Windows'],
                'daily_rate' => 28.00,
                'weekly_rate' => 168.00,
                'monthly_rate' => 650.00,
                'security_deposit' => 400.00,
                'status' => VehicleStatus::AVAILABLE,
                'location' => 'Munich',
                'owner_id' => $owner?->id,
                'manager_id' => $manager?->id,
                'description' => 'Budget-friendly economy car for everyday use',
                'multilang_data' => [
                    'en' => [
                        'description' => 'Budget-friendly economy car for everyday use',
                    ],
                    'de' => [
                        'description' => 'Budgetfreundliches Wirtschaftsauto für den täglichen Gebrauch',
                    ],
                ],
            ],
        ];

        foreach ($vehicles as $vehicleData) {
            Vehicle::create($vehicleData);
        }

        $this->command->info('Vehicles seeded successfully!');
    }
}
