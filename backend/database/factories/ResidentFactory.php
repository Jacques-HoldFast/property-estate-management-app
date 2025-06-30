<?php

namespace Database\Factories;

use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Resident>
 */
class ResidentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $southAfricanNames = [
            'male_first' => ['Sipho', 'Thabo', 'Mandla', 'Tshepo', 'Lerato', 'Bongani', 'Nkosana', 'Andile', 'Sizani', 'Mpho'],
            'female_first' => ['Nomsa', 'Thandiwe', 'Zanele', 'Nokuthula', 'Precious', 'Beauty', 'Faith', 'Grace', 'Hope', 'Bontle'],
            'surnames' => ['Mthembu', 'Nkomo', 'Dlamini', 'Khumalo', 'Mabaso', 'Sithole', 'Mbeki', 'Modise', 'Van Der Merwe', 'Smith', 'Johnson', 'Williams', 'Botha', 'Pretorius']
        ];
        
        $gender = $this->faker->randomElement(['male', 'female']);
        $firstName = $gender === 'male' 
            ? $this->faker->randomElement($southAfricanNames['male_first'])
            : $this->faker->randomElement($southAfricanNames['female_first']);
        
        $lastName = $this->faker->randomElement($southAfricanNames['surnames']);
        
        // Generate South African ID number (simplified)
        $birthYear = $this->faker->numberBetween(70, 05); // 1970-2005
        $birthMonth = str_pad($this->faker->numberBetween(1, 12), 2, '0', STR_PAD_LEFT);
        $birthDay = str_pad($this->faker->numberBetween(1, 28), 2, '0', STR_PAD_LEFT);
        $genderDigit = $gender === 'male' ? $this->faker->numberBetween(5, 9) : $this->faker->numberBetween(0, 4);
        $citizenshipDigit = 0; // 0 = SA citizen
        $raceDigit = 8; // Not used anymore but required for format
        $checkDigit = $this->faker->numberBetween(0, 9);
        
        $idNumber = $birthYear . $birthMonth . $birthDay . $this->faker->numberBetween(5000, 9999) . $citizenshipDigit . $raceDigit . $checkDigit;
        
        // Generate realistic lease dates
        $leaseStart = $this->faker->dateTimeBetween('-2 years', 'now');
        $leaseEnd = $this->faker->optional(0.8)->dateTimeBetween($leaseStart, '+2 years'); // 80% have end date
        
        // Generate realistic rent based on property type (we'll randomize since we don't have property context here)
        $monthlyRent = $this->faker->numberBetween(3000, 15000); // R3,000 - R15,000
        $depositAmount = $monthlyRent * $this->faker->numberBetween(1, 3); // 1-3 months deposit
        
        $occupations = [
            'Teacher', 'Nurse', 'Engineer', 'Accountant', 'Sales Representative', 'Marketing Manager',
            'IT Specialist', 'Doctor', 'Lawyer', 'Student', 'Consultant', 'Driver', 'Administrator',
            'Project Manager', 'Analyst', 'Technician', 'Supervisor', 'Coordinator', 'Specialist'
        ];

        return [
            'property_id' => Property::factory(),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => strtolower($firstName . '.' . $lastName . $this->faker->numberBetween(1, 999)) . '@' . $this->faker->randomElement(['gmail.com', 'yahoo.com', 'outlook.com', 'webmail.co.za']),
            'phone' => '+27' . $this->faker->numberBetween(60, 89) . $this->faker->numerify('#######'),
            'id_number' => $idNumber,
            'date_of_birth' => $this->faker->dateTimeBetween('-55 years', '-18 years')->format('Y-m-d'),
            'gender' => $gender,
            'occupation' => $this->faker->randomElement($occupations),
            'emergency_contact_name' => $this->faker->name(),
            'emergency_contact_phone' => '+27' . $this->faker->numberBetween(60, 89) . $this->faker->numerify('#######'),
            'lease_start_date' => $leaseStart->format('Y-m-d'),
            'lease_end_date' => $leaseEnd?->format('Y-m-d'),
            'monthly_rent' => $monthlyRent,
            'deposit_amount' => $depositAmount,
            'status' => $this->faker->randomElement(['active', 'inactive', 'pending']),
            'notes' => $this->faker->optional(0.3)->paragraph(), // 30% have notes
        ];
    }
}
