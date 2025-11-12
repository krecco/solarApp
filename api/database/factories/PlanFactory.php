<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Plan>
 */
class PlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $features = [
            'api_calls_per_hour' => fake()->randomElement([1000, 5000, 10000, 50000]),
            'export_enabled' => fake()->boolean(),
            'advanced_reports' => fake()->boolean(),
            'white_label' => fake()->boolean(),
            'ai_assistant' => fake()->boolean(),
            'custom_domain' => fake()->boolean(),
            'sso_enabled' => fake()->boolean(),
        ];

        $limits = [
            'max_users' => fake()->randomElement([5, 10, 25, 50, 100, -1]),
            'max_projects' => fake()->randomElement([10, 50, 100, 500, -1]),
            'storage_gb' => fake()->randomElement([10, 50, 100, 500, 1000]),
            'max_api_keys' => fake()->randomElement([3, 5, 10, 50, -1]),
        ];

        $priceMonthly = fake()->randomFloat(2, 9, 999);
        $priceYearly = $priceMonthly * 10; // 10x monthly for yearly

        return [
            'name' => fake()->words(2, true),
            'slug' => fake()->slug() . '-' . fake()->randomNumber(6),
            'price_monthly' => $priceMonthly,
            'price_yearly' => $priceYearly,
            'stripe_price_monthly_id' => 'price_' . fake()->randomNumber(8),
            'stripe_price_yearly_id' => 'price_' . fake()->randomNumber(8),
            'features' => $features,
            'limits' => $limits,
            'is_active' => true,
            'sort_order' => fake()->numberBetween(1, 10),
        ];
    }

    /**
     * Create an inactive plan.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Create a starter plan with basic features.
     */
    public function starter(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Test Starter',
            'slug' => 'test-starter-' . fake()->randomNumber(6),
            'price_monthly' => 29.00,
            'price_yearly' => 290.00,
            'features' => [
                'api_calls_per_hour' => 1000,
                'export_enabled' => true,
                'advanced_reports' => false,
                'white_label' => false,
                'ai_assistant' => false,
                'custom_domain' => false,
                'sso_enabled' => false,
            ],
            'limits' => [
                'max_users' => 5,
                'max_projects' => 10,
                'storage_gb' => 10,
                'max_api_keys' => 3,
            ],
            'sort_order' => 1,
        ]);
    }

    /**
     * Create an enterprise plan with all features.
     */
    public function enterprise(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Test Enterprise',
            'slug' => 'test-enterprise-' . fake()->randomNumber(6),
            'price_monthly' => 299.00,
            'price_yearly' => 2990.00,
            'features' => [
                'api_calls_per_hour' => 100000,
                'export_enabled' => true,
                'advanced_reports' => true,
                'white_label' => true,
                'ai_assistant' => true,
                'custom_domain' => true,
                'sso_enabled' => true,
            ],
            'limits' => [
                'max_users' => -1,
                'max_projects' => -1,
                'storage_gb' => 1000,
                'max_api_keys' => -1,
            ],
            'sort_order' => 3,
        ]);
    }

    /**
     * Create a plan with valid test Stripe price IDs.
     */
    public function withStripeIds(): static
    {
        return $this->state(fn (array $attributes) => [
            'stripe_price_monthly_id' => 'price_test_monthly_' . fake()->randomNumber(6),
            'stripe_price_yearly_id' => 'price_test_yearly_' . fake()->randomNumber(6),
        ]);
    }
}
