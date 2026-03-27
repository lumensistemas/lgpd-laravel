<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use LumenSistemas\Lgpd\Enums\DataSensitivity;
use LumenSistemas\Lgpd\Enums\LegalBasis;
use LumenSistemas\Lgpd\Models\ProcessingActivity;

uses(RefreshDatabase::class);

it('uses the configured table name', function (): void {
    expect(new ProcessingActivity()->getTable())->toBe('processing_activities');
});

it('uses a custom table name from config', function (): void {
    config(['lgpd.tables.processing_activities' => 'custom_activities']);

    expect(new ProcessingActivity()->getTable())->toBe('custom_activities');
});

it('has uuid primary key', function (): void {
    $activity = ProcessingActivity::create([
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
        'activity' => 'user_registration',
        'legal_basis' => LegalBasis::CONTRACT,
        'sensitivity' => DataSensitivity::PERSONAL,
        'purpose' => 'Create account',
        'processed_at' => '2025-06-15 10:30:00',
    ]);

    $activity->refresh();

    expect($activity->processed_at)->toBeInstanceOf(Carbon\CarbonImmutable::class);
});

it('stores nullable fields as null', function (): void {
    $activity = ProcessingActivity::create([
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

it('does not use soft deletes', function (): void {
    expect(in_array(Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive(ProcessingActivity::class)))->toBeFalse();
});
