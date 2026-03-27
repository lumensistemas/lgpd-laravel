<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        $tableName = config('lgpd.tables.processing_activities', 'processing_activities');
        $dataSubjectsTable = config('lgpd.tables.data_subjects', 'data_subjects');

        Schema::create($tableName, function (Blueprint $table) use ($dataSubjectsTable): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('data_subject_id')->nullable()->constrained($dataSubjectsTable)->nullOnDelete();

            if (config('lgpd.multi_tenancy.enabled')) {
                $table->string(config('lgpd.multi_tenancy.column'))->index();
            }

            $table->string('activity');
            $table->string('legal_basis');
            $table->string('sensitivity');
            $table->string('purpose');
            $table->json('data_categories')->nullable();
            $table->string('retention_period')->nullable();
            $table->timestamp('processed_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('lgpd.tables.processing_activities', 'processing_activities'));
    }
};
