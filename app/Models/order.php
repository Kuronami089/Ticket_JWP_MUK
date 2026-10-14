<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class order extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function order_details(){
        return $this->hasMany(order_detail::class, 'order_id');
    }
}
