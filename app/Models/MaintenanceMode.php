<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceMode extends Model
{
    use HasFactory;

    protected $table = 'maintenance_mode';

    protected $fillable = [
        'is_enabled',
        'message',
        'scheduled_start',
        'scheduled_end',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'scheduled_start' => 'datetime',
        'scheduled_end' => 'datetime',
    ];

    public static function isEnabled(): bool
    {
        $maintenance = self::first();
        if (!$maintenance) {
            return false;
        }

        // Check if maintenance is manually enabled
        if ($maintenance->is_enabled) {
            return true;
        }

        // Check if we're within scheduled maintenance window
        if ($maintenance->scheduled_start && $maintenance->scheduled_end) {
            $now = now();
            return $now->between($maintenance->scheduled_start, $maintenance->scheduled_end);
        }

        return false;
    }

    public static function getMessage(): ?string
    {
        $maintenance = self::first();
        return $maintenance?->message;
    }

    public static function getStatus(): array
    {
        $maintenance = self::first();
        if (!$maintenance) {
            return [
                'is_enabled' => false,
                'message' => null,
                'scheduled_start' => null,
                'scheduled_end' => null,
            ];
        }

        return [
            'is_enabled' => self::isEnabled(),
            'message' => $maintenance->message,
            'scheduled_start' => $maintenance->scheduled_start,
            'scheduled_end' => $maintenance->scheduled_end,
        ];
    }
}