<?php

namespace App\Support;

/**
 * Links that open a coordinate in the map apps used in Uzbekistan.
 */
class Maps
{
    public static function google(float $latitude, float $longitude): string
    {
        return "https://www.google.com/maps?q={$latitude},{$longitude}";
    }

    public static function yandex(float $latitude, float $longitude): string
    {
        return "https://yandex.uz/maps/?pt={$longitude},{$latitude}&z=17&l=map";
    }
}
