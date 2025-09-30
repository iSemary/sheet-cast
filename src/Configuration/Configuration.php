<?php

namespace App\Configuration;

class Configuration
{
    private static array $configs = [];

    /**
     * Get configuration array for a specific module
     */
    public static function get(string $module): array
    {
        if (!isset(self::$configs[$module])) {
            $configPath = __DIR__ . "/../../config/{$module}.php";

            if (!file_exists($configPath)) {
                throw new \InvalidArgumentException("Configuration file not found: {$configPath}");
            }

            self::$configs[$module] = require $configPath;
        }

        return self::$configs[$module];
    }

}
