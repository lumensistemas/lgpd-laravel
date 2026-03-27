<?php

declare(strict_types=1);

use function LumenSistemas\Lgpd\trans_string;

it('returns the translated string for a valid key', function (): void {
    expect(trans_string('lgpd::enums.data_sensitivity.public'))->toBe('Public');
});

it('returns the key when the translation does not exist', function (): void {
    expect(trans_string('lgpd::enums.nonexistent'))->toBe('lgpd::enums.nonexistent');
});

it('returns the key when the translation resolves to an array', function (): void {
    expect(trans_string('lgpd::enums.data_sensitivity'))->toBe('lgpd::enums.data_sensitivity');
});

it('supports replacements', function (): void {
    app('translator')->addLines(['test.greeting' => 'Hello :name'], 'en');

    expect(trans_string('test.greeting', ['name' => 'Lucas']))->toBe('Hello Lucas');
});

it('supports locale override', function (): void {
    expect(trans_string('lgpd::enums.data_sensitivity.public', locale: 'pt_BR'))->toBe('Público');
});
