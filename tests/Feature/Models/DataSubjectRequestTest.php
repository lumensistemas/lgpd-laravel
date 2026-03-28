<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use LumenSistemas\Lgpd\Enums\DataSubjectRight;
use LumenSistemas\Lgpd\Enums\RequestStatus;
use LumenSistemas\Lgpd\Models\DataSubject;
use LumenSistemas\Lgpd\Models\DataSubjectRequest;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->subject = DataSubject::create(['document_hash' => '12345678900']);
});

it('uses the configured table name', function (): void {
    expect(new DataSubjectRequest()->getTable())->toBe('data_subject_requests');
});

it('uses a custom table name from config', function (): void {
    config(['lgpd.tables.data_subject_requests' => 'custom_requests']);

    expect(new DataSubjectRequest()->getTable())->toBe('custom_requests');
});

it('has uuid primary key', function (): void {
    $request = DataSubjectRequest::create([
        'data_subject_id' => $this->subject->id,
        'right' => DataSubjectRight::ACCESS,
        'status' => RequestStatus::PENDING,
        'requested_at' => now(),
    ]);

    expect($request->id)->toBeString();
    expect(mb_strlen((string) $request->id))->toBe(36);
});

it('has the correct fillable attributes', function (): void {
    $fillable = new DataSubjectRequest()->getFillable();

    expect($fillable)->toContain('data_subject_id');
    expect($fillable)->toContain('right');
    expect($fillable)->toContain('status');
    expect($fillable)->toContain('requested_at');
    expect($fillable)->toContain('responded_at');
    expect($fillable)->toContain('response_notes');
});

it('casts right to DataSubjectRight enum', function (): void {
    $request = DataSubjectRequest::create([
        'data_subject_id' => $this->subject->id,
        'right' => DataSubjectRight::DELETION,
        'status' => RequestStatus::PENDING,
        'requested_at' => now(),
    ]);

    $request->refresh();

    expect($request->right)->toBe(DataSubjectRight::DELETION);
});

it('casts status to RequestStatus enum', function (): void {
    $request = DataSubjectRequest::create([
        'data_subject_id' => $this->subject->id,
        'right' => DataSubjectRight::ACCESS,
        'status' => RequestStatus::IN_PROGRESS,
        'requested_at' => now(),
    ]);

    $request->refresh();

    expect($request->status)->toBe(RequestStatus::IN_PROGRESS);
});

it('casts datetime fields', function (): void {
    $request = DataSubjectRequest::create([
        'data_subject_id' => $this->subject->id,
        'right' => DataSubjectRight::ACCESS,
        'status' => RequestStatus::COMPLETED,
        'requested_at' => '2025-01-01 10:00:00',
        'responded_at' => '2025-01-05 14:00:00',
    ]);

    $request->refresh();

    expect($request->requested_at)->toBeInstanceOf(Carbon\CarbonImmutable::class);
    expect($request->responded_at)->toBeInstanceOf(Carbon\CarbonImmutable::class);
});

it('belongs to a data subject', function (): void {
    $request = DataSubjectRequest::create([
        'data_subject_id' => $this->subject->id,
        'right' => DataSubjectRight::ACCESS,
        'status' => RequestStatus::PENDING,
        'requested_at' => now(),
    ]);

    expect($request->dataSubject)->toBeInstanceOf(DataSubject::class);
    expect($request->dataSubject->id)->toBe($this->subject->id);
});

it('supports soft deletes', function (): void {
    $request = DataSubjectRequest::create([
        'data_subject_id' => $this->subject->id,
        'right' => DataSubjectRight::ACCESS,
        'status' => RequestStatus::PENDING,
        'requested_at' => now(),
    ]);

    $request->delete();

    expect(DataSubjectRequest::count())->toBe(0);
    expect(DataSubjectRequest::withTrashed()->count())->toBe(1);
    expect($request->trashed())->toBeTrue();
});

