<?php


namespace Cruxinator\SingleTableInheritance\Tests\Fixtures;

use Cruxinator\SingleTableInheritance\SingleTableInheritanceTrait;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use SingleTableInheritanceTrait;

    protected $table = 'files';

    protected static $singleTableTypeField = 'type';

    protected static $singleTableSubclasses = [
        Audio::class,
    ];
}
