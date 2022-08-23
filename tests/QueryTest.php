<?php


namespace Cruxinator\SingleTableInheritance\Tests;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Cruxinator\SingleTableInheritance\Exceptions\InvalidAttributesException;
use Cruxinator\SingleTableInheritance\Exceptions\SingleTableInheritanceException;
use Cruxinator\SingleTableInheritance\Tests\Fixtures\Bike;
use Cruxinator\SingleTableInheritance\Tests\Fixtures\Car;
use Cruxinator\SingleTableInheritance\Tests\Fixtures\Listing;
use Cruxinator\SingleTableInheritance\Tests\Fixtures\MotorVehicle;
use Cruxinator\SingleTableInheritance\Tests\Fixtures\MP4Video;
use Cruxinator\SingleTableInheritance\Tests\Fixtures\Taxi;
use Cruxinator\SingleTableInheritance\Tests\Fixtures\Truck;
use Cruxinator\SingleTableInheritance\Tests\Fixtures\User;
use Cruxinator\SingleTableInheritance\Tests\Fixtures\Vehicle;
use Cruxinator\SingleTableInheritance\Tests\Fixtures\Video;
use Cruxinator\SingleTableInheritance\Tests\Fixtures\VideoType;

/**
 * Class SingleTableInheritanceTraitQueryTest
 *
 * A set of tests of the query methods added to by the SingleTableInheritanceTrait
 * These tests are mostly duplicative of the model and static tests but they prove the integration
 * of the Trait with key parts of the Eloquent ORM.
 */
class QueryTest extends TestCase
{

    public function testQueryingOnRoot()
    {
        (new MotorVehicle())->save();
        (new Car())->save();
        (new Truck())->save();
        (new Truck())->save();
        (new Bike())->save();

        $results = Vehicle::all();

        $this->assertCount(5, $results);

        $this->assertInstanceOf(MotorVehicle::class, $results[0]);
        $this->assertInstanceOf(Car::class,          $results[1]);
        $this->assertInstanceOf(Truck::class,        $results[2]);
        $this->assertInstanceOf(Truck::class,        $results[3]);
        $this->assertInstanceOf(Bike::class,         $results[4]);
    }

    public function testQueryingOnChild()
    {
        (new MotorVehicle())->save();
        (new Car())->save();
        (new Truck())->save();
        (new Truck())->save();
        (new Bike())->save();

        $results = MotorVehicle::all();

        $this->assertCount(4, $results);

        $this->assertInstanceOf(MotorVehicle::class, $results[0]);
        $this->assertInstanceOf(Car::class,          $results[1]);
        $this->assertInstanceOf(Truck::class,        $results[2]);
        $this->assertInstanceOf(Truck::class,        $results[3]);
    }

    public function testQueryingOnChildOfChild()
    {
        (new MotorVehicle())->save();
        (new Car())->save();
        (new Truck())->save();
        (new Truck())->save();
        (new Bike())->save();

        $results = Truck::all();

        $this->assertCount(2, $results);

        $this->assertInstanceOf(Truck::class,        $results[0]);
        $this->assertInstanceOf(Truck::class,        $results[1]);
    }

    public function testQueryingOnLeaf()
    {

        (new MotorVehicle())->save();
        (new Car())->save();
        (new Truck())->save();
        (new Truck())->save();
        (new Bike())->save();

        $results = Car::all();

        $this->assertCount(1, $results);

        $this->assertInstanceOf(Car::class,   $results[0]);
    }

    public function testFindHasToMatchType()
    {
        $car = new Car();
        $car->save();
        $carId = $car->id;

        $this->assertNull(Truck::find($carId));
    }

    public function testFindWorksThroughParentClass()
    {
        $car = new Car();
        $car->save();
        $carId = $car->id;

        $vehicle = Vehicle::find($carId);
        $this->assertNotNull($vehicle);
        $this->assertInstanceOf(Car::class, $vehicle);
    }

