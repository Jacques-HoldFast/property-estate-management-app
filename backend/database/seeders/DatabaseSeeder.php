<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Property;
use App\Models\Resident;
use App\Models\MaintenanceRequest;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Only create test user if it doesn't exist
        if (!User::where('email', 'test@example.com')->exists()) {
            User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);
        }

        $properties = Property::factory(50)->create();

        // Create residents for existing properties (1-3 residents per property)
        $properties->each(function ($property) {
            $residentCount = fake()->numberBetween(0, 3); // Some properties might be vacant
            if ($residentCount > 0) {
                Resident::factory($residentCount)->create([
                    'property_id' => $property->id,
                ]);
            }
        });

        // Create maintenance requests for properties with residents
        $propertiesWithResidents = Property::whereHas('residents')->with('residents')->get();
        
        foreach ($propertiesWithResidents as $property) {
            // Create 0-3 maintenance requests per property
            $maintenanceCount = fake()->numberBetween(0, 3);
            
            for ($i = 0; $i < $maintenanceCount; $i++) {
                $randomResident = $property->residents->random();
                
                MaintenanceRequest::factory()->create([
                    'property_id' => $property->id,
                    'resident_id' => $randomResident->id,
                ]);
            }
        }

        // Create additional maintenance requests to reach 50 total
        $currentCount = MaintenanceRequest::count();
        $remaining = max(0, 50 - $currentCount);
        
        if ($remaining > 0) {
            $allResidents = Resident::with('property')->get();
            
            for ($i = 0; $i < $remaining; $i++) {
                $randomResident = $allResidents->random();
                
                MaintenanceRequest::factory()->create([
                    'property_id' => $randomResident->property_id,
                    'resident_id' => $randomResident->id,
                ]);
            }
        }
    }
}
