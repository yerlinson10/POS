<?php

namespace App\Models;

use App\Traits\HasAdvancedFilters;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class PosSession extends Model
{
    /** @use HasFactory<\Database\Factories\PosSessionFactory> */
    use HasFactory, HasAdvancedFilters;

    protected $fillable = [
        'user_id',
        'opened_at',
        'closed_at',
        'initial_cash',
        'final_cash',
        'expected_cash',
        'cash_difference',
        'opening_notes',
        'closing_notes',
        'status',
        'cash_breakdown'
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'initial_cash' => 'decimal:2',
        'final_cash' => 'decimal:2',
        'expected_cash' => 'decimal:2',
        'cash_difference' => 'decimal:2',
        'cash_breakdown' => 'array',
    ];

    // Relaciones
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    // Scopes
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('opened_at', Carbon::today());
    }

    // Métodos de utilidad
    public function isOpen()
    {
        return $this->status === 'open';
    }

    public function isClosed()
    {
        return $this->status === 'closed';
    }

    public function getDurationAttribute()
    {
        if (!$this->closed_at) {
            return Carbon::parse($this->opened_at)->diffForHumans(null, true);
        }

        return Carbon::parse($this->opened_at)->diffForHumans($this->closed_at, true);
    }

    public function getTotalSalesAttribute()
    {
        return $this->invoices()->where('status', 'paid')->sum('total_amount');
    }

    public function getCashSalesAttribute()
    {
        return $this->invoices()
            ->where('status', 'paid')
            ->where('payment_method', 'cash')
            ->sum('total_amount');
    }

    public function getCardSalesAttribute()
    {
        return $this->invoices()
            ->where('status', 'paid')
            ->where('payment_method', 'card')
            ->sum('total_amount');
    }

    public function getSalesCountAttribute()
    {
        return $this->invoices()->where('status', 'paid')->count();
    }

    // Métodos estáticos
    public static function getActiveSession($userId = null)
    {
        $query = static::open();

        if ($userId) {
            $query->forUser($userId);
        }

        return $query->latest('opened_at')->first();
    }

    public static function hasActiveSession($userId = null)
    {
        return static::getActiveSession($userId) !== null;
    }

    public static function openSession($data)
    {
        // Cerrar cualquier sesión abierta del usuario
        if (isset($data['user_id'])) {
            static::open()->forUser($data['user_id'])->update([
                'status' => 'closed',
                'closed_at' => now(),
                'closing_notes' => 'Sesión cerrada automáticamente al abrir nueva sesión'
            ]);
        }

        return static::create(array_merge($data, [
            'opened_at' => now(),
            'status' => 'open'
        ]));
    }

    public function closeSession($data = [])
    {
        $this->update(array_merge($data, [
            'closed_at' => now(),
            'status' => 'closed'
        ]));

        return $this;
    }
}
