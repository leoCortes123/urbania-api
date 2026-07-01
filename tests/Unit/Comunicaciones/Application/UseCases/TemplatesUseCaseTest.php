<?php

declare(strict_types=1);

namespace Tests\Unit\Comunicaciones\Application\UseCases;

use Mockery;
use Tests\TestCase;
use Urbania\Comunicaciones\Application\DTOs\CreateTemplateDto;
use Urbania\Comunicaciones\Application\DTOs\UpdateTemplateDto;
use Urbania\Comunicaciones\Application\UseCases\Templates\CreateTemplateUseCase;
use Urbania\Comunicaciones\Application\UseCases\Templates\DeleteTemplateUseCase;
use Urbania\Comunicaciones\Application\UseCases\Templates\ListTemplatesUseCase;
use Urbania\Comunicaciones\Application\UseCases\Templates\UpdateTemplateUseCase;
use Urbania\Comunicaciones\Domain\Entities\MessageTemplateEntity;
use Urbania\Comunicaciones\Domain\Exceptions\TemplateNotFoundException;
use Urbania\Comunicaciones\Domain\Repositories\MessageTemplateRepositoryInterface;
use Urbania\Shared\Domain\ValueObjects\Uuid;

uses(TestCase::class);

function createTemplateEntity(array $overrides = []): MessageTemplateEntity
{
    return MessageTemplateEntity::create(
        condominiumId: $overrides['condominiumId'] ?? Uuid::v7(),
        nombre: $overrides['nombre'] ?? 'Plantilla por defecto',
        tipo: $overrides['tipo'] ?? 'anuncio',
        cuerpo: $overrides['cuerpo'] ?? 'Cuerpo de la plantilla',
    );
}

beforeEach(function (): void {
    $this->templateRepository = Mockery::mock(MessageTemplateRepositoryInterface::class);
});

afterEach(function (): void {
    Mockery::close();
});

describe('CreateTemplateUseCase', function (): void {
    it('creates a template and returns TemplateDto', function (): void {
        $condominiumId = Uuid::v7();
        $useCase = new CreateTemplateUseCase($this->templateRepository);

        $this->templateRepository->shouldReceive('save')
            ->once()
            ->with(Mockery::type(MessageTemplateEntity::class));

        $dto = new CreateTemplateDto(
            condominiumId: $condominiumId,
            nombre: 'Plantilla de bienvenida',
            tipo: 'anuncio',
            cuerpo: 'Hola {{nombre}}, bienvenido.',
        );

        $result = $useCase->execute($dto);

        expect($result->nombre)->toBe('Plantilla de bienvenida')
            ->and($result->tipo)->toBe('anuncio')
            ->and($result->cuerpo)->toBe('Hola {{nombre}}, bienvenido.');
    });

    it('creates a template with null tipo', function (): void {
        $condominiumId = Uuid::v7();
        $useCase = new CreateTemplateUseCase($this->templateRepository);

        $this->templateRepository->shouldReceive('save')
            ->once()
            ->with(Mockery::type(MessageTemplateEntity::class));

        $dto = new CreateTemplateDto(
            condominiumId: $condominiumId,
            nombre: 'Plantilla genérica',
            tipo: null,
            cuerpo: 'Cuerpo genérico',
        );

        $result = $useCase->execute($dto);

        expect($result->nombre)->toBe('Plantilla genérica')
            ->and($result->tipo)->toBeNull()
            ->and($result->cuerpo)->toBe('Cuerpo genérico');
    });
});

describe('ListTemplatesUseCase', function (): void {
    it('returns a paginated list of templates', function (): void {
        $condominiumId = Uuid::v7();
        $template = createTemplateEntity(['condominiumId' => $condominiumId]);
        $useCase = new ListTemplatesUseCase($this->templateRepository);

        $this->templateRepository->shouldReceive('findByCondominiumId')
            ->once()
            ->with(Mockery::type(Uuid::class), [], 1, 20)
            ->andReturn([
                'items' => [$template],
                'total' => 1,
                'page' => 1,
                'perPage' => 20,
                'lastPage' => 1,
            ]);

        $result = $useCase->execute($condominiumId, [], 1, 20);

        expect($result['items'])->toHaveCount(1)
            ->and($result['total'])->toBe(1)
            ->and($result['page'])->toBe(1)
            ->and($result['perPage'])->toBe(20)
            ->and($result['lastPage'])->toBe(1)
            ->and($result['items'][0]->id)->toBe($template->id()->toString())
            ->and($result['items'][0]->nombre)->toBe($template->nombre())
            ->and($result['items'][0]->tipo)->toBe($template->tipo());
    });

    it('passes filters to the repository', function (): void {
        $condominiumId = Uuid::v7();
        $useCase = new ListTemplatesUseCase($this->templateRepository);

        $this->templateRepository->shouldReceive('findByCondominiumId')
            ->once()
            ->with(Mockery::type(Uuid::class), ['tipo' => 'anuncio'], 2, 5)
            ->andReturn([
                'items' => [],
                'total' => 0,
                'page' => 2,
                'perPage' => 5,
                'lastPage' => 1,
            ]);

        $result = $useCase->execute($condominiumId, ['tipo' => 'anuncio'], 2, 5);

        expect($result['items'])->toBeEmpty()
            ->and($result['total'])->toBe(0);
    });
});

