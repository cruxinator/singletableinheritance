<?php


namespace Tests\Packages\SingleTableInheritance;

use Cruxinator\SingleTableInheritance\Tests\Fixtures\Car;
use Cruxinator\SingleTableInheritance\Tests\Fixtures\Listing;
use Cruxinator\SingleTableInheritance\Tests\Fixtures\Truck;
use Cruxinator\SingleTableInheritance\Tests\TestCase;

/**
 * Class SingleTableInheritanceTraitManyToManyTest
 *
 * A set of tests around many-to-many relationships involving SingleTableInheritanceTrait
 */
class TraitManyToManyTest extends TestCase
{

    protected $redCar;
    protected $blueTruck;
    protected $listing;

    public function setUp(): void
    {
        parent::setUp();
        $this->redCar = new Car();
        $this->redCar->color = 'red';
        $this->redCar->fuel = 'gas';
        $this->redCar->save();

        $this->blueTruck = new Truck();
        $this->blueTruck->color = 'blue';
        $this->blueTruck->save();

        $this->listing = new Listing();
        $this->listing->name = 'best vehicles 2019';
        $this->listing->save();
        $this->listing->vehicles()->save($this->redCar);
        $this->listing->vehicles()->save($this->blueTruck);
    }

    public function testManyToManyCollection()
    {
        $vehicles = Listing::first()->vehicles;
        $this->assertEquals([$this->redCar->id, $this->blueTruck->id], [$vehicles[0]->id, $vehicles[1]->id]);
    }

    public function testManyToManyLoadedCollection()
    {
        $vehicles = Listing::first()->load('vehicles')->vehicles;
        $this->assertEquals([$this->redCar->id, $this->blueTruck->id], [$vehicles[0]->id, $vehicles[1]->id]);

        $this->assertEquals('gas', $vehicles[0]->fuel);
        $this->assertEquals('red', $vehicles[0]->color);
        $this->assertEquals('blue', $vehicles[1]->color);
    }

    public function testManyToManyWithCollection()
    {
        $vehicles = Listing::with('vehicles')->first()->vehicles;
        $this->assertEquals([$this->redCar->id, $this->blueTruck->id], [$vehicles[0]->id, $vehicles[1]->id]);

        $this->assertEquals('gas', $vehicles[0]->fuel);
        $this->assertEquals('red', $vehicles[0]->color);
        $this->assertEquals('blue', $vehicles[1]->color);
    }

    public function testManyToManyWithoutPersistedColumns()
    {
        $vehicles = Car::withAllPersisted([], function () {
            return Listing::with('vehicles')->first()->vehicles;
        });
        $this->assertCount(2, $vehicles);
        $this->assertEquals(
            [$this->redCar->id, $this->blueTruck->id],
            [$vehicles[0]->id, $vehicles[1]->id]
        );

        $this->assertEquals('gas', $vehicles[0]->fuel);
        $this->assertEquals('red', $vehicles[0]->color);
        $this->assertEquals('blue', $vehicles[1]->color);
    }
}
