<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Role as ModelsRole;

class Role extends ModelsRole {
    use HasFactory;

    // public static function boot() {
    //     parent::boot();

    //     static::deleting(function($role) {
    //         //  $role->permissions()->dettach();
    //         //  $role->users()->dettach();
    //          // do the rest of the cleanup...
    //     });
    // }
}