<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_number' => 'ORD-' . $this->faker->unique()->numerify('######'),
            'type' => $this->faker->randomElement(['dine-in', 'takeaway', 'online']),
            'status' => $this->faker->randomElement(['pending', 'preparing', 'prepared', 'completed']),
            'payment_status' => $this->faker->randomElement(['unpaid', 'paid', 'refunded']),
            'payment_method' => $this->faker->randomElement(['cash', 'card', 'wallet']),
            'total_amount' => $this->faker->randomFloat(2, 10, 100),
            'tax_amount' => $this->faker->randomFloat(2, 1, 13),
            'grand_total' => $this->faker->randomFloat(2, 11, 113),
            'guest_name' => $this->faker->optional()->name(),
            'guest_email' => $this->faker->optional()->email(),
            'shipping_address' => $this->faker->optional()->address(),
            'billing_address' => $this->faker->optional()->address(),
            'user_id' => User::factory(),
            'created_by' => User::factory(),
        ];
    }

    /**
     * Indicate that the order is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);
    }

    /**
     * Indicate that the order is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'payment_status' => 'paid',
        ]);
    }

    /**
     * Indicate that the order is for dine-in.
     */
    public function dineIn(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'dine-in',
        ]);
    }

    /**
     * Indicate that the order is for takeaway.
     */
    public function takeaway(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'takeaway',
            'table_id' => null,
        ]);
    }
}