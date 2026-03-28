<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use LumenSistemas\Lgpd\Concerns\Anonymizable;
use LumenSistemas\Lgpd\Concerns\HasPersonalData;
use LumenSistemas\Lgpd\Contracts\HoldsPersonalData;
use LumenSistemas\Lgpd\Enums\DataSensitivity;

uses(RefreshDatabase::class);

function ensureContactsTable(): void
{
    if (!Schema::hasTable('contacts')) {
        Schema::create('contacts', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('cpf');
            $table->string('notes')->nullable();
            $table->foreignUuid('data_subject_id')->nullable();
            $table->timestamps();
        });
    }
}

function createContact(array $attributes = []): Model
{
    ensureContactsTable();

    $contact = new class() extends Model implements HoldsPersonalData {
        use Anonymizable;
        use HasPersonalData;

        protected $table = 'contacts';

        /** @var list<string> */
        protected $fillable = ['name', 'email', 'cpf', 'notes', 'data_subject_id'];

        public function dataClassification(): array
        {
            return [
                'name' => DataSensitivity::PERSONAL,
                'email' => DataSensitivity::PERSONAL,
                'cpf' => DataSensitivity::SENSITIVE,
            ];
        }
    };

    return $contact->create(array_merge([
        'name' => 'Lucas Vasconcelos',
        'email' => 'lucas@example.com',
        'cpf' => '12345678900',
        'notes' => 'VIP customer',
    ], $attributes));
}

it('anonymizes all classified fields in memory', function (): void {
    $contact = createContact();

    $result = $contact->anonymize();

    expect($result)->toBeInstanceOf($contact::class);
    expect($contact->name)->toBe('**REDACTED**');
    expect($contact->email)->toBe('**REDACTED**');
    expect($contact->cpf)->toBe('**REDACTED**');
});

it('does not anonymize unclassified fields', function (): void {
    $contact = createContact();

    $contact->anonymize();

    expect($contact->notes)->toBe('VIP customer');
});

it('does not persist changes automatically', function (): void {
    $contact = createContact();

    $contact->anonymize();

    $fresh = $contact->fresh();

    expect($fresh->name)->toBe('Lucas Vasconcelos');
    expect($fresh->email)->toBe('lucas@example.com');
});

it('persists when consumer calls save explicitly', function (): void {
    $contact = createContact();

    $contact->anonymize()->save();

    $fresh = $contact->fresh();

    expect($fresh->name)->toBe('**REDACTED**');
    expect($fresh->email)->toBe('**REDACTED**');
    expect($fresh->cpf)->toBe('**REDACTED**');
    expect($fresh->notes)->toBe('VIP customer');
});

it('returns true when model is anonymized', function (): void {
    $contact = createContact();

    expect($contact->isAnonymized())->toBeFalse();

    $contact->anonymize();

    expect($contact->isAnonymized())->toBeTrue();
});

it('returns false when partially anonymized', function (): void {
    $contact = createContact();

    $contact->update(['name' => '**REDACTED**']);

    expect($contact->isAnonymized())->toBeFalse();
});

it('returns false for empty classification', function (): void {
    ensureContactsTable();

    $contact = new class() extends Model implements HoldsPersonalData {
        use Anonymizable;
        use HasPersonalData;

        protected $table = 'contacts';

        /** @var list<string> */
        protected $fillable = ['name', 'email', 'cpf', 'notes', 'data_subject_id'];

        public function dataClassification(): array
        {
            return [];
        }
    };

    $instance = $contact->create([
        'name' => 'Lucas',
        'email' => 'lucas@example.com',
        'cpf' => '12345678900',
    ]);

    expect($instance->isAnonymized())->toBeFalse();
});

