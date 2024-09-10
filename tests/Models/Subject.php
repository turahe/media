<?php

namespace Turahe\Media\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Turahe\Media\HasMedia;

class Subject extends Model
{
    use HasMedia;

    public function registerMediaGroups()
    {
        $this->addMediaGroup('converted-images')
            ->performConversions('conversion');
    }
}
