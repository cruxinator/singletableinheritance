<?php


namespace Cruxinator\SingleTableInheritance\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WidgetNonSti extends Model
{
    protected $table = 'widgets';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parentWidget(): BelongsTo
    {
        return $this->belongsTo(WidgetNonSti::class, 'parent_widget_id');
    }

    public function childWidgets()
    {
        return $this->hasMany(WidgetNonSti::class, 'parent_widget_id');
    }

}