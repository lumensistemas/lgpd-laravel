<?php

declare(strict_types=1);

use LumenSistemas\Lgpd\Enums\DataSensitivity;

it('has the correct cases', function (): void {
    expect(DataSensitivity::cases())->toHaveCount(4);
    expect(DataSensitivity::PUBLIC->value)->toBe('public');
    expect(DataSensitivity::INTERNAL->value)->toBe('internal');
    expect(DataSensitivity::PERSONAL->value)->toBe('personal');
    expect(DataSensitivity::SENSITIVE->value)->toBe('sensitive');
});

it('returns translated labels in english', function (DataSensitivity $case, string $expected): void {
    expect($case->label())->toBe($expected);
})->with([
    [DataSensitivity::PUBLIC, 'Public'],
    [DataSensitivity::INTERNAL, 'Internal'],
    [DataSensitivity::PERSONAL, 'Personal'],
    [DataSensitivity::SENSITIVE, 'Sensitive'],
]);

it('returns translated labels in pt_BR', function (DataSensitivity $case, string $expected): void {
    app()->setLocale('pt_BR');

    expect($case->label())->toBe($expected);
})->with([
    [DataSensitivity::PUBLIC, 'Público'],
    [DataSensitivity::INTERNAL, 'Interno'],
    [DataSensitivity::PERSONAL, 'Pessoal'],
    [DataSensitivity::SENSITIVE, 'Sensível'],
]);

it('returns translated descriptions', function (DataSensitivity $case): void {
    $description = $case->description();

    expect($description)->toBeString()->not->toBe(sprintf('lgpd::enums.data_sensitivity.%s_description', $case->value));
})->with(DataSensitivity::cases());

it('returns the highest sensitivity from mixed levels', function (): void {
    expect(DataSensitivity::highest([
        DataSensitivity::PERSONAL,
        DataSensitivity::PUBLIC,
        DataSensitivity::SENSITIVE,
        DataSensitivity::INTERNAL,
    ]))->toBe(DataSensitivity::SENSITIVE);
});

it('returns the single value when given one element', function (): void {
    expect(DataSensitivity::highest([DataSensitivity::PERSONAL]))->toBe(DataSensitivity::PERSONAL);
});

it('returns PUBLIC when all levels are PUBLIC', function (): void {
    expect(DataSensitivity::highest([
        DataSensitivity::PUBLIC,
        DataSensitivity::PUBLIC,
    ]))->toBe(DataSensitivity::PUBLIC);
});
