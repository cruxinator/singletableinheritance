<?php


namespace Cruxinator\SingleTableInheritance\Tests\Fixtures;

class MP4Video extends Video
{

    protected static $singleTableType = VideoType::MP4;
}
