<?php


namespace Cruxinator\SingleTableInheritance\Tests\Fixtures;

class Audio extends File
{

    protected static $singleTableType = 'audio';

    protected static $singleTableSubclasses = [
       AudioMP3::class,
    ];
}
