<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use LumenSistemas\Lgpd\Models\DataSubject;

uses(RefreshDatabase::class);

it('uses the configured table name', function (): void {
    expect(new DataSubject()->getTable())->toBe('data_subjects');
});

it('uses a custom table name from config', function (): void {
    config(['lgpd.tables.data_subjects' => 'custom_subjects']);

    expect(new DataSubject()->getTable())->toBe('custom_subjects');
});

it('has uuid primary key', function (): void {
    $subject = DataSubject::create(['document_hash' => '12345678900']);

    expect($subject->id)->toBeString();
    expect(mb_strlen((string) $subject->id))->toBe(36);
});

it('has fillable document_hash', function (): void {
    expect(new DataSubject()->getFillable())->toBe(['document_hash']);
});

it('hashes document_hash using blind index on create', function (): void {
    $subject = DataSubject::create(['document_hash' => '12345678900']);

    expect($subject->getRawOriginal('document_hash'))->not->toBe('12345678900');
});

it('supports soft deletes', function (): void {
    $subject = DataSubject::create(['document_hash' => '12345678900']);
    $subject->delete();

    expect(DataSubject::count())->toBe(0);
    expect(DataSubject::withTrashed()->count())->toBe(1);
    expect($subject->trashed())->toBeTrue();
});

it('has consents relationship', function (): void {
    $subject = DataSubject::create(['document_hash' => '12345678900']);

    expect($subject->consents())->toBeInstanceOf(Illuminate\Database\Eloquent\Relations\HasMany::class);
    expect($subject->consents)->toBeEmpty();
});

it('casts document_hash as blind index', function (): void {
    $casts = new DataSubject()->getCasts();

    expect($casts['document_hash'])->toBe(LumenSistemas\Encrypt\Casts\AsBlindIndex::class);
});

it('enforces unique document_hash', function (): void {
    DataSubject::create(['document_hash' => '12345678900']);

    expect(fn () => DataSubject::create(['document_hash' => '12345678900']))
        ->toThrow(Illuminate\Database\QueryException::class);
});
