<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use LumenSistemas\Lgpd\Concerns\HasPersonalData;
use LumenSistemas\Lgpd\Contracts\HoldsPersonalData;
use LumenSistemas\Lgpd\Enums\DataSensitivity;
use LumenSistemas\Lgpd\Models\DataSubject;

uses(RefreshDatabase::class);

function ensureCustomersTable(): void
{
    if (!Schema::hasTable('customers')) {
        Schema::create('customers', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('cpf');
            $table->foreignUuid('data_subject_id')->nullable();
            $table->timestamps();
        });
    }
}

function createCustomer(array $attributes = []): Model
{
    ensureCustomersTable();

    $customer = new class() extends Model implements HoldsPersonalData {
        use HasPersonalData;

        protected $table = 'customers';

        /** @var list<string> */
        protected $fillable = ['name', 'cpf', 'data_subject_id'];

        public function dataClassification(): array
        {
            return [
                'name' => DataSensitivity::PERSONAL,
                'cpf' => DataSensitivity::SENSITIVE,
            ];
        }
    };

    return $customer->create(array_merge(['name' => 'Lucas', 'cpf' => '12345678900'], $attributes));
}

it('returns a data classification map', function (): void {
    $customer = createCustomer();

    $classification = $customer->dataClassification();

    expect($classification)->toBeArray();
    expect($classification)->toHaveCount(2);
    expect($classification['name'])->toBe(DataSensitivity::PERSONAL);
    expect($classification['cpf'])->toBe(DataSensitivity::SENSITIVE);
});

it('provides a dataSubject relationship', function (): void {
    $subject = DataSubject::create(['document_hash' => '12345678900']);
    $customer = createCustomer(['data_subject_id' => $subject->id]);

    expect($customer->dataSubject)->toBeInstanceOf(DataSubject::class);
    expect($customer->dataSubject->id)->toBe($subject->id);
});

it('returns null dataSubject when not linked', function (): void {
    $customer = createCustomer();

    expect($customer->dataSubject)->toBeNull();
});
