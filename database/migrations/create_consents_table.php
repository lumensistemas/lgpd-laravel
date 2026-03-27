<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        $tableName = config('lgpd.tables.consents', 'consents');
        $dataSubjectsTable = config('lgpd.tables.data_subjects', 'data_subjects');

        Schema::create($tableName, function (Blueprint $table) use ($dataSubjectsTable): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('data_subject_id')->constrained($dataSubjectsTable)->cascadeOnDelete();

            if (config('lgpd.multi_tenancy.enabled')) {
                $table->string(config('lgpd.multi_tenancy.column'))->index();
            }

            $table->string('purpose');
            $table->string('legal_basis');
            $table->timestamp('granted_at');
            $table->timestamp('revoked_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->json('metadata')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('lgpd.tables.consents', 'consents'));
    }
};
