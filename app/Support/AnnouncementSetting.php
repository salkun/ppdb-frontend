<?php

namespace App\Support;

use Illuminate\Support\Facades\File;

class AnnouncementSetting
{
    private const DEFAULTS = [
        'enabled' => false,
        'title' => '',
        'message' => '',
    ];

    private static function path(): string
    {
        return storage_path('app/announcement_setting.json');
    }

    public static function get(): array
    {
        $saved = File::exists(self::path()) ? json_decode(File::get(self::path()), true) : [];

        return array_merge(self::DEFAULTS, array_intersect_key((array) $saved, self::DEFAULTS));
    }

    public static function save(array $data): void
    {
        File::put(self::path(), json_encode(array_intersect_key($data, self::DEFAULTS), JSON_PRETTY_PRINT));
    }
}
