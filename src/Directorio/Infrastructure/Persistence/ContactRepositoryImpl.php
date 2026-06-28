<?php

declare(strict_types=1);

namespace Directorio\Infrastructure\Persistence;

use App\Models\Contact as EloquentContact;
use Directorio\Domain\Entities\Contact;
use Directorio\Domain\Repositories\ContactRepository;
use Directorio\Infrastructure\Mappers\ContactMapper;

class ContactRepositoryImpl implements ContactRepository
{
    /**
     * @param  array<string, mixed>  $filters
     * @return Contact[]
     */
    public function findAll(array $filters = []): array
    {
        $query = EloquentContact::query();

        if (! empty($filters['full_name'])) {
            $query->where('full_name', 'ilike', '%'.self::stringValue($filters['full_name']).'%');
        }
        if (! empty($filters['document_type'])) {
            $query->where('document_type', $filters['document_type']);
        }
        if (! empty($filters['document_number'])) {
            $query->where('document_number', $filters['document_number']);
        }
        if (! empty($filters['email'])) {
            $query->where('email', 'ilike', '%'.self::stringValue($filters['email']).'%');
        }
        if (! empty($filters['phone'])) {
            $query->where('phone', 'ilike', '%'.self::stringValue($filters['phone']).'%');
        }

        $models = $query->orderBy('full_name')->get();

        return ContactMapper::toDomainArray($models->all());
    }

    public function findById(string $id): ?Contact
    {
        $model = EloquentContact::find($id);

        return $model ? ContactMapper::toDomain($model) : null;
    }

    public function findByDocument(string $documentType, string $documentNumber): ?Contact
    {
        $model = EloquentContact::where('document_type', $documentType)
            ->where('document_number', $documentNumber)
            ->first();

        return $model ? ContactMapper::toDomain($model) : null;
    }

    public function findByUserId(string $userId): ?Contact
    {
        $model = EloquentContact::where('user_id', $userId)->first();

        return $model ? ContactMapper::toDomain($model) : null;
    }

    public function save(Contact $contact): Contact
    {
        $data = ContactMapper::toPersistence($contact);
        EloquentContact::create($data);

        return $contact;
    }

    public function update(Contact $contact): Contact
    {
        $data = ContactMapper::toPersistence($contact);
        EloquentContact::where('id', $contact->id())->update($data);

        return $contact;
    }

    public function delete(string $id): void
    {
        EloquentContact::where('id', $id)->delete();
    }

    public function count(): int
    {
        return EloquentContact::count();
    }

    private static function stringValue(mixed $value): string
    {
        return is_string($value) ? $value : (is_scalar($value) ? (string) $value : '');
    }
}
