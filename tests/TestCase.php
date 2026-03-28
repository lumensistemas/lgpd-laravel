<?php

declare(strict_types=1);

namespace LumenSistemas\Lgpd\Tests;

use LumenSistemas\Encrypt\EncryptionServiceProvider;
use LumenSistemas\Lgpd\LgpdServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            EncryptionServiceProvider::class,
            LgpdServiceProvider::class,
        ];
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
        $keyDir = sys_get_temp_dir().'/lgpd-test-keys';

        if (!is_dir($keyDir)) {
            mkdir($keyDir, 0o700, true);
        }

        $encKeyPath = $keyDir.'/enc.key';
        $authKeyPath = $keyDir.'/auth.key';

        if (!file_exists($encKeyPath)) {
            file_put_contents($encKeyPath, sodium_crypto_secretbox_keygen());
        }

        if (!file_exists($authKeyPath)) {
            file_put_contents($authKeyPath, sodium_crypto_auth_keygen());
        }

        if (PHP_OS_FAMILY !== 'Windows') {
            chmod($encKeyPath, 0o600);
            chmod($authKeyPath, 0o600);
        }

        $app['config']->set('encryption-laravel.enc_key_path', $encKeyPath);
        $app['config']->set('encryption-laravel.auth_key_path', $authKeyPath);
    }
}
