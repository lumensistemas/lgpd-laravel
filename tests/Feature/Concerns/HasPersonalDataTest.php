<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use LumenSistemas\Lgpd\Concerns\HasPersonalData;
use LumenSistemas\Lgpd\Contracts\HasDataClassification;
use LumenSistemas\Lgpd\Enums\DataSensitivity;
use LumenSistemas\Lgpd\Models\DataSubject;

uses(RefreshDatabase::class);

function ensureUsersTable(): void
{
    if (!Schema::hasTable('users')) {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->foreignUuid('data_subject_id')->nullable();
            $table->timestamps();
        });
    }
}

function createUser(array $attributes = []): Model
{
    ensureUsersTable();

    $user = new class() extends Model implements HasDataClassification {
        use HasPersonalData;

        protected $table = 'users';

        /** @var list<string> */
        protected $fillable = ['name', 'data_subject_id'];

        public function dataClassification(): array
        {
            return [
                'name' => DataSensitivity::PERSONAL,
            ];
        }
    };

    return $user->create(array_merge(['name' => 'Lucas'], $attributes));
}

it('has a dataSubject relationship', function (): void {
    $subject = DataSubject::create(['document_hash' => '12345678900']);
    $user = createUser(['data_subject_id' => $subject->id]);

    expect($user->dataSubject)->toBeInstanceOf(DataSubject::class);
    expect($user->dataSubject->id)->toBe($subject->id);
});

it('returns null when no data subject is linked', function (): void {
    $user = createUser();

    expect($user->dataSubject)->toBeNull();
});
