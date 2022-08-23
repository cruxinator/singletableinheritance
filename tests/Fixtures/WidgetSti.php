<?php


namespace Cruxinator\SingleTableInheritance\Tests\Fixtures;

use Cruxinator\SingleTableInheritance\SingleTableInheritanceTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WidgetSti extends Model
{
    use SingleTableInheritanceTrait;

    protected $table = 'widgets';

    protected static $singleTableTypeField = 'type';

    protected static $singleTableSubclasses = [

    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parentWidget(): BelongsTo
    {
        return $this->belongsTo(WidgetSti::class, 'parent_widget_id');
    }

    public function childWidgets(): HasMany
    {
        return $this->hasMany(WidgetSti::class, 'parent_widget_id');
    }
}
