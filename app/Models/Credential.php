<?php

namespace App\Models;

use App\Models\Concerns\HasLocalizedFields;
use Database\Factories\CredentialFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['title', 'institution', 'description', 'kind', 'start_year', 'end_year', 'sort_order', 'is_active'])]
class Credential extends Model
{
    /** @use HasFactory<CredentialFactory> */
    use HasFactory, HasLocalizedFields;

    protected function casts(): array
    {
        return [
            'title' => 'array',
            'institution' => 'array',
            'description' => 'array',
            'start_year' => 'integer',
            'end_year' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
