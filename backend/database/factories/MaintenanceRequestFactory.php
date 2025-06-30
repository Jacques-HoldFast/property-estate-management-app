<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MaintenanceRequest>
 */
class MaintenanceRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = ['plumbing', 'electrical', 'hvac', 'appliances', 'structural', 'painting', 'landscaping', 'security', 'cleaning', 'other'];
        $priorities = ['low', 'medium', 'high', 'urgent'];
        $statuses = ['pending', 'assigned', 'in_progress', 'on_hold', 'completed', 'cancelled'];
        
        $category = $this->faker->randomElement($categories);
        $priority = $this->faker->randomElement($priorities);
        $status = $this->faker->randomElement($statuses);
        
        $reportedAt = $this->faker->dateTimeBetween('-6 months', 'now');
        $assignedAt = $status !== 'pending' ? $this->faker->dateTimeBetween($reportedAt, 'now') : null;
        $startedAt = in_array($status, ['in_progress', 'on_hold', 'completed']) ? 
            $this->faker->dateTimeBetween($assignedAt ?? $reportedAt, 'now') : null;
        $completedAt = $status === 'completed' ? 
            $this->faker->dateTimeBetween($startedAt ?? $assignedAt ?? $reportedAt, 'now') : null;

        $titles = [
            'plumbing' => ['Leaking tap', 'Blocked drain', 'Low water pressure', 'Toilet not flushing', 'Hot water issue'],
            'electrical' => ['Power outlet not working', 'Light switch broken', 'Circuit breaker tripping', 'Flickering lights', 'No electricity in room'],
            'hvac' => ['Air conditioning not cooling', 'Heater not working', 'Strange noise from AC', 'Poor air circulation', 'Thermostat malfunction'],
            'appliances' => ['Refrigerator not cooling', 'Washing machine not working', 'Oven temperature issue', 'Dishwasher leaking', 'Microwave not heating'],
            'structural' => ['Cracked wall', 'Loose floorboard', 'Door not closing properly', 'Window won\'t open', 'Ceiling leak'],
            'painting' => ['Wall needs repainting', 'Peeling paint', 'Scratch on wall', 'Color touch-up needed', 'Interior painting required'],
            'landscaping' => ['Grass needs cutting', 'Tree pruning required', 'Garden maintenance', 'Irrigation system issue', 'Weed removal'],
            'security' => ['Lock replacement needed', 'Security system malfunction', 'Gate not working', 'Key duplication', 'Alarm system issue'],
            'cleaning' => ['Deep cleaning required', 'Carpet stain removal', 'Window cleaning', 'Mold removal', 'General maintenance cleaning'],
            'other' => ['General maintenance', 'Pest control needed', 'Furniture repair', 'Miscellaneous issue', 'Property inspection needed']
        ];

        $contractors = [
            'John Smith Plumbing', 'Electric Solutions SA', 'CoolAir HVAC Services', 'Fix-It Maintenance',
            'Property Care SA', 'Home Repair Pros', 'Maintenance Masters', 'Quick Fix Services',
            'Professional Property Care', 'Reliable Repairs', 'Expert Maintenance', 'Quality Service Co'
        ];

        return [
            'title' => $this->faker->randomElement($titles[$category]),
            'description' => $this->faker->sentences(3, true),
            'category' => $category,
            'priority' => $priority,
            'status' => $status,
            'estimated_cost' => $this->faker->randomFloat(2, 100, 5000),
            'actual_cost' => $status === 'completed' ? $this->faker->randomFloat(2, 100, 5000) : null,
            'assigned_to' => $status !== 'pending' ? $this->faker->randomElement($contractors) : null,
            'notes' => $this->faker->optional(0.7)->sentences(2, true),
            'reported_at' => $reportedAt,
            'assigned_at' => $assignedAt,
            'started_at' => $startedAt,
            'completed_at' => $completedAt,
        ];
    }
}
