<?php


namespace Cruxinator\SingleTableInheritance\Tests\Fixtures;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Cruxinator\SingleTableInheritance\SingleTableInheritanceTrait;

/**
 * @method static find(int $carId)
 * @method static where(string $string, string $string1)
 */
class Vehicle extends Model
{
    use SingleTableInheritanceTrait;

    protected $table = 'vehicles';

    protected static $singleTableTypeField = 'type';

    protected static $persisted = ['color', 'owner_id'];

    protected static $singleTableSubclasses = [
        MotorVehicle::class,
        Bike::class
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // testing hooks to manipulate protected properties from a public context
    public static function withAllPersisted($persisted, $closure)
    {
        $oldPersisted = static::$allPersisted[get_called_class()];

        static::$allPersisted[get_called_class()] = $persisted;

        $result = null;
        try {
            $result = $closure();
        } catch (Exception $e) {

        }
        static::$allPersisted[get_called_class()] = $oldPersisted;
        return $result;
    }

    public static function withTypeField($typeField, $closure)
    {
        $oldTypeField = static::$singleTableTypeField;
        static::$singleTableTypeField = $typeField;

        $result = null;
        try {
            $result = $closure();
        } catch (Exception $e) {

        }
        static::$singleTableTypeField = $oldTypeField;

        return $result;
    }

    public function setDates(array $dates)
    {
        $this->dates = $dates;
    }

    public function setPrimaryKey($primaryKey)
    {
        $this->primaryKey = $primaryKey;
    }

    public function setTable($table)
    {
        $this->table = $table;
    }
}
