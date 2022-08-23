<?php


namespace Tests\Packages\SingleTableInheritance;

use Cruxinator\SingleTableInheritance\Exceptions\WrongInheritanceException;
use Cruxinator\SingleTableInheritance\Tests\Fixtures\Bike;
use Cruxinator\SingleTableInheritance\Tests\Fixtures\Car;
use Cruxinator\SingleTableInheritance\Tests\Fixtures\File;
use Cruxinator\SingleTableInheritance\Tests\Fixtures\MotorVehicle;
use Cruxinator\SingleTableInheritance\Tests\Fixtures\MP4Video;
use Cruxinator\SingleTableInheritance\Tests\Fixtures\Taxi;
use Cruxinator\SingleTableInheritance\Tests\Fixtures\Truck;
use Cruxinator\SingleTableInheritance\Tests\Fixtures\Vehicle;
use Cruxinator\SingleTableInheritance\Tests\Fixtures\Video;
use Cruxinator\SingleTableInheritance\Tests\Fixtures\WMVVideo;
use Cruxinator\SingleTableInheritance\Tests\TestCase;

/**
 * Class SingleTableInheritanceTraitStaticMethodsTest
 *
 * A set of tests of the static methods added to by the SingleTableInheritanceTrait
 */
class StaticMethodsTest extends TestCase
{

    // getSingleTableTypeMap

    public function testGetTypeMapOfRoot()
    {
        $expectedSubclassTypes = [
            'motorvehicle' => MotorVehicle::class,
            'car'          => Car::class,
            'truck'        => Truck::class,
            'bike'         => Bike::class,
            'taxi'         => Taxi::class,
            Vehicle::class => Vehicle::class,
        ];

        $this->assertEquals($expectedSubclassTypes, Vehicle::getSingleTableTypeMap());
    }

    public function testGetTypeMapOfChild()
    {
        $expectedSubclassTypes = [
            'motorvehicle' => MotorVehicle::class,
            'car'          => Car::class,
            'truck'        => Truck::class,
            'taxi'         => Taxi::class,
        ];

        $this->assertEquals($expectedSubclassTypes, MotorVehicle::getSingleTableTypeMap());
    }

    public function testGetTypeMapOfLeaf()
    {

        $expectedSubclassTypes = [
            'car' => Car::class
        ];

        $this->assertEquals($expectedSubclassTypes, Car::getSingleTableTypeMap());
    }

    public function testGetTypeMapOfRootHavingIntegerType()
    {
        $expectedSubclassTypes = [
            1 => MP4Video::class,
            2 => WMVVideo::class,
            Video::class => Video::class
        ];

        $this->assertEquals($expectedSubclassTypes, Video::getSingleTableTypeMap());
    }

    // getAllPersistedAttributes

    public function testGetAllPersistedOfRoot()
    {
        $a = Vehicle::getAllPersistedAttributes();
        sort($a);
        $this->assertEquals(['color', 'owner_id'], $a);
    }

    public function testGetAllPersistedOfChild()
    {
        $a = MotorVehicle::getAllPersistedAttributes();
        sort($a);
        $this->assertEquals(['color', 'fuel', 'owner_id'], $a);
    }

    public function testGetAllPersistedOfLeaf()
    {
        $a = Car::getAllPersistedAttributes();
        sort($a);
        $this->assertEquals(['capacity', 'color', 'fuel', 'owner_id'], $a);
    }

    public function testWrongInheritanceException()
    {
        $this->expectException(WrongInheritanceException::class);
        File::getSingleTableTypeMap();
    }
}
