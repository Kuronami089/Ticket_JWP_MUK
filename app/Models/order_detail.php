<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class order_detail extends Model
{
    use HasFactory;

    protected $table = 'order_details';
    protected $guarded = ['id'];

    public function events(){
        return $this->belongsTo(event::class, 'event_id');
    }
    public function orders(){
        return $this->belongsTo(order::class, 'order_id');
    }
}
