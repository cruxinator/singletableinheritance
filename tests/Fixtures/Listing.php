<?php


namespace Cruxinator\SingleTableInheritance\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Listing extends Model
{

    public function vehicles(): BelongsToMany
    {
        return $this->belongsToMany(Vehicle::class);
    }
}
