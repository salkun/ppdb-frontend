<?php

namespace App\Support;

use Illuminate\Support\Facades\File;

class BankSetting
{
    private const DEFAULTS = [
        'bank_name' => 'Bank Mandiri',
        'account_number' => '1730004960275',
        'account_holder' => 'PUTRI MUNAWWAROH',
    ];

    private static function path(): string
    {
        return storage_path('app/bank_setting.json');
    }

    public static function get(): array
    {
        $saved = File::exists(self::path()) ? json_decode(File::get(self::path()), true) : [];

        return array_merge(self::DEFAULTS, array_filter((array) $saved, fn ($v) => $v !== null && $v !== ''));
    }

    public static function save(array $data): void
    {
        File::put(self::path(), json_encode(array_intersect_key($data, self::DEFAULTS), JSON_PRETTY_PRINT));
    }
}
