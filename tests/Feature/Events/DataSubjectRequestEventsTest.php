<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use LumenSistemas\Lgpd\Enums\DataSubjectRight;
use LumenSistemas\Lgpd\Enums\RequestStatus;
use LumenSistemas\Lgpd\Events\DataSubjectRequestCompleted;
use LumenSistemas\Lgpd\Events\DataSubjectRequestCreated;
use LumenSistemas\Lgpd\Models\DataSubject;
use LumenSistemas\Lgpd\Models\DataSubjectRequest;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->subject = DataSubject::create(['document_hash' => '12345678900']);
});

it('dispatches DataSubjectRequestCreated when request is created', function (): void {
    Event::fake([DataSubjectRequestCreated::class]);

    $request = DataSubjectRequest::create([
        'data_subject_id' => $this->subject->id,
        'right' => DataSubjectRight::ACCESS,
        'status' => RequestStatus::PENDING,
        'requested_at' => now(),
    ]);

    Event::assertDispatched(DataSubjectRequestCreated::class, fn (DataSubjectRequestCreated $event): bool => $event->request->id === $request->id);
});

it('dispatches DataSubjectRequestCompleted when status changes to completed', function (): void {
    Event::fake([DataSubjectRequestCompleted::class]);

    $request = DataSubjectRequest::create([
        'data_subject_id' => $this->subject->id,
        'right' => DataSubjectRight::ACCESS,
        'status' => RequestStatus::PENDING,
        'requested_at' => now(),
    ]);

    $request->update([
        'status' => RequestStatus::COMPLETED,
        'responded_at' => now(),
    ]);

    Event::assertDispatched(DataSubjectRequestCompleted::class, fn (DataSubjectRequestCompleted $event): bool => $event->request->id === $request->id);
});

it('dispatches DataSubjectRequestCompleted when status changes to denied', function (): void {
    Event::fake([DataSubjectRequestCompleted::class]);

    $request = DataSubjectRequest::create([
        'data_subject_id' => $this->subject->id,
        'right' => DataSubjectRight::DELETION,
        'status' => RequestStatus::PENDING,
        'requested_at' => now(),
    ]);

    $request->update([
        'status' => RequestStatus::DENIED,
        'responded_at' => now(),
        'response_notes' => 'Identity verification failed.',
    ]);

    Event::assertDispatched(DataSubjectRequestCompleted::class);
});

it('does not dispatch DataSubjectRequestCompleted when status changes to in_progress', function (): void {
    Event::fake([DataSubjectRequestCompleted::class]);

    $request = DataSubjectRequest::create([
        'data_subject_id' => $this->subject->id,
        'right' => DataSubjectRight::ACCESS,
        'status' => RequestStatus::PENDING,
        'requested_at' => now(),
    ]);

    $request->update(['status' => RequestStatus::IN_PROGRESS]);

    Event::assertNotDispatched(DataSubjectRequestCompleted::class);
});
