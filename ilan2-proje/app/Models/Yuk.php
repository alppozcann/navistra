<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Yuk extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'title', // İlan başlığı
        'yuk_type', // Yük türü
        'weight', // Ağırlık (kg)
        'dimensions', // Boyutlar (JSON formatında - en, boy, yükseklik)
        'from_location', // Yükün alınacağı yer
        'to_location', // Yükün teslim edileceği yer
        'proposed_price', // Teklif edilen fiyat
        'desired_delivery_date', // İstenen teslimat tarihi
        'description', // Detaylı açıklama
        'status', // İlan durumu (active, matched, completed, cancelled)
        'matched_gemi_route_id', // Eşleştirildiği gemi rotası
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'dimensions' => 'array',
        'desired_delivery_date' => 'datetime',
    ];

    /**
     * İlanı oluşturan kullanıcı
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Eşleştirildiği gemi rotası
     */
    public function matchedGemiRoute()
    {
        return $this->belongsTo(GemiRoute::class, 'matched_gemi_route_id');
    }
}
