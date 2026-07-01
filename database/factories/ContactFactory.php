<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Contact;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contact>
 */
final class ContactFactory extends Factory
{
    protected $model = Contact::class;

    public function definition(): array
    {
        return [
            'document_type' => $this->faker->randomElement(['CC', 'NIT', 'CE', 'Pasaporte']),
            'document_number' => $this->faker->unique()->numerify('########'),
            'full_name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'emergency_contact_name' => $this->faker->optional()->name(),
            'emergency_contact_phone' => $this->faker->optional()->phoneNumber(),
            'notes' => $this->faker->optional()->sentence(),
            'organization_id' => Organization::factory(),
        ];
    }
}