    public function testTypedModelLoadedFromRelationship()
    {
        $user = new User();
        $user->name = 'Vehicle Owner';
        $user->save();
        $user->vehicles()->save(new Car());
        $user->vehicles()->save(new Bike());

        // reload the user
        $user = User::find($user->id);

        $this->assertCount(2, $user->vehicles);
        $this->assertInstanceOf(Car::class, $user->vehicles[0]);
        $this->assertInstanceOf(Bike::class, $user->vehicles[1]);
    }

    public function testIgnoreRowsWithMismatchingFieldType()
    {
        $now = Carbon::now();

        DB::table('vehicles')->insert([
            [
                'type'       => 'junk',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'type'       => 'car',
                'created_at' => $now,
                'updated_at' => $now
            ]
        ]);

        $results = Vehicle::all();
        $this->assertCount(1, $results);

        $this->assertInstanceOf(Car::class, $results[0]);
    }

    public function testOnlyPersistedAttributesAreReturnedInQuery()
    {
        $now = Carbon::now();

        DB::table('vehicles')->insert([
            [
                'type'       => 'car',
                'color'      => 'red',
                'cruft'      => 'red is my favorite',
                'owner_id'   => null,
                'created_at' => $now,
                'updated_at' => $now
            ]
        ]);

        $car = Car::all()->first();

        $this->assertNull($car->cruft);
    }

    public function testPersistedAttributesCanIncludeBelongsToForeignKeys()
    {
        $now = Carbon::now();

        $userId = DB::table('users')->insert([
            [
                'name'       => 'Mickey Mouse',
                'created_at' => $now,
                'updated_at' => $now
            ]
        ]);

        DB::table('vehicles')->insert([
            [
                'type'       => 'car',
                'color'      => 'red',
                'owner_id'   => $userId,
                'created_at' => $now,
                'updated_at' => $now
            ]
        ]);

        $car = Car::all()->first();

        $this->assertEquals($userId, $car->owner()->first()->id);
    }

    public function testEmptyPersistedAttributesReturnsEverythingInQuery()
    {
        $now = Carbon::now();

        DB::table('vehicles')->insert([
            [
                'type'       => 'car',
                'color'      => 'red',
                'cruft'      => 'red is my favorite',
                'owner_id'   => null,
                'created_at' => $now,
                'updated_at' => $now
            ]
        ]);

        $car = Car::withAllPersisted([], function () {
            return Car::all()->first();
        });

        $this->assertEquals('red is my favorite', $car->cruft);
    }

    public function testQueryThrowsExceptionIfConfigured()
    {
        $now = Carbon::now();

        DB::table('vehicles')->insert([
            [
                'type'       => 'bike',
                'color'      => 'red',
                'cruft'      => 'red is my favorite',
                'created_at' => $now,
                'updated_at' => $now
            ]
        ]);

        $this->expectException(SingleTableInheritanceException::class);

        Bike::all()->first();
    }

    public function testUpdateRemovesScope()
    {
        $car = new Car();
        $car->color = 'red';
        $car->save();

        $dbCar = Vehicle::where('color', 'red')->first();
        $dbCar->color = 'green';
        $this->assertTrue($dbCar->save()); // if the scope doesn't remove bindings this save will throw an exception.
    }


    public function testPluckNonIdProperty()
    {
        $redCar = new Car();
        $redCar->color = 'red';
        $redCar->save();

        $blueBike = new Bike();
        $blueBike->color = 'blue';
        $blueBike->save();

        $carColors = Vehicle::all()->pluck('color');

        $this->assertEquals(['red', 'blue'], $carColors->toArray());
    }

    public function testPluckId()
    {
        $redCar = new Car();
        $redCar->color = 'red';
        $redCar->save();

        $blueBike = new Bike();
        $blueBike->color = 'blue';
        $blueBike->save();

        $carIds = Vehicle::all()->pluck('id');

        $this->assertEquals([$redCar->id, $blueBike->id], $carIds->toArray());
    }
}
