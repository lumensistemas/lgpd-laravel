<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use LumenSistemas\Lgpd\Concerns\HasPersonalData;
use LumenSistemas\Lgpd\Contracts\HoldsPersonalData;
use LumenSistemas\Lgpd\Enums\DataSensitivity;
use LumenSistemas\Lgpd\Enums\LegalBasis;
use LumenSistemas\Lgpd\Models\DataSubject;
use LumenSistemas\Lgpd\Models\ProcessingActivity;
use LumenSistemas\Lgpd\Services\ProcessingActivityRecorder;

uses(RefreshDatabase::class);

function ensureEmployeesTable(): void
{
    if (!Schema::hasTable('employees')) {
        Schema::create('employees', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('cpf');
            $table->foreignUuid('data_subject_id')->nullable();
            $table->timestamps();
        });
    }
}

function createEmployee(array $attributes = []): Model
{
    ensureEmployeesTable();

    $employee = new class() extends Model implements HoldsPersonalData {
        use HasPersonalData;

        protected $table = 'employees';

        /** @var list<string> */
        protected $fillable = ['name', 'email', 'cpf', 'data_subject_id'];

        public function dataClassification(): array
        {
            return [
                'name' => DataSensitivity::PERSONAL,
                'email' => DataSensitivity::PERSONAL,
                'cpf' => DataSensitivity::SENSITIVE,
            ];
        }
    };

    return $employee->create(array_merge([
        'name' => 'Lucas',
        'email' => 'lucas@example.com',
        'cpf' => '12345678900',
    ], $attributes));
}

beforeEach(function (): void {
    $this->subject = DataSubject::create(['document_hash' => '12345678900']);
    $this->recorder = new ProcessingActivityRecorder();
});

it('records a processing activity with auto-filled categories and sensitivity', function (): void {
    $employee = createEmployee(['data_subject_id' => $this->subject->id]);

    $activity = $this->recorder->record($employee, [
        'activity' => 'user_registration',
        'legal_basis' => LegalBasis::CONTRACT,
        'purpose' => 'Create account',
        'processed_at' => now(),
    ]);

    expect($activity)->toBeInstanceOf(ProcessingActivity::class);
    expect($activity->data_categories)->toBe(['name', 'email', 'cpf']);
    expect($activity->sensitivity)->toBe(DataSensitivity::SENSITIVE);
});

it('auto-fills data_subject_id from the model relationship', function (): void {
    $employee = createEmployee(['data_subject_id' => $this->subject->id]);

    $activity = $this->recorder->record($employee, [
        'activity' => 'user_registration',
        'legal_basis' => LegalBasis::CONTRACT,
        'purpose' => 'Create account',
        'processed_at' => now(),
    ]);

    expect($activity->data_subject_id)->toBe($this->subject->id);
});

it('throws when model is not linked to a data subject', function (): void {
    $employee = createEmployee();

    expect(fn () => $this->recorder->record($employee, [
        'activity' => 'system_check',
        'legal_basis' => LegalBasis::LEGITIMATE_INTEREST,
        'purpose' => 'Health check',
        'processed_at' => now(),
    ]))->toThrow(LumenSistemas\Lgpd\Exceptions\MissingDataSubjectException::class);
});

it('allows caller to override data_subject_id', function (): void {
    $employee = createEmployee(['data_subject_id' => $this->subject->id]);
    $otherSubject = DataSubject::create(['document_hash' => '99999999999']);

    $activity = $this->recorder->record($employee, [
        'data_subject_id' => $otherSubject->id,
        'activity' => 'data_transfer',
        'legal_basis' => LegalBasis::CONSENT,
        'purpose' => 'Transfer data',
        'processed_at' => now(),
    ]);

    expect($activity->data_subject_id)->toBe($otherSubject->id);
});

it('filters data_categories when columns parameter is provided', function (): void {
    $employee = createEmployee(['data_subject_id' => $this->subject->id]);

    $activity = $this->recorder->record($employee, [
        'activity' => 'profile_view',
        'legal_basis' => LegalBasis::CONTRACT,
        'purpose' => 'Display user profile',
        'processed_at' => now(),
    ], columns: ['name', 'email']);

    expect($activity->data_categories)->toBe(['name', 'email']);
    expect($activity->sensitivity)->toBe(DataSensitivity::PERSONAL);
});

it('preserves all caller-provided attributes', function (): void {
    $employee = createEmployee(['data_subject_id' => $this->subject->id]);

    $activity = $this->recorder->record($employee, [
        'activity' => 'data_export',
        'legal_basis' => LegalBasis::CONSENT,
        'purpose' => 'Export personal data',
        'retention_period' => '2 years',
        'processed_at' => '2025-06-15 10:00:00',
    ]);

    expect($activity->activity)->toBe('data_export');
    expect($activity->legal_basis)->toBe(LegalBasis::CONSENT);
    expect($activity->purpose)->toBe('Export personal data');
    expect($activity->retention_period)->toBe('2 years');
});
