<?php

namespace Turahe\Media\Tests;

use Illuminate\Database\Schema\Blueprint;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();
    }

    protected function getPackageProviders($app)
    {
        return [
            \Turahe\Media\MediaServiceProvider::class,
        ];
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('filesystems.default', 'local');
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $app['config']->set('app.key', 'base64:MFOsOH9RomiI2LRdgP4hIeoQJ5nyBhdABdH77UY2zi8=');
    }

    protected function setUpDatabase()
    {
        $this->app['db']->connection()->getSchemaBuilder()->create('cache', function (Blueprint $table) {
            $table->string('key')->unique();
            $table->text('value');
            $table->integer('expiration');
        });

        $this->app['db']->connection()->getSchemaBuilder()->create('media', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
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

        $this->app['db']->connection()->getSchemaBuilder()->create('mediables', function (Blueprint $table) {
            $table->unsignedBigInteger('media_id')->index();
            $table->morphs('mediable');
            $table->string('group');
        });

        $this->app['db']->connection()->getSchemaBuilder()->create('subjects', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }
}
