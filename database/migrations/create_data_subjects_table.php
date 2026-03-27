<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        $tableName = config('lgpd.tables.data_subjects', 'data_subjects');

        Schema::create($tableName, function (Blueprint $table): void {
            $table->uuid('id')->primary();

            if (Config::bool('lgpd.multi_tenancy.enabled')) {
                $column = Config::string('lgpd.multi_tenancy.column');

                if (Config::bool('lgpd.multi_tenancy.use_uuid')) {
                    $table->foreignUuid($column);
                } else {
                    $table->foreignId($column);
                }
            }

            $table->string('document_hash');

            if (Config::bool('lgpd.multi_tenancy.enabled')) {
                $table->unique([Config::string('lgpd.multi_tenancy.column'), 'document_hash']);
            } else {
                $table->unique('document_hash');
            }

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(Config::string('lgpd.tables.data_subjects', 'data_subjects'));
    }
};
