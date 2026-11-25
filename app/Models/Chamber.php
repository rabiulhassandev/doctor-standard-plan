<?php

namespace App\Models;

use App\Models\Concerns\HasLocalizedFields;
use Database\Factories\ChamberFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'hospital_name', 'address', 'notice', 'slug', 'phone', 'serial_phone', 'map_url', 'latitude', 'longitude', 'sort_order', 'is_active'])]
class Chamber extends Model
{
    /** @use HasFactory<ChamberFactory> */
    use HasFactory, HasLocalizedFields;

    protected function casts(): array
    {
        return [
            'name' => 'array',
            'hospital_name' => 'array',
            'address' => 'array',
            'notice' => 'array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function visitingWindows(): HasMany
    {
        return $this->hasMany(VisitingWindow::class)->orderBy('weekday')->orderBy('starts_at')->orderBy('id');
    }
}
