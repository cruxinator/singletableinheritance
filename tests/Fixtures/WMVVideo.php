<?php


namespace Cruxinator\SingleTableInheritance\Tests\Fixtures;

class WMVVideo extends Video
{

    protected static $singleTableType = VideoType::WMV;
}
