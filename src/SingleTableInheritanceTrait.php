<?php


namespace Cruxinator\SingleTableInheritance;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Cruxinator\SingleTableInheritance\Exceptions\SingleTableInheritanceException;
use Cruxinator\SingleTableInheritance\Exceptions\InvalidAttributesException;
use Cruxinator\SingleTableInheritance\Exceptions\WrongInheritanceException;
use Cruxinator\SingleTableInheritance\Strings\MyStr;
use ReflectionClass;

/**
 * Trait SingleTableInheritanceTrait model needs
 * bool $throwInvalidAttributeExceptions to define if it should throw invalid attribute exception
 * string $singleTableTypeField a field that defines the object type. the method getSingleTableTypeField():string can be defined as an alternative
 * string[] $singleTableSubclasses a list of objects that inherit from the parent. the method getSingleTableSubclasses can be defined as an alternative
 * @package Packages\SingleTableInheritance
 * @property string[] $singleTableSubclasses
 * @property string $singleTableTypeField
 * @property string $singleTableType a type entry that can be used to differentiate between classes
 * @mixin Model
 */
trait SingleTableInheritanceTrait
{
    //TODO: Overload fireModelEvent to also fire events from parent objects
    //TODO: Enable declare(strict_types)
    /**
     * A cache of all the class types strings to class names.
     * A map of model class name to map of type to subclass name.
     *
     * @var array
     */
    protected static $singleTableTypeMap = [];

    /**
     * A cache of all the persisted attributes associated of each class including super class attributes.
     * A map of model class name to attribute name array.
     *
     * @var array
     */
    protected static $allPersisted = [];

    /**
     * Boot the trait.
     *
     * @return void
     * @throws WrongInheritanceException
     */
    public static function bootSingleTableInheritanceTrait(): void
    {

        static::getSingleTableTypeMap();
        static::getAllPersistedAttributes();

        static::addGlobalScope(new SingleTableInheritanceScope);

        static::observe(new SingleTableInheritanceObserver());
    }

    /**
     * Get the map of type field values to class names.
     *
     * @return array the type map
     * @throws WrongInheritanceException
     */
    public static function getSingleTableTypeMap(): array
    {
        $calledClass = get_called_class();

        if (array_key_exists($calledClass, self::$singleTableTypeMap)) {
            return self::$singleTableTypeMap[$calledClass];
        }

        $typeMap = [];

        // Check if the calledClass is a leaf of the hierarchy. singleTableSubclasses will be inherited from the parent class
        // so it's important we check for the tableType first, otherwise we'd infinitely recurse.
        if (property_exists($calledClass, 'singleTableType')) {
            /** @noinspection PhpUndefinedFieldInspection */
            $classType = static::$singleTableType;
            $typeMap[$classType] = $calledClass;
        } elseif (method_exists($calledClass, 'getSingleTableType')) {
            /** @noinspection PhpUndefinedMethodInspection */
            $classType = static::getSingleTableType();
            $typeMap[$classType] = $calledClass;
        } elseif (!(new ReflectionClass(static::class))->isAbstract()) {
            $typeMap[static::class] = $calledClass;
        }
        $subclasses = null;
        if (property_exists($calledClass, 'singleTableSubclasses')) {
            $subclasses = static::$singleTableSubclasses;
        } elseif (method_exists($calledClass, 'getSingleTableSubclasses')) {
            /** @noinspection PhpUndefinedMethodInspection */
            $subclasses = static::getSingleTableSubclasses();
        }
        if (null !== $subclasses) {
            // prevent infinite recursion if the singleTableSubclasses is inherited
            if (!in_array($calledClass, $subclasses)) {
                foreach ($subclasses as $subclass) {
                    if (!is_subclass_of($subclass, $calledClass)) {
                        throw new WrongInheritanceException('Subclass must extend its parent class.');
                    }
                    $typeMap = $typeMap + $subclass::getSingleTableTypeMap();
                }
            }
        }
        self::$singleTableTypeMap[$calledClass] = $typeMap;

        return $typeMap;
    }

    /**
     * Get all the persisted attributes that belongs to the class inheriting values declared on super classes
     *
     * @return array
     */
    public static function getAllPersistedAttributes(): array
    {
        $calledClass = get_called_class();

        if (array_key_exists($calledClass, self::$allPersisted)) {
            return self::$allPersisted[$calledClass];
        } else {
            $persisted = [];
            if (property_exists($calledClass, 'persisted')) {
                $persisted = $calledClass::$persisted;
            }
            $parent = get_parent_class($calledClass);
            if (method_exists($parent, 'getAllPersistedAttributes')) {
                $persisted = array_merge($persisted, $parent::getAllPersistedAttributes());
            }
        }
        self::$allPersisted[$calledClass] = $persisted;
        return self::$allPersisted[$calledClass];
    }

    /**
     * Get the list of persisted attributes on this model inheriting values declared on super classes and
     * including the model's primary key and any date fields.
     * @return array
     */
    public function getPersistedAttributes(): array
    {
        $persisted = static::getAllPersistedAttributes();
        if (empty($persisted)) {
            // if the static persisted declaration is empty return empty
            return [];
        } else {
            // otherwise add the instance variables for primaryKey, typeField and dates
            return array_merge([$this->primaryKey, static::$singleTableTypeField], static::getAllPersistedAttributes(), $this->getDates());
        }
    }

