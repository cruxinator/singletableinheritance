<?php


namespace Cruxinator\SingleTableInheritance\Tests\Fixtures;

use Cruxinator\SingleTableInheritance\SingleTableInheritanceTrait;
use Illuminate\Database\Eloquent\Model;

class Video extends Model
{

    use SingleTableInheritanceTrait;

    protected $table = 'videos';

    protected static $singleTableTypeField = 'type';

    protected static $singleTableSubclasses = [
        MP4Video::class,
        WMVVideo::class,
    ];
}
