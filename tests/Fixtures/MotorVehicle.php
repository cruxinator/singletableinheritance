<?php


namespace Cruxinator\SingleTableInheritance\Tests\Fixtures;

class MotorVehicle extends Vehicle
{

    protected static $singleTableType = 'motorvehicle';

    protected static $persisted = ['fuel'];

    protected static $singleTableSubclasses = [
        Car::class,
        Truck::class,
        Taxi::class
    ];
}
