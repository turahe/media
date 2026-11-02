<?php

namespace Turahe\Media\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\WithFaker;
use Turahe\Media\MediaServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();
    }

    protected function getPackageProviders($app)
    {
        return [
            MediaServiceProvider::class,
        ];
    }

    /**
     * @param  Application  $app
     */
    protected function getEnvironmentSetUp($app)
    {
        $connection = getenv('DB_CONNECTION') ?: 'sqlite';

        $app['config']->set('filesystems.default', 'local');
        $app['config']->set('database.default', $connection);
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $app['config']->set('database.connections.mysql', [
            'driver' => 'mysql',
            'host' => getenv('DB_HOST') ?: '127.0.0.1',
            'port' => getenv('DB_PORT') ?: '3306',
            'database' => getenv('DB_DATABASE') ?: 'media',
            'username' => getenv('DB_USERNAME') ?: 'media',
            'password' => getenv('DB_PASSWORD') ?: 'media',
            'unix_socket' => '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
        ]);
        $app['config']->set('database.connections.pgsql', [
            'driver' => 'pgsql',
            'host' => getenv('DB_HOST') ?: '127.0.0.1',
            'port' => getenv('DB_PORT') ?: '5432',
            'database' => getenv('DB_DATABASE') ?: 'media',
            'username' => getenv('DB_USERNAME') ?: 'media',
            'password' => getenv('DB_PASSWORD') ?: 'media',
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => 'prefer',
        ]);
        $app['config']->set('app.key', 'base64:MFOsOH9RomiI2LRdgP4hIeoQJ5nyBhdABdH77UY2zi8=');
    }

    protected function setUpDatabase()
    {
        $schema = $this->app['db']->connection()->getSchemaBuilder();

        $schema->dropIfExists('mediables');
        $schema->dropIfExists('subjects');
        $schema->dropIfExists('media');
        $schema->dropIfExists('cache');

        $schema->create('cache', function (Blueprint $table) {
            $table->string('key')->unique();
            $table->text('value');
            $table->integer('expiration');
        });

        $schema->create('media', function (Blueprint $table) {
            $table->id();
            $table->string('hash');
            $table->string('name');
            $table->string('file_name');
            $table->string('disk');
            $table->string('mime_type');
            $table->unsignedInteger('size');
            $table->unsignedBigInteger('record_left')->nullable();
            $table->unsignedBigInteger('record_right')->nullable();
            $table->unsignedBigInteger('record_ordering')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('custom_attribute')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        $schema->create('mediables', function (Blueprint $table) {
            $table->unsignedBigInteger('media_id')->index();
            $table->morphs('mediable');
            $table->string('group');
        });

        $schema->create('subjects', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }
}