    /**
     * Filter the attributes on the model. Any attribute that is not in the list of persisted attributes will be set to null.
     * Called before the model is saved to prevent setting spurious data in the database for columns belonging to other models.
     * If the flag $throwInvalidAttributeExceptions is set to true then this method will throw exceptions if it finds
     * attributes that are not expected to be persisted.
     * @throws InvalidAttributesException
     */
    public function filterPersistedAttributes()
    {
        $persisted = $this->getPersistedAttributes();
        $extraAttributes = null;
        // if $persisted is empty we don't filter
        if (!empty($persisted)) {
            $extraAttributes = array_diff(array_keys($this->attributes), $this->getPersistedAttributes());

            if (!empty($extraAttributes)) {
                if ($this->getThrowInvalidAttributeExceptions()) {
                    throw new InvalidAttributesException('Cannot save ' . get_called_class() . '.', $extraAttributes);
                }
                foreach ($extraAttributes as $attribute) {
                    unset($this->attributes[$attribute]);
                }
            }
        }
    }

    /**
     * Get the list of all types in the hierarchy.
     * @return array the list of type strings
     * @throws WrongInheritanceException
     */
    public function getSingleTableTypes(): array
    {
        return array_keys(static::getSingleTableTypeMap());
    }

    /**
     * Set the type value into the type field attribute
     * @throws SingleTableInheritanceException
     */
    public function setSingleTableType()
    {
        $modelClass = get_class($this);
        $classType = property_exists($modelClass, 'singleTableType') ? $modelClass::$singleTableType : null;
        if ($classType !== null) {
            if ($this->hasGetMutator(static::$singleTableTypeField)) {
                $this->{static::$singleTableTypeField} = $this->mutateAttribute(static::$singleTableTypeField, $classType);
            } else {
                $this->{static::$singleTableTypeField} = $classType;
            }
        } elseif (!(new ReflectionClass(static::class))->isAbstract()) {
            $this->{static::$singleTableTypeField} = static::class;
        } else {
            // We'd like to be able to declare non-leaf classes in the hierarchy as abstract so they can't be instantiated and saved.
            // However, Eloquent expects to instantiate classes at various points. Therefore throw an exception if we try to save
            // and instance that doesn't have a type.
            throw new SingleTableInheritanceException('Cannot save Single table inheritance model without declaring static property $singleTableType.');
        }
    }

    /**
     * Override the Eloquent method to construct a model of the type given by the value of singleTableTypeField
     * @param array $attributes
     * @param null $connection
     * @return Builder
     * @throws SingleTableInheritanceException
     * @throws WrongInheritanceException
     */
    public function newFromBuilder($attributes = array(), $connection = null)
    {
        $typeField = static::$singleTableTypeField;
        $attributes = (array)$attributes;

        $classType = array_key_exists($typeField, $attributes) ? $attributes[$typeField] : null;

        if ($classType !== null) {
            $childTypes = static::getSingleTableTypeMap();
            if (array_key_exists($classType, $childTypes)) {
                $class = $childTypes[$classType];
                $instance = (new $class)->newInstance([], true);
                $instance->setFilteredAttributes($attributes);
                $instance->setConnection($connection ?: $this->getConnectionName());
                $instance->fireModelEvent('retrieved', false);
                return $instance;
            } else {
                // Something has gone very wrong with the Global Scope
                // There is not graceful recovery so complain loudly.
                throw new SingleTableInheritanceException("Cannot construct newFromBuilder for unrecognized $typeField=$classType");
            }
        } else {
            // There are some cases, like Model::pluck('id'), where $attributes does not contain classType
            // In those situations defer to the original implementation.
            return parent::newFromBuilder($attributes, $connection);
        }
    }

    /**
     * Get the qualified name of the column used to store the class type.
     * @return string the qualified column name
     */
    public function getQualifiedSingleTableTypeColumn(): string
    {
        return $this->getTable() . '.' . static::$singleTableTypeField;
    }

    /**
     * @param array $attributes
     * @throws InvalidAttributesException
     */
    public function setFilteredAttributes(array $attributes)
    {
        $persistedAttributes = $this->getPersistedAttributes();

        if (empty($persistedAttributes)) {
            $filteredAttributes = $attributes;
        } else {
            // The query often include a 'select *' from the table which will return null for columns that are not persisted.
            // If any of those columns are non-null then we need to filter them our or throw and exception if configured.
            // array_flip is a cute way to do diff/intersection on keys by a non-associative array
            $extraAttributes = array_filter(array_diff_key($attributes, array_flip($persistedAttributes)), function ($value) {
                return !is_null($value);
            });
            if (!empty($extraAttributes) && $this->getThrowInvalidAttributeExceptions()) {
                throw new InvalidAttributesException('Cannot construct ' . get_called_class() . '.', $extraAttributes);
            }
            // Make sure to include all pivot attributes so we hydrate many-to-many relationships correctly
            $persistedAttributes += $this->getPivotAttributeNames($attributes);
            $filteredAttributes = array_intersect_key($attributes, array_flip($persistedAttributes));
        }

        $this->setRawAttributes($filteredAttributes, true);
    }

    protected function getPivotAttributeNames($attributes): array
    {
        return array_filter(array_keys($attributes), function ($key) {
            return MyStr::startsWith($key, 'pivot_');
        });
    }

    protected function getThrowInvalidAttributeExceptions(): bool
    {
        return property_exists(get_called_class(), 'throwInvalidAttributeExceptions') ? static::$throwInvalidAttributeExceptions : false;
    }
}