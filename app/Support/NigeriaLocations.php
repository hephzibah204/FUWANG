<?php

namespace App\Support;

final class NigeriaLocations
{
    private static ?array $map = null;

    /**
     * @return array<string, list<string>> State name => cities/towns (from public/data JSON)
     */
    public static function stateToCityMap(): array
    {
        if (self::$map !== null) {
            return self::$map;
        }

        $path = public_path('data/nigeria-state-cities.json');
        if (! is_readable($path)) {
            return self::$map = [];
        }

        $decoded = json_decode((string) file_get_contents($path), true);
        if (! is_array($decoded)) {
            return self::$map = [];
        }

        $map = [];
        foreach ($decoded as $row) {
            if (! is_array($row) || ! isset($row['state'])) {
                continue;
            }
            $state = (string) $row['state'];
            $list = $row['cities'] ?? $row['lgas'] ?? null;
            if (! is_array($list)) {
                continue;
            }
            $cities = array_values(array_unique(array_map('strval', $list)));
            sort($cities, SORT_STRING);
            $map[$state] = $cities;
        }

        ksort($map, SORT_STRING);

        return self::$map = $map;
    }

    /**
     * @return list<string>
     */
    public static function stateNames(): array
    {
        return array_keys(self::stateToCityMap());
    }
}
