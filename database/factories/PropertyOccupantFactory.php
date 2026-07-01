<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Contact;
use App\Models\OccupantType;
use App\Models\Property;
use App\Models\PropertyOccupant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PropertyOccupant>
 */
final class PropertyOccupantFactory extends Factory
{
    protected $model = PropertyOccupant::class;

    public function definition(): array
    {
        return [
            'property_id' => Property::factory(),
            'contact_id' => Contact::factory(),
            'occupant_type_id' => OccupantType::factory(),
            'is_primary' => false,
            'is_active' => true,
            'move_in_date' => $this->faker->optional()->date(),
            'move_out_date' => null,
        ];
    }
}
