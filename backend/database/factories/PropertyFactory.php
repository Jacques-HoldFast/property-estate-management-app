<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Property>
 */
class PropertyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $southAfricanCities = [
            ['name' => 'Cape Town', 'province' => 'Western Cape', 'lat' => -33.9249, 'lng' => 18.4241],
            ['name' => 'Johannesburg', 'province' => 'Gauteng', 'lat' => -26.2041, 'lng' => 28.0473],
            ['name' => 'Durban', 'province' => 'KwaZulu-Natal', 'lat' => -29.8587, 'lng' => 31.0218],
            ['name' => 'Pretoria', 'province' => 'Gauteng', 'lat' => -25.7479, 'lng' => 28.2293],
            ['name' => 'Port Elizabeth', 'province' => 'Eastern Cape', 'lat' => -33.9608, 'lng' => 25.6022],
            ['name' => 'Bloemfontein', 'province' => 'Free State', 'lat' => -29.0852, 'lng' => 26.1596],
            ['name' => 'East London', 'province' => 'Eastern Cape', 'lat' => -33.0153, 'lng' => 27.9116],
            ['name' => 'Pietermaritzburg', 'province' => 'KwaZulu-Natal', 'lat' => -29.6093, 'lng' => 30.3791],
            ['name' => 'Kimberley', 'province' => 'Northern Cape', 'lat' => -28.7282, 'lng' => 24.7499],
            ['name' => 'Polokwane', 'province' => 'Limpopo', 'lat' => -23.9045, 'lng' => 29.4689],
            ['name' => 'Nelspruit', 'province' => 'Mpumalanga', 'lat' => -25.4753, 'lng' => 30.9820],
            ['name' => 'Rustenburg', 'province' => 'North West', 'lat' => -25.6669, 'lng' => 27.2422],
        ];

        $propertyTypes = ['House', 'Apartment', 'Townhouse', 'Villa', 'Duplex', 'Penthouse', 'Studio'];
        $statuses = ['available', 'sold', 'rented', 'under_offer'];
        
        $city = $this->faker->randomElement($southAfricanCities);
        $type = $this->faker->randomElement($propertyTypes);
        
        // Generate realistic bedrooms/bathrooms based on property type
        $bedrooms = match($type) {
            'Studio' => 0,
            'Apartment' => $this->faker->numberBetween(1, 3),
            'Townhouse' => $this->faker->numberBetween(2, 4),
            'Villa', 'House' => $this->faker->numberBetween(2, 6),
            'Penthouse' => $this->faker->numberBetween(2, 5),
            'Duplex' => $this->faker->numberBetween(3, 5),
            default => $this->faker->numberBetween(1, 4)
        };
        
        $bathrooms = max(1, $bedrooms - $this->faker->numberBetween(0, 1));
        
        // Generate realistic pricing based on city and type
        $basePrice = match($city['name']) {
            'Cape Town' => $this->faker->numberBetween(1500000, 8000000),
            'Johannesburg' => $this->faker->numberBetween(1200000, 6000000),
            'Durban' => $this->faker->numberBetween(800000, 4000000),
            'Pretoria' => $this->faker->numberBetween(900000, 3500000),
            default => $this->faker->numberBetween(600000, 2500000)
        };
        
        $price = $basePrice + ($bedrooms * 200000) + ($type === 'Penthouse' ? 1000000 : 0);

        return [
            'title' => $this->faker->randomElement([
                'Stunning ' . $type . ' in ' . $city['name'],
                'Beautiful ' . $bedrooms . ' Bedroom ' . $type,
                'Modern ' . $type . ' with Sea Views',
                'Luxury ' . $type . ' in Prime Location',
                'Spacious ' . $type . ' with Garden',
                'Executive ' . $type . ' for Sale',
                'Family ' . $type . ' in Quiet Area',
            ]),
            'description' => $this->faker->paragraphs(3, true),
            'type' => $type,
            'price' => $price,
            'bedrooms' => $bedrooms,
            'bathrooms' => $bathrooms,
            'parking_spaces' => $this->faker->numberBetween(0, 4),
            'size_sqm' => $this->faker->numberBetween(50, 500),
            'address' => $this->faker->streetAddress(),
            'city' => $city['name'],
            'province' => $city['province'],
            'postal_code' => $this->faker->postcode(),
            'latitude' => $city['lat'] + $this->faker->randomFloat(4, -0.1, 0.1),
            'longitude' => $city['lng'] + $this->faker->randomFloat(4, -0.1, 0.1),
            'status' => $this->faker->randomElement($statuses),
            'is_featured' => $this->faker->boolean(20), // 20% chance of being featured
        ];
    }
}
