<?php


namespace Cruxinator\SingleTableInheritance\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * @method static find(mixed $id)
 * @property mixed|string $name
 * @property int $id
 */
class User extends Model
{

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class, 'owner_id');
    }

    public function plainWidgets(): HasMany
    {
        return $this->hasMany(WidgetNonSti::class, 'user_id');
    }

    public function kidPlainWidgets(): HasManyThrough
    {
        // brute force over ignorance, but this works how the docs led me to believe
        // have mallet, will travel
        // TODO: Review when upgrading to Laravel 9.x
        $res = $this->hasManyThrough(
            WidgetNonSti::class,
            WidgetNonSti::class,
            'mid_widgets.user_id',
            'parent_widget_id',
            null,
            'mid_widgets.id'
        );
        $res->toBase()->from = 'widgets as mid_widgets';

        return $res;
    }

    public function stiWidgets(): HasMany
    {
        return $this->hasMany(WidgetSti::class, 'user_id');
    }

    public function kidStiWidgets(): HasManyThrough
    {
        $res = $this->hasManyThrough(
            WidgetSti::class,
            WidgetSti::class,
            'mid_widgets.user_id',
            'parent_widget_id',
            null,
            'mid_widgets.id'
        );
        $res->toBase()->joins[0]->table = 'widgets as mid_widgets';

        return $res;
    }
}