it('allows customizing the anonymized value', function (): void {
    ensureContactsTable();

    $contact = new class() extends Model implements HoldsPersonalData {
        use Anonymizable;
        use HasPersonalData;

        protected $table = 'contacts';

        /** @var list<string> */
        protected $fillable = ['name', 'email', 'cpf', 'notes', 'data_subject_id'];

        public function dataClassification(): array
        {
            return [
                'name' => DataSensitivity::PERSONAL,
                'email' => DataSensitivity::PERSONAL,
                'cpf' => DataSensitivity::SENSITIVE,
            ];
        }

        protected function anonymizedValue(string $column): string
        {
            return match ($column) {
                'email' => 'anonymous@redacted.com',
                'cpf' => '00000000000',
                default => '***',
            };
        }
    };

    $instance = $contact->create([
        'name' => 'Lucas',
        'email' => 'lucas@example.com',
        'cpf' => '12345678900',
    ]);

    $instance->anonymize();

    expect($instance->name)->toBe('***');
    expect($instance->email)->toBe('anonymous@redacted.com');
    expect($instance->cpf)->toBe('00000000000');
});

it('is chainable', function (): void {
    $contact = createContact();

    $result = $contact->anonymize();

    expect($result)->toBe($contact);
});

it('returns masked values for all classified columns', function (): void {
    $contact = createContact();

    $masked = $contact->masked();

    expect($masked)->toHaveCount(3);
    expect($masked['name'])->toStartWith('L')->toEndWith('s')->toContain('*');
    expect($masked['email'])->toStartWith('l')->toEndWith('m')->toContain('*');
    expect($masked['cpf'])->toStartWith('1')->toEndWith('0')->toContain('*');
    expect(mb_strlen((string) $masked['name']))->toBe(mb_strlen('Lucas Vasconcelos'));
    expect(mb_strlen((string) $masked['email']))->toBe(mb_strlen('lucas@example.com'));
    expect(mb_strlen((string) $masked['cpf']))->toBe(mb_strlen('12345678900'));
});

it('returns masked values for a subset of columns', function (): void {
    $contact = createContact();

    $masked = $contact->masked(['email']);

    expect($masked)->toHaveCount(1);
    expect($masked['email'])->toStartWith('l')->toEndWith('m')->toContain('*');
    expect($masked)->not->toHaveKey('name');
    expect($masked)->not->toHaveKey('cpf');
});

it('does not modify the model when masking', function (): void {
    $contact = createContact();

    $contact->masked();

    expect($contact->name)->toBe('Lucas Vasconcelos');
    expect($contact->email)->toBe('lucas@example.com');
});

it('masks short values completely', function (): void {
    $contact = createContact(['name' => 'Lu']);

    $masked = $contact->masked(['name']);

    expect($masked['name'])->toBe('**');
});

it('allows customizing the masked value', function (): void {
    ensureContactsTable();

    $contact = new class() extends Model implements HoldsPersonalData {
        use Anonymizable;
        use HasPersonalData;

        protected $table = 'contacts';

        /** @var list<string> */
        protected $fillable = ['name', 'email', 'cpf', 'notes', 'data_subject_id'];

        public function dataClassification(): array
        {
            return [
                'name' => DataSensitivity::PERSONAL,
                'email' => DataSensitivity::PERSONAL,
                'cpf' => DataSensitivity::SENSITIVE,
            ];
        }

        protected function maskedValue(string $column, string $value): string
        {
            return match ($column) {
                'email' => preg_replace('/^(.).*(@.*)$/', '$1***$2', $value) ?? '***',
                'cpf' => '***.***.'.mb_substr($value, 6, 3).'-'.mb_substr($value, 9, 2),
                default => mb_substr($value, 0, 1).'****',
            };
        }
    };

    $instance = $contact->create([
        'name' => 'Lucas',
        'email' => 'lucas@example.com',
        'cpf' => '12345678900',
    ]);

    $masked = $instance->masked();

    expect($masked['name'])->toBe('L****');
    expect($masked['email'])->toBe('l***@example.com');
    expect($masked['cpf'])->toBe('***.***.789-00');
});
