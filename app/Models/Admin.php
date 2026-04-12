<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    protected $primaryKey = 'Admin_ID'; // Crucial since it's not the default 'id'

    protected $fillable = [
        'AdminFirstName',
        'AdminMiddleName',
        'AdminLastName',
        'AdminEmail',
        'AdminPasswordHash',
        'AdminRole',
    ];
}