<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $table = 'menu';
    protected $fillable = ['name'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_has_menu', 'menu_id', 'user_id');
    }
}
