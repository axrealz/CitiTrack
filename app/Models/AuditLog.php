<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Shown to users as "Action Logs".
 * Class, table (audit_logs) and routes keep their original names on purpose.
 */
class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    private const SENSITIVE_KEYS = '/password|passwd|token|secret|api_?key|app_?key|remember/i';

    private const HIDDEN_IN_DETAILS = ['id', 'created_at', 'updated_at', 'deleted_at', 'email_verified_at'];

    private const MAX_DETAIL_LINES = 6;

    protected static function booted(): void
    {
        // Strip sensitive keys from every log entry on save
        static::saving(function (AuditLog $log) {
            $log->old_values = self::scrub($log->old_values);
            $log->new_values = self::scrub($log->new_values);
        });

        // Log entries are history: never edited or deleted through the model
        static::updating(fn () => false);
        static::deleting(fn () => false);
    }

    /**
     * A failure to write a log entry must never break the real operation.
     * The error goes to storage/logs/laravel.log and the app carries on.
     */
    public function save(array $options = []): bool
    {
        try {
            return parent::save($options);
        } catch (\Throwable $e) {
            report($e);

            return false;
        }
    }

    private static function scrub(?array $values): ?array
    {
        if ($values === null) {
            return null;
        }

        $clean = [];

        foreach ($values as $key => $value) {
            if (is_string($key) && preg_match(self::SENSITIVE_KEYS, $key)) {
                continue;
            }

            $clean[$key] = is_array($value) ? self::scrub($value) : $value;
        }

        return $clean;
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function getChangedKeysAttribute(): array
    {
        $old = $this->old_values ?? [];
        $new = $this->new_values ?? [];

        return array_unique(array_merge(array_keys($old), array_keys($new)));
    }

    /** Short readable lines for the "Details" column of the Action Logs page. */
    public function getDetailsAttribute(): array
    {
        $old = self::scrub($this->old_values) ?? [];
        $new = self::scrub($this->new_values) ?? [];
        $lines = [];

        if ($old && $new) {
            foreach ($new as $key => $value) {
                if (in_array($key, self::HIDDEN_IN_DETAILS, true)) {
                    continue;
                }

                if (! array_key_exists($key, $old) || self::flat($old[$key]) !== self::flat($value)) {
                    $lines[] = self::label($key) . ': ' . self::flat($old[$key] ?? null) . ' → ' . self::flat($value);
                }
            }
        } else {
            foreach (($old ?: $new) as $key => $value) {
                if (in_array($key, self::HIDDEN_IN_DETAILS, true) || $value === null || $value === '') {
                    continue;
                }

                $lines[] = self::label($key) . ': ' . self::flat($value);
            }
        }

        if (count($lines) > self::MAX_DETAIL_LINES) {
            $extra = count($lines) - self::MAX_DETAIL_LINES;
            $lines = array_slice($lines, 0, self::MAX_DETAIL_LINES);
            $lines[] = "+{$extra} more";
        }

        return $lines;
    }

    private static function label(string|int $key): string
    {
        return Str::ucfirst(str_replace('_', ' ', (string) $key));
    }

    private static function flat(mixed $value): string
    {
        if ($value === null) {
            return '—';
        }

        if (is_bool($value)) {
            return $value ? 'yes' : 'no';
        }

        if (is_array($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        return Str::limit((string) $value, 60);
    }

    public function scopeOfAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }
}