it('has restrict on delete foreign key constraint', function (): void {
    DataSubjectRequest::create([
        'data_subject_id' => $this->subject->id,
        'right' => DataSubjectRight::ACCESS,
        'status' => RequestStatus::PENDING,
        'requested_at' => now(),
    ]);

    expect(fn () => $this->subject->forceDelete())
        ->toThrow(Illuminate\Database\QueryException::class);
});

it('stores nullable fields as null', function (): void {
    $request = DataSubjectRequest::create([
        'data_subject_id' => $this->subject->id,
        'right' => DataSubjectRight::ACCESS,
        'status' => RequestStatus::PENDING,
        'requested_at' => now(),
    ]);

    $request->refresh();

    expect($request->responded_at)->toBeNull();
    expect($request->response_notes)->toBeNull();
});

it('scopes pending requests', function (): void {
    DataSubjectRequest::create([
        'data_subject_id' => $this->subject->id,
        'right' => DataSubjectRight::ACCESS,
        'status' => RequestStatus::PENDING,
        'requested_at' => now(),
    ]);

    DataSubjectRequest::create([
        'data_subject_id' => $this->subject->id,
        'right' => DataSubjectRight::DELETION,
        'status' => RequestStatus::COMPLETED,
        'requested_at' => now(),
    ]);

    expect(DataSubjectRequest::pending()->count())->toBe(1);
});

it('scopes in progress requests', function (): void {
    DataSubjectRequest::create([
        'data_subject_id' => $this->subject->id,
        'right' => DataSubjectRight::ACCESS,
        'status' => RequestStatus::IN_PROGRESS,
        'requested_at' => now(),
    ]);

    expect(DataSubjectRequest::inProgress()->count())->toBe(1);
});

it('scopes completed requests', function (): void {
    DataSubjectRequest::create([
        'data_subject_id' => $this->subject->id,
        'right' => DataSubjectRight::ACCESS,
        'status' => RequestStatus::COMPLETED,
        'requested_at' => now(),
        'responded_at' => now(),
    ]);

    expect(DataSubjectRequest::completed()->count())->toBe(1);
});

it('scopes denied requests', function (): void {
    DataSubjectRequest::create([
        'data_subject_id' => $this->subject->id,
        'right' => DataSubjectRight::ACCESS,
        'status' => RequestStatus::DENIED,
        'requested_at' => now(),
        'responded_at' => now(),
    ]);

    expect(DataSubjectRequest::denied()->count())->toBe(1);
});

it('scopes overdue requests with default 15 days', function (): void {
    DataSubjectRequest::create([
        'data_subject_id' => $this->subject->id,
        'right' => DataSubjectRight::ACCESS,
        'status' => RequestStatus::PENDING,
        'requested_at' => now()->subDays(20),
    ]);

    DataSubjectRequest::create([
        'data_subject_id' => $this->subject->id,
        'right' => DataSubjectRight::DELETION,
        'status' => RequestStatus::PENDING,
        'requested_at' => now()->subDays(5),
    ]);

    expect(DataSubjectRequest::overdue()->count())->toBe(1);
});

it('scopes overdue requests with custom days', function (): void {
    DataSubjectRequest::create([
        'data_subject_id' => $this->subject->id,
        'right' => DataSubjectRight::ACCESS,
        'status' => RequestStatus::IN_PROGRESS,
        'requested_at' => now()->subDays(10),
    ]);

    expect(DataSubjectRequest::overdue(7)->count())->toBe(1);
    expect(DataSubjectRequest::overdue(14)->count())->toBe(0);
});

it('excludes completed and denied from overdue scope', function (): void {
    DataSubjectRequest::create([
        'data_subject_id' => $this->subject->id,
        'right' => DataSubjectRight::ACCESS,
        'status' => RequestStatus::COMPLETED,
        'requested_at' => now()->subDays(30),
        'responded_at' => now(),
    ]);

    expect(DataSubjectRequest::overdue()->count())->toBe(0);
});
