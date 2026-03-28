<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        $dataSubjectsTable = Config::string('lgpd.tables.data_subjects', 'data_subjects');
        $processingActivitiesTable = Config::string('lgpd.tables.processing_activities', 'processing_activities');
        $consentsTable = Config::string('lgpd.tables.consents', 'consents');
        $dataSubjectRequestsTable = Config::string('lgpd.tables.data_subject_requests', 'data_subject_requests');

        Schema::create($dataSubjectsTable, function (Blueprint $table): void {
            $table->uuid('id')->primary();

            if (Config::boolean('lgpd.multi_tenancy.enabled')) {
                $column = Config::string('lgpd.multi_tenancy.column');

                if (Config::boolean('lgpd.multi_tenancy.use_uuid')) {
                    $table->foreignUuid($column);
                } else {
                    $table->foreignId($column);
                }
            }

            $table->string('document_hash');

            if (Config::boolean('lgpd.multi_tenancy.enabled')) {
                $table->unique([Config::string('lgpd.multi_tenancy.column'), 'document_hash']);
            } else {
                $table->unique('document_hash');
            }

            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create($processingActivitiesTable, function (Blueprint $table): void {
            $table->uuid('id')->primary();

            if (Config::boolean('lgpd.multi_tenancy.enabled')) {
                $table->string(Config::string('lgpd.multi_tenancy.column'))->index();
            }

            $table->string('activity');
            $table->string('legal_basis');
            $table->string('sensitivity');
            $table->string('purpose');
            $table->json('data_categories')->nullable();
            $table->string('retention_period')->nullable();
            $table->timestamp('processed_at');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create($consentsTable, function (Blueprint $table) use ($dataSubjectsTable): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('data_subject_id')->constrained($dataSubjectsTable);

            if (Config::boolean('lgpd.multi_tenancy.enabled')) {
                $column = Config::string('lgpd.multi_tenancy.column');

                if (Config::boolean('lgpd.multi_tenancy.use_uuid')) {
                    $table->foreignUuid($column);
                } else {
                    $table->foreignId($column);
                }
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

        Schema::create($dataSubjectRequestsTable, function (Blueprint $table) use ($dataSubjectsTable): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('data_subject_id')->constrained($dataSubjectsTable)->restrictOnDelete();

            if (Config::boolean('lgpd.multi_tenancy.enabled')) {
                $column = Config::string('lgpd.multi_tenancy.column');

                if (Config::boolean('lgpd.multi_tenancy.use_uuid')) {
                    $table->foreignUuid($column);
                } else {
                    $table->foreignId($column);
                }
            }

            $table->string('right');
            $table->string('status');
            $table->timestamp('requested_at');
            $table->timestamp('responded_at')->nullable();
            $table->text('response_notes')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(Config::string('lgpd.tables.data_subject_requests', 'data_subject_requests'));
        Schema::dropIfExists(Config::string('lgpd.tables.consents', 'consents'));
        Schema::dropIfExists(Config::string('lgpd.tables.processing_activities', 'processing_activities'));
        Schema::dropIfExists(Config::string('lgpd.tables.data_subjects', 'data_subjects'));
    }
};
