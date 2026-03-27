<?php

declare(strict_types=1);

use LumenSistemas\Lgpd\Enums\RequestStatus;

it('has the correct cases', function (): void {
    expect(RequestStatus::cases())->toHaveCount(4);
});

it('has the correct values', function (RequestStatus $case, string $expected): void {
    expect($case->value)->toBe($expected);
})->with([
    [RequestStatus::PENDING, 'pending'],
    [RequestStatus::IN_PROGRESS, 'in_progress'],
    [RequestStatus::COMPLETED, 'completed'],
    [RequestStatus::DENIED, 'denied'],
]);

it('returns translated labels', function (RequestStatus $case): void {
    $label = $case->label();

    expect($label)->toBeString()->not->toBe('lgpd::enums.request_status.'.$case->value);
})->with(RequestStatus::cases());

it('returns translated labels in pt_BR', function (RequestStatus $case): void {
    app()->setLocale('pt_BR');

    $label = $case->label();

    expect($label)->toBeString()->not->toBe('lgpd::enums.request_status.'.$case->value);
})->with(RequestStatus::cases());

it('returns translated descriptions', function (RequestStatus $case): void {
    $description = $case->description();

    expect($description)->toBeString()->not->toBe(sprintf('lgpd::enums.request_status.%s_description', $case->value));
})->with(RequestStatus::cases());
