<?php


namespace Cruxinator\SingleTableInheritance\Tests\Fixtures;

/**
 * @property int $id
 * @property mixed|string $color
 */
class Bike extends Vehicle
{

    protected static $singleTableType = 'bike';

    protected static $throwInvalidAttributeExceptions = true;
}
