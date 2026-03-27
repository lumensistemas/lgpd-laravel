<?php

declare(strict_types=1);

use LumenSistemas\Lgpd\Enums\DataSubjectRight;

it('has all nine rights from Art. 18', function (): void {
    expect(DataSubjectRight::cases())->toHaveCount(9);
});

it('has the correct values', function (DataSubjectRight $case, string $expected): void {
    expect($case->value)->toBe($expected);
})->with([
    [DataSubjectRight::ACCESS, 'access'],
    [DataSubjectRight::CORRECTION, 'correction'],
    [DataSubjectRight::ANONYMIZATION, 'anonymization'],
    [DataSubjectRight::PORTABILITY, 'portability'],
    [DataSubjectRight::DELETION, 'deletion'],
    [DataSubjectRight::SHARING_INFO, 'sharing_info'],
    [DataSubjectRight::CONSENT_INFO, 'consent_info'],
    [DataSubjectRight::CONSENT_REVOCATION, 'consent_revocation'],
    [DataSubjectRight::OPPOSITION, 'opposition'],
]);

it('returns translated labels', function (DataSubjectRight $case): void {
    $label = $case->label();

    expect($label)->toBeString()->not->toBe('lgpd::enums.data_subject_right.'.$case->value);
})->with(DataSubjectRight::cases());

it('returns translated labels in pt_BR', function (DataSubjectRight $case): void {
    app()->setLocale('pt_BR');

    $label = $case->label();

    expect($label)->toBeString()->not->toBe('lgpd::enums.data_subject_right.'.$case->value);
})->with(DataSubjectRight::cases());

it('returns translated descriptions', function (DataSubjectRight $case): void {
    $description = $case->description();

    expect($description)->toBeString()->not->toBe(sprintf('lgpd::enums.data_subject_right.%s_description', $case->value));
})->with(DataSubjectRight::cases());
