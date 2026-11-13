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

    private static function path(string $scope = 'dashboard'): string
    {
        return storage_path($scope === 'landing' ? 'app/announcement_landing_setting.json' : 'app/announcement_setting.json');
    }

    public static function get(string $scope = 'dashboard'): array
    {
        $saved = File::exists(self::path($scope)) ? json_decode(File::get(self::path($scope)), true) : [];

        return array_merge(self::DEFAULTS, array_intersect_key((array) $saved, self::DEFAULTS));
    }

    public static function save(array $data, string $scope = 'dashboard'): void
    {
        File::put(self::path($scope), json_encode(array_intersect_key($data, self::DEFAULTS), JSON_PRETTY_PRINT));
    }
}
