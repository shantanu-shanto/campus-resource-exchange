<?php
// database/factories/ItemFactory.php
namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'availability_mode' => $this->faker->randomElement(['lend', 'sell', 'both']),
            'price' => $this->faker->randomFloat(2, 10, 500),
            'lending_duration_days' => $this->faker->numberBetween(3, 14),
            'status' => 'available',
            'pickup_location' => $this->faker->address(),
        ];
    }
}
