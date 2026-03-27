<?php

declare(strict_types=1);

use LumenSistemas\Lgpd\Enums\LegalBasis;

it('has all ten legal bases from Art. 7', function (): void {
    expect(LegalBasis::cases())->toHaveCount(10);
});

it('has the correct values', function (LegalBasis $case, string $expected): void {
    expect($case->value)->toBe($expected);
})->with([
    [LegalBasis::CONSENT, 'consent'],
    [LegalBasis::LEGAL_OBLIGATION, 'legal_obligation'],
    [LegalBasis::PUBLIC_ADMINISTRATION, 'public_administration'],
    [LegalBasis::RESEARCH, 'research'],
    [LegalBasis::CONTRACT, 'contract'],
    [LegalBasis::LEGAL_PROCEEDINGS, 'legal_proceedings'],
    [LegalBasis::LIFE_PROTECTION, 'life_protection'],
    [LegalBasis::HEALTH, 'health'],
    [LegalBasis::LEGITIMATE_INTEREST, 'legitimate_interest'],
    [LegalBasis::CREDIT_PROTECTION, 'credit_protection'],
]);

it('returns translated labels', function (LegalBasis $case): void {
    $label = $case->label();

    expect($label)->toBeString()->not->toBe('lgpd::enums.legal_basis.'.$case->value);
})->with(LegalBasis::cases());

it('returns translated labels in pt_BR', function (LegalBasis $case): void {
    app()->setLocale('pt_BR');

    $label = $case->label();

    expect($label)->toBeString()->not->toBe('lgpd::enums.legal_basis.'.$case->value);
})->with(LegalBasis::cases());

it('returns translated descriptions', function (LegalBasis $case): void {
    $description = $case->description();

    expect($description)->toBeString()->not->toBe(sprintf('lgpd::enums.legal_basis.%s_description', $case->value));
})->with(LegalBasis::cases());
