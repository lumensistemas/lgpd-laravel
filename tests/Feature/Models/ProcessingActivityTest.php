<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use LumenSistemas\Lgpd\Enums\DataSensitivity;
use LumenSistemas\Lgpd\Enums\LegalBasis;
use LumenSistemas\Lgpd\Models\DataSubject;
use LumenSistemas\Lgpd\Models\ProcessingActivity;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->subject = DataSubject::create(['document_hash' => '12345678900']);
});

it('uses the configured table name', function (): void {
    expect(new ProcessingActivity()->getTable())->toBe('processing_activities');
});

it('uses a custom table name from config', function (): void {
    config(['lgpd.tables.processing_activities' => 'custom_activities']);

    expect(new ProcessingActivity()->getTable())->toBe('custom_activities');
});

it('has uuid primary key', function (): void {
    $activity = ProcessingActivity::create([
        'data_subject_id' => $this->subject->id,
        'activity' => 'user_registration',
        'legal_basis' => LegalBasis::CONTRACT,
        'sensitivity' => DataSensitivity::PERSONAL,
        'purpose' => 'Create account',
        'processed_at' => now(),
    ]);

    expect($activity->id)->toBeString();
    expect(mb_strlen((string) $activity->id))->toBe(36);
});

it('has the correct fillable attributes', function (): void {
    $fillable = new ProcessingActivity()->getFillable();

    expect($fillable)->toContain('data_subject_id');
    expect($fillable)->toContain('activity');
    expect($fillable)->toContain('legal_basis');
    expect($fillable)->toContain('sensitivity');
    expect($fillable)->toContain('purpose');
    expect($fillable)->toContain('data_categories');
    expect($fillable)->toContain('retention_period');
    expect($fillable)->toContain('processed_at');
});

it('casts legal_basis to LegalBasis enum', function (): void {
    $activity = ProcessingActivity::create([
        'data_subject_id' => $this->subject->id,
        'activity' => 'user_registration',
        'legal_basis' => LegalBasis::CONTRACT,
        'sensitivity' => DataSensitivity::PERSONAL,
        'purpose' => 'Create account',
        'processed_at' => now(),
    ]);

    $activity->refresh();

    expect($activity->legal_basis)->toBe(LegalBasis::CONTRACT);
});

it('casts sensitivity to DataSensitivity enum', function (): void {
    $activity = ProcessingActivity::create([
        'data_subject_id' => $this->subject->id,
        'activity' => 'medical_record_access',
        'legal_basis' => LegalBasis::HEALTH,
        'sensitivity' => DataSensitivity::SENSITIVE,
        'purpose' => 'Access patient records',
        'processed_at' => now(),
    ]);

    $activity->refresh();

    expect($activity->sensitivity)->toBe(DataSensitivity::SENSITIVE);
});

it('casts data_categories to array', function (): void {
    $activity = ProcessingActivity::create([
        'data_subject_id' => $this->subject->id,
        'activity' => 'user_registration',
        'legal_basis' => LegalBasis::CONTRACT,
        'sensitivity' => DataSensitivity::PERSONAL,
        'purpose' => 'Create account',
        'data_categories' => ['name', 'email', 'cpf'],
        'processed_at' => now(),
    ]);

    $activity->refresh();

    expect($activity->data_categories)->toBeArray();
    expect($activity->data_categories)->toBe(['name', 'email', 'cpf']);
});

it('casts processed_at to datetime', function (): void {
    $activity = ProcessingActivity::create([
        'data_subject_id' => $this->subject->id,
        'activity' => 'user_registration',
        'legal_basis' => LegalBasis::CONTRACT,
        'sensitivity' => DataSensitivity::PERSONAL,
        'purpose' => 'Create account',
        'processed_at' => '2025-06-15 10:30:00',
    ]);

    $activity->refresh();

    expect($activity->processed_at)->toBeInstanceOf(Carbon\Carbon::class);
});

it('belongs to a data subject', function (): void {
    $activity = ProcessingActivity::create([
        'data_subject_id' => $this->subject->id,
        'activity' => 'user_registration',
        'legal_basis' => LegalBasis::CONTRACT,
        'sensitivity' => DataSensitivity::PERSONAL,
        'purpose' => 'Create account',
        'processed_at' => now(),
    ]);

    expect($activity->dataSubject)->toBeInstanceOf(DataSubject::class);
    expect($activity->dataSubject->id)->toBe($this->subject->id);
});

it('allows nullable data_subject_id', function (): void {
    $activity = ProcessingActivity::create([
        'data_subject_id' => null,
        'activity' => 'anonymized_analytics',
        'legal_basis' => LegalBasis::LEGITIMATE_INTEREST,
        'sensitivity' => DataSensitivity::INTERNAL,
        'purpose' => 'Aggregate usage statistics',
        'processed_at' => now(),
    ]);

    expect($activity->data_subject_id)->toBeNull();
    expect($activity->dataSubject)->toBeNull();
});

it('stores nullable fields as null', function (): void {
    $activity = ProcessingActivity::create([
        'data_subject_id' => $this->subject->id,
        'activity' => 'user_registration',
        'legal_basis' => LegalBasis::CONTRACT,
        'sensitivity' => DataSensitivity::PERSONAL,
        'purpose' => 'Create account',
        'processed_at' => now(),
    ]);

    $activity->refresh();

    expect($activity->data_categories)->toBeNull();
    expect($activity->retention_period)->toBeNull();
});

it('has restrict on delete foreign key constraint', function (): void {
    ProcessingActivity::create([
        'data_subject_id' => $this->subject->id,
        'activity' => 'user_registration',
        'legal_basis' => LegalBasis::CONTRACT,
        'sensitivity' => DataSensitivity::PERSONAL,
        'purpose' => 'Create account',
        'processed_at' => now(),
    ]);

    expect(fn () => $this->subject->forceDelete())
        ->toThrow(Illuminate\Database\QueryException::class);
});

it('does not use soft deletes', function (): void {
    expect(in_array(Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive(ProcessingActivity::class)))->toBeFalse();
});
