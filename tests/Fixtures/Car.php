<?php


namespace Cruxinator\SingleTableInheritance\Tests\Fixtures;

/**
 * @property int $id
 * @property mixed|string $color
 */
class Car extends MotorVehicle
{

    protected static $singleTableType = 'car';

    protected static $persisted = ['capacity'];
}
