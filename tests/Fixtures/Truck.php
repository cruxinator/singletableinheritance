<?php


namespace Cruxinator\SingleTableInheritance\Tests\Fixtures;

/**
 * @method static find(int $carId)
 */
class Truck extends MotorVehicle
{

    protected static $singleTableType = 'truck';
}
