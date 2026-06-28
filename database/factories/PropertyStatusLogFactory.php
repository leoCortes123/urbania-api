<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Property;
use App\Models\PropertyStatus;
use App\Models\PropertyStatusLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PropertyStatusLog>
 */
final class PropertyStatusLogFactory extends Factory
{
    /** @var class-string<PropertyStatusLog> */
    protected $model = PropertyStatusLog::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'property_id' => Property::factory(),
            'from_status_id' => PropertyStatus::factory(),
            'to_status_id' => PropertyStatus::factory(),
            'changed_by_user_id' => User::factory(),
            'reason' => $this->faker->sentence(),
            'created_at' => now(),
        ];
    }
}
