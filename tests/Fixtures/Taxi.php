<?php


namespace Cruxinator\SingleTableInheritance\Tests\Fixtures;

class Taxi extends MotorVehicle
{

    protected static $singleTableType = 'taxi';

    public function setTypeAttribute($value)
    {
        $this->attributes['type'] = $value;
    }

    public function getTypeAttribute($value): string
    {
        return ucfirst($value);
    }
}
