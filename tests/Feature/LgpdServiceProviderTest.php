<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Lang;
use Illuminate\Support\ServiceProvider;
use LumenSistemas\Lgpd\LgpdServiceProvider;

it('merges the lgpd config', function (): void {
    expect(config('lgpd.multi_tenancy.enabled'))->toBeFalse();
    expect(config('lgpd.multi_tenancy.column'))->toBe('tenant_id');
    expect(config('lgpd.tables.data_subjects'))->toBe('data_subjects');
    expect(config('lgpd.tables.consents'))->toBe('consents');
    expect(config('lgpd.tables.processing_activities'))->toBe('processing_activities');
    expect(config('lgpd.tables.data_subject_requests'))->toBe('data_subject_requests');
    expect(config('lgpd.models.data_subject'))->toBe(LumenSistemas\Lgpd\Models\DataSubject::class);
    expect(config('lgpd.models.consent'))->toBe(LumenSistemas\Lgpd\Models\Consent::class);
    expect(config('lgpd.models.processing_activity'))->toBe(LumenSistemas\Lgpd\Models\ProcessingActivity::class);
    expect(config('lgpd.models.data_subject_request'))->toBe(LumenSistemas\Lgpd\Models\DataSubjectRequest::class);
});

it('registers config for publishing', function (): void {
    $paths = ServiceProvider::pathsToPublish(LgpdServiceProvider::class, 'lgpd-config');

    expect($paths)->not->toBeEmpty();
    expect(array_values($paths)[0])->toContain('lgpd.php');
});

it('registers translations', function (): void {
    expect(Lang::has('lgpd::enums.data_sensitivity.public'))->toBeTrue();
    expect(Lang::has('lgpd::enums.data_sensitivity.sensitive'))->toBeTrue();
});

it('registers translations for publishing', function (): void {
    $paths = ServiceProvider::pathsToPublish(LgpdServiceProvider::class, 'lgpd-lang');

    expect($paths)->not->toBeEmpty();
});

it('registers migrations for publishing', function (): void {
    $paths = ServiceProvider::pathsToPublish(LgpdServiceProvider::class, 'lgpd-migrations');

    expect($paths)->not->toBeEmpty();
});