describe('UpdateTemplateUseCase', function (): void {
    it('updates an existing template', function (): void {
        $template = createTemplateEntity();
        $useCase = new UpdateTemplateUseCase($this->templateRepository);

        $this->templateRepository->shouldReceive('findById')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn($template);

        $this->templateRepository->shouldReceive('save')
            ->once()
            ->with(Mockery::type(MessageTemplateEntity::class));

        $dto = new UpdateTemplateDto(
            nombre: 'Nuevo nombre',
            tipo: 'encuesta',
            cuerpo: 'Nuevo cuerpo',
        );

        $result = $useCase->execute($template->id(), $dto);

        expect($result->id)->toBe($template->id()->toString())
            ->and($result->nombre)->toBe('Nuevo nombre')
            ->and($result->tipo)->toBe('encuesta')
            ->and($result->cuerpo)->toBe('Nuevo cuerpo');
    });

    it('updates only nombre on partial update', function (): void {
        $template = createTemplateEntity([
            'nombre' => 'Original',
            'tipo' => 'anuncio',
            'cuerpo' => 'Cuerpo original',
        ]);
        $useCase = new UpdateTemplateUseCase($this->templateRepository);

        $this->templateRepository->shouldReceive('findById')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn($template);

        $this->templateRepository->shouldReceive('save')
            ->once()
            ->with(Mockery::type(MessageTemplateEntity::class));

        $dto = new UpdateTemplateDto(
            nombre: 'Solo nombre cambiado',
            tipo: null,
            cuerpo: null,
        );

        $result = $useCase->execute($template->id(), $dto);

        expect($result->nombre)->toBe('Solo nombre cambiado')
            ->and($result->tipo)->toBe('anuncio')      // preserved
            ->and($result->cuerpo)->toBe('Cuerpo original'); // preserved
    });

    it('updates only cuerpo on partial update', function (): void {
        $template = createTemplateEntity([
            'nombre' => 'Plantilla X',
            'tipo' => 'anuncio',
            'cuerpo' => 'Cuerpo antiguo',
        ]);
        $useCase = new UpdateTemplateUseCase($this->templateRepository);

        $this->templateRepository->shouldReceive('findById')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn($template);

        $this->templateRepository->shouldReceive('save')
            ->once()
            ->with(Mockery::type(MessageTemplateEntity::class));

        $dto = new UpdateTemplateDto(
            nombre: null,
            tipo: null,
            cuerpo: 'Solo cuerpo cambiado',
        );

        $result = $useCase->execute($template->id(), $dto);

        expect($result->nombre)->toBe('Plantilla X')       // preserved
            ->and($result->tipo)->toBe('anuncio')          // preserved
            ->and($result->cuerpo)->toBe('Solo cuerpo cambiado');
    });

    it('throws TemplateNotFoundException when template does not exist', function (): void {
        $useCase = new UpdateTemplateUseCase($this->templateRepository);

        $this->templateRepository->shouldReceive('findById')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn(null);

        $dto = new UpdateTemplateDto(
            nombre: 'Test',
            tipo: null,
            cuerpo: null,
        );

        $useCase->execute(Uuid::v7(), $dto);
    })->throws(TemplateNotFoundException::class);
});

describe('DeleteTemplateUseCase', function (): void {
    it('deletes an existing template', function (): void {
        $template = createTemplateEntity();
        $useCase = new DeleteTemplateUseCase($this->templateRepository);

        $this->templateRepository->shouldReceive('findById')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn($template);

        $this->templateRepository->shouldReceive('delete')
            ->once()
            ->with(Mockery::type(Uuid::class));

        $useCase->execute($template->id());

        // If no exception is thrown, the test passes
        expect(true)->toBeTrue();
    });

    it('throws TemplateNotFoundException when template does not exist', function (): void {
        $useCase = new DeleteTemplateUseCase($this->templateRepository);

        $this->templateRepository->shouldReceive('findById')
            ->once()
            ->with(Mockery::type(Uuid::class))
            ->andReturn(null);

        $useCase->execute(Uuid::v7());
    })->throws(TemplateNotFoundException::class);
});
