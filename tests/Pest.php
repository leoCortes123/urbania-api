<?php

declare(strict_types=1);
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

uses(TestCase::class)->in('Feature', 'Integration');
uses(LazilyRefreshDatabase::class)->in('Feature', 'Integration');
