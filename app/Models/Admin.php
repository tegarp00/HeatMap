<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class Admin extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $table = 'admin';

    protected static function boot()
    {
        parent::boot();

        static::creating(function(Admin $admin){
            $admin->password = Hash::make($admin->password);
        });

        static::updating(function(Admin $admin) {
            if($admin->isDirty(["password"])) {
                $admin->password = Hash::make($admin->password);
            }
        });
    }
}
