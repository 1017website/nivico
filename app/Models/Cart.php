<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = ['user_id', 'session_id', 'promo_id'];

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    public function promo()
    {
        return $this->belongsTo(Promo::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subtotal(): int
    {
        return (int) $this->items->sum(fn ($i) => $i->effectivePrice() * $i->qty);
    }

    public function totalQty(): int
    {
        return (int) $this->items->sum('qty');
    }
}
