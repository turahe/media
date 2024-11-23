<?php

namespace Turahe\Media\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Turahe\Media\HasMedia;
use Turahe\Media\HasMediaInterface;

class Subject extends Model implements HasMediaInterface
{
    use HasMedia;
}
