<?php


namespace Cruxinator\SingleTableInheritance\Tests;

use Illuminate\Support\Facades\DB;
use Cruxinator\SingleTableInheritance\Exceptions\InvalidAttributesException;
use Cruxinator\SingleTableInheritance\Exceptions\SingleTableInheritanceException;
use Cruxinator\SingleTableInheritance\Tests\Fixtures\Bike;
use Cruxinator\SingleTableInheritance\Tests\Fixtures\Car;
use Cruxinator\SingleTableInheritance\Tests\Fixtures\Listing;
use Cruxinator\SingleTableInheritance\Tests\Fixtures\MotorVehicle;
use Cruxinator\SingleTableInheritance\Tests\Fixtures\MP4Video;
use Cruxinator\SingleTableInheritance\Tests\Fixtures\Taxi;
use Cruxinator\SingleTableInheritance\Tests\Fixtures\User;
use Cruxinator\SingleTableInheritance\Tests\Fixtures\Vehicle;
use Cruxinator\SingleTableInheritance\Tests\Fixtures\Video;
use Cruxinator\SingleTableInheritance\Tests\Fixtures\VideoType;

/**
 * Class SingleTableInheritanceTraitPersistenceTest
 *
 * A set of tests of the persistence methods added to by the SingleTableInheritanceTrait
 * These tests are mostly duplicative of the model and static tests but they prove the integration
 * of the Trait with key parts of the Eloquent ORM.
 */
class PersistenceTest extends TestCase
{

    public function testSavingThrowsExceptionIfModelHasNoClassType()
    {
        $this->markTestSkipped('this test needs to be run against an abstract class');
        $this->expectException(SingleTableInheritanceException::class);

        (new Vehicle())->save();
    }

    public function testOnlyPersistedAttributesAreSaved()
    {
        $car = new Car;
        $car->color = 'red';
        $car->fuel = 'unleaded';
        $car->cruft = 'red is my favorite';

        $car->save();

        $dbCar = DB::table('vehicles')->first();

        $this->assertEquals($car->id, $dbCar->id);
        $this->assertNull($dbCar->cruft);

        $this->assertEquals('red', $dbCar->color);
        $this->assertEquals('unleaded', $dbCar->fuel);
    }

    public function testBelongsToRelationForeignKeyIsSaved()
    {
        $owner = new User;
        $owner->name = 'Mickey Mouse';
        $owner->save();

        $car = new Car;
        $car->color = 'red';
        $car->fuel = 'unleaded';
        $car->cruft = 'red is my favorite';
        $car->owner()->associate($owner);
        $car->save();

        $dbCar = DB::table('vehicles')->first();

        $this->assertEquals($car->id, $dbCar->id);
        $this->assertEquals($owner->id, $dbCar->owner_id);
    }

    public function testAllAttributesAreSavedIfPersistedIsEmpty()
    {
        $car = new Car;
        $car->color = 'red';
        $car->fuel = 'unleaded';
        $car->cruft = 'red is my favorite';

        Car::withAllPersisted([], function () use ($car) {
            $car->save();
        });

        $dbCar = DB::table('vehicles')->first();

        $this->assertEquals($car->id, $dbCar->id);
        $this->assertEquals('red is my favorite', $dbCar->cruft);

        $this->assertEquals('red', $dbCar->color);
        $this->assertEquals('unleaded', $dbCar->fuel);
    }

    public function testSaveAttributesWhereSingleTableInheritanceTypeHasAnAccessor()
    {
        $taxi = new Taxi;
        $taxi->color = 'yellow';
        $taxi->fuel = 'diesel';
        $taxi->cruft = 'yellow is my favorite';
        $taxi->save();

        $dbTaxi = DB::table('vehicles')->first();

        $this->assertEquals($taxi->id, $dbTaxi->id);

        $this->assertEquals('yellow', $dbTaxi->color);
        $this->assertEquals('diesel', $dbTaxi->fuel);

        $this->assertEquals('Taxi', $dbTaxi->type);
    }

    public function testSaveThrowsExceptionForInvalidAttributesIfConfigured()
    {
        $bike = new Bike;
        $bike->color = 'red';
        $bike->cruft = 'red is my favorite';

        $this->expectException(SingleTableInheritanceException::class);

        $bike->save();
    }

    public function testSaveMany()
    {
        $bike = new Bike;
        $bike->color = 'red';

        $blueBike = new Bike;
        $blueBike->color = 'blue';

        $car = new Car;
        $car->color = 'pink';
        $car->fuel = 'gas';

        $listing = new Listing();
        $listing->name = 'best vehicles 2019';
        $listing->save();

        $listing->vehicles()->saveMany([$bike, $blueBike, $car]);

        $vehicles = Vehicle::all()->toArray();

        $this->assertEquals(3, count($vehicles));
        $this->assertEquals(2, count(Bike::all()->toArray()));
        $this->assertEquals(1, count(Car::all()));
        $this->assertEquals('pink', Car::all()[0]->color);
        $this->assertEquals('gas', Car::all()[0]->fuel);
    }
}
