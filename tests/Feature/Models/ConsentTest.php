<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use LumenSistemas\Lgpd\Enums\LegalBasis;
use LumenSistemas\Lgpd\Models\Consent;
use LumenSistemas\Lgpd\Models\DataSubject;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->subject = DataSubject::create(['document_hash' => '12345678900']);
});

it('uses the configured table name', function (): void {
    expect(new Consent()->getTable())->toBe('consents');
});

it('uses a custom table name from config', function (): void {
    config(['lgpd.tables.consents' => 'custom_consents']);

    expect(new Consent()->getTable())->toBe('custom_consents');
});

it('has uuid primary key', function (): void {
    $consent = Consent::create([
        'data_subject_id' => $this->subject->id,
        'purpose' => 'Marketing emails',
        'legal_basis' => LegalBasis::CONSENT,
        'granted_at' => now(),
    ]);

    expect($consent->id)->toBeString();
    expect(mb_strlen((string) $consent->id))->toBe(36);
});

it('has the correct fillable attributes', function (): void {
    $fillable = new Consent()->getFillable();

    expect($fillable)->toContain('data_subject_id');
    expect($fillable)->toContain('purpose');
    expect($fillable)->toContain('legal_basis');
    expect($fillable)->toContain('granted_at');
    expect($fillable)->toContain('revoked_at');
    expect($fillable)->toContain('expires_at');
    expect($fillable)->toContain('ip_address');
    expect($fillable)->toContain('user_agent');
    expect($fillable)->toContain('metadata');
});

it('casts legal_basis to LegalBasis enum', function (): void {
    $consent = Consent::create([
        'data_subject_id' => $this->subject->id,
        'purpose' => 'Marketing emails',
        'legal_basis' => LegalBasis::CONSENT,
        'granted_at' => now(),
    ]);

    $consent->refresh();

    expect($consent->legal_basis)->toBe(LegalBasis::CONSENT);
});

it('casts datetime fields', function (): void {
    $consent = Consent::create([
        'data_subject_id' => $this->subject->id,
        'purpose' => 'Marketing emails',
        'legal_basis' => LegalBasis::CONSENT,
        'granted_at' => '2025-01-01 12:00:00',
        'revoked_at' => '2025-06-01 12:00:00',
        'expires_at' => '2026-01-01 12:00:00',
    ]);

    $consent->refresh();

    expect($consent->granted_at)->toBeInstanceOf(Carbon\CarbonImmutable::class);
    expect($consent->revoked_at)->toBeInstanceOf(Carbon\CarbonImmutable::class);
    expect($consent->expires_at)->toBeInstanceOf(Carbon\CarbonImmutable::class);
});

it('casts metadata to array', function (): void {
    $consent = Consent::create([
        'data_subject_id' => $this->subject->id,
        'purpose' => 'Marketing emails',
        'legal_basis' => LegalBasis::CONSENT,
        'granted_at' => now(),
        'metadata' => ['source' => 'web', 'version' => '1.0'],
    ]);

    $consent->refresh();

    expect($consent->metadata)->toBeArray();
    expect($consent->metadata['source'])->toBe('web');
});

it('belongs to a data subject', function (): void {
    $consent = Consent::create([
        'data_subject_id' => $this->subject->id,
        'purpose' => 'Marketing emails',
        'legal_basis' => LegalBasis::CONSENT,
        'granted_at' => now(),
    ]);

    expect($consent->dataSubject)->toBeInstanceOf(DataSubject::class);
    expect($consent->dataSubject->id)->toBe($this->subject->id);
});

it('supports soft deletes', function (): void {
    $consent = Consent::create([
        'data_subject_id' => $this->subject->id,
        'purpose' => 'Marketing emails',
        'legal_basis' => LegalBasis::CONSENT,
        'granted_at' => now(),
    ]);

    $consent->delete();

    expect(Consent::count())->toBe(0);
    expect(Consent::withTrashed()->count())->toBe(1);
    expect($consent->trashed())->toBeTrue();
});

it('has restrict on delete foreign key constraint', function (): void {
    Consent::create([
        'data_subject_id' => $this->subject->id,
        'purpose' => 'Marketing emails',
        'legal_basis' => LegalBasis::CONSENT,
        'granted_at' => now(),
    ]);

    expect(fn () => $this->subject->forceDelete())
        ->toThrow(Illuminate\Database\QueryException::class);
});

it('stores nullable fields as null', function (): void {
    $consent = Consent::create([
        'data_subject_id' => $this->subject->id,
        'purpose' => 'Marketing emails',
        'legal_basis' => LegalBasis::CONSENT,
        'granted_at' => now(),
    ]);

    $consent->refresh();

    expect($consent->revoked_at)->toBeNull();
    expect($consent->expires_at)->toBeNull();
    expect($consent->ip_address)->toBeNull();
    expect($consent->user_agent)->toBeNull();
    expect($consent->metadata)->toBeNull();
});

it('scopes active consents', function (): void {
    Consent::create([
        'data_subject_id' => $this->subject->id,
        'purpose' => 'Active consent',
        'legal_basis' => LegalBasis::CONSENT,
        'granted_at' => now(),
    ]);

    Consent::create([
        'data_subject_id' => $this->subject->id,
        'purpose' => 'Revoked consent',
        'legal_basis' => LegalBasis::CONSENT,
        'granted_at' => now(),
        'revoked_at' => now(),
    ]);

    Consent::create([
        'data_subject_id' => $this->subject->id,
        'purpose' => 'Expired consent',
        'legal_basis' => LegalBasis::CONSENT,
        'granted_at' => now(),
        'expires_at' => now()->subDay(),
    ]);

    expect(Consent::active()->count())->toBe(1);
});

it('includes consents with future expiry in active scope', function (): void {
    Consent::create([
        'data_subject_id' => $this->subject->id,
        'purpose' => 'Future expiry',
        'legal_basis' => LegalBasis::CONSENT,
        'granted_at' => now(),
        'expires_at' => now()->addYear(),
    ]);

    expect(Consent::active()->count())->toBe(1);
});

it('scopes revoked consents', function (): void {
    Consent::create([
        'data_subject_id' => $this->subject->id,
        'purpose' => 'Active',
        'legal_basis' => LegalBasis::CONSENT,
        'granted_at' => now(),
    ]);

    Consent::create([
        'data_subject_id' => $this->subject->id,
        'purpose' => 'Revoked',
        'legal_basis' => LegalBasis::CONSENT,
        'granted_at' => now(),
        'revoked_at' => now(),
    ]);

    expect(Consent::revoked()->count())->toBe(1);
});

it('scopes expired consents', function (): void {
    Consent::create([
        'data_subject_id' => $this->subject->id,
        'purpose' => 'Active',
        'legal_basis' => LegalBasis::CONSENT,
        'granted_at' => now(),
        'expires_at' => now()->addYear(),
    ]);

    Consent::create([
        'data_subject_id' => $this->subject->id,
        'purpose' => 'Expired',
        'legal_basis' => LegalBasis::CONSENT,
        'granted_at' => now(),
        'expires_at' => now()->subDay(),
    ]);

    expect(Consent::expired()->count())->toBe(1);
});
