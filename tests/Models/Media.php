<?php

namespace Turahe\Media\Tests\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Turahe\Media\Models\Media as BaseMedia;
use Turahe\Media\Tests\Database\Factories\MediaFactory;

class Media extends BaseMedia
{
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     *
     * @return Factory<static>
     */
    protected static function newFactory()
    {
        return new MediaFactory;
    }
}
