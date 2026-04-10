<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActiveIngredient extends Model
{
    protected $fillable = [
        'stt',
        'name',
        'dosage_form',
        'hospital_level',
        'note',
        'drug_group',
    ];

    protected $casts = [
        'stt' => 'integer',
        'hospital_level' => 'integer',
    ];

    // 🔥 Scope search chuẩn
    public function scopeSearch($query, $term)
    {
        if (!$term) return $query;

        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('note', 'like', "%{$term}%");
        });
    }
}
