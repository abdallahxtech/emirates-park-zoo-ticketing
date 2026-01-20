<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $fillable = [
        'brand_name',
        'primary_color',
        'logo_path',
        'welcome_message',
    ];
}
