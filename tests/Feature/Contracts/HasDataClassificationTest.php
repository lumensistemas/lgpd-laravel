<?php

declare(strict_types=1);

use LumenSistemas\Lgpd\Contracts\HasDataClassification;
use LumenSistemas\Lgpd\Enums\DataSensitivity;

it('can be implemented by a model', function (): void {
    $model = new class() implements HasDataClassification {
        public function dataClassification(): array
        {
            return [
                'name' => DataSensitivity::PERSONAL,
                'email' => DataSensitivity::PERSONAL,
                'cpf' => DataSensitivity::SENSITIVE,
            ];
        }
    };

    $classification = $model->dataClassification();

    expect($classification)->toBeArray();
    expect($classification)->toHaveCount(3);
    expect($classification['name'])->toBe(DataSensitivity::PERSONAL);
    expect($classification['cpf'])->toBe(DataSensitivity::SENSITIVE);
});

it('returns the highest sensitivity level across all columns', function (): void {
    $model = new class() implements HasDataClassification {
        public function dataClassification(): array
        {
            return [
                'name' => DataSensitivity::PERSONAL,
                'email' => DataSensitivity::PERSONAL,
                'cpf' => DataSensitivity::SENSITIVE,
            ];
        }
    };

    $classification = $model->dataClassification();
    $values = array_map(fn (DataSensitivity $s): int => array_search($s, DataSensitivity::cases(), true), $classification);

    expect(DataSensitivity::cases()[max($values)])->toBe(DataSensitivity::SENSITIVE);
});

it('can return an empty classification', function (): void {
    $model = new class() implements HasDataClassification {
        public function dataClassification(): array
        {
            return [];
        }
    };

    expect($model->dataClassification())->toBe([]);
});
