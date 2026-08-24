<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscriber extends Model
{
    use HasFactory;

    /**
     * Les attributs assignables en masse (mass assignment).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
    ];
}