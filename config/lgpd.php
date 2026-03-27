<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Multi-tenancy
    |--------------------------------------------------------------------------
    |
    | Enable or disable multi-tenancy support. When enabled, a tenant column
    | will be added to LGPD tables and used to scope queries.
    |
    | Set 'use_uuid' to true if your app uses UUIDs for tenant identifiers.
    |
    */

    'multi_tenancy' => [
        'enabled' => false,
        'column' => 'tenant_id',
        'use_uuid' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Table Names
    |--------------------------------------------------------------------------
    |
    | Customize the table names used by the LGPD package. This is useful when
    | you need to avoid naming conflicts with existing tables.
    |
    */

    'tables' => [
        'data_subjects' => 'data_subjects',
        'consents' => 'consents',
        'processing_activities' => 'processing_activities',
    ],

    /*
    |--------------------------------------------------------------------------
    | Models
    |--------------------------------------------------------------------------
    |
    | Customize the model classes used by the LGPD package. This allows you
    | to extend the package models with your own implementations.
    |
    */

    'models' => [
        'data_subject' => LumenSistemas\Lgpd\Models\DataSubject::class,
        'consent' => LumenSistemas\Lgpd\Models\Consent::class,
        'processing_activity' => LumenSistemas\Lgpd\Models\ProcessingActivity::class,
    ],

];
