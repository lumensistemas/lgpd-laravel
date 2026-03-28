<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use LumenSistemas\Lgpd\Enums\LegalBasis;
use LumenSistemas\Lgpd\Events\ConsentGranted;
use LumenSistemas\Lgpd\Events\ConsentRevoked;
use LumenSistemas\Lgpd\Models\Consent;
use LumenSistemas\Lgpd\Models\DataSubject;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->subject = DataSubject::create(['document_hash' => '12345678900']);
});

it('dispatches ConsentGranted when consent is created', function (): void {
    Event::fake([ConsentGranted::class]);

    $consent = Consent::create([
        'data_subject_id' => $this->subject->id,
        'purpose' => 'Marketing emails',
        'legal_basis' => LegalBasis::CONSENT,
        'granted_at' => now(),
    ]);

    Event::assertDispatched(ConsentGranted::class, fn (ConsentGranted $event): bool => $event->consent->id === $consent->id);
});

it('dispatches ConsentRevoked when revoked_at is set', function (): void {
    Event::fake([ConsentRevoked::class]);

    $consent = Consent::create([
        'data_subject_id' => $this->subject->id,
        'purpose' => 'Marketing emails',
        'legal_basis' => LegalBasis::CONSENT,
        'granted_at' => now(),
    ]);

    $consent->update(['revoked_at' => now()]);

    Event::assertDispatched(ConsentRevoked::class, fn (ConsentRevoked $event): bool => $event->consent->id === $consent->id);
});

it('does not dispatch ConsentRevoked on unrelated updates', function (): void {
    Event::fake([ConsentRevoked::class]);

    $consent = Consent::create([
        'data_subject_id' => $this->subject->id,
        'purpose' => 'Marketing emails',
        'legal_basis' => LegalBasis::CONSENT,
        'granted_at' => now(),
    ]);

    $consent->update(['purpose' => 'Updated purpose']);

    Event::assertNotDispatched(ConsentRevoked::class);
});
