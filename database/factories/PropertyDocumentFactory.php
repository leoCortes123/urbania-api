<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Property;
use App\Models\PropertyDocument;
use App\Models\PropertyDocumentType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PropertyDocument>
 */
final class PropertyDocumentFactory extends Factory
{
    /** @var class-string<PropertyDocument> */
    protected $model = PropertyDocument::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'property_id' => Property::factory(),
            'property_document_type_id' => PropertyDocumentType::factory(),
            'name' => $this->faker->sentence(3),
            'file_url' => $this->faker->url(),
            'file_size_bytes' => $this->faker->optional()->numberBetween(1024, 10485760),
            'mime_type' => $this->faker->mimeType(),
            'notes' => $this->faker->optional()->sentence(),
            'uploaded_by_user_id' => User::factory(),
            'created_at' => now(),
        ];
    }
}
