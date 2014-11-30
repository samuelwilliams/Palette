<?php
/*
 * This file is part of Badcow Palette Library.
 *
 * (c) Samuel Williams <sam@badcow.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Badcow\Palette;

class Converter
{
    /**
     * @param string $fromSpace
     * @param string $toSpace
     * @param array $color
     * @return mixed
     */
    public static function convert($fromSpace, $toSpace, array $color)
    {
        $func = sprintf(
            '%sTo%s',
            strtolower($fromSpace),
            ucfirst(strtolower($toSpace))
        );

        return call_user_func('self::' . $func, $color);
    }

    /**
     * @param array $rgb
     * @return array
     */
    public static function rgbToCmyk(array $rgb)
    {
        list($r, $g, $b, $a) = self::alphaPush($rgb);

        $r = ((int) $r % 256) / 255;
        $g = ((int) $g % 256) / 255;
        $b = ((int) $b % 256) / 255;

        if ($r === 0 && $g === 0 && $b===0) {
            return [0, 0, 0, 1];
        }

        $k = min(1 - $r, 1 - $g, 1 - $b);
        $c = (1 - $r - $k) / (1 - $k);
        $m = (1 - $g - $k) / (1 - $k);
        $y = (1 - $b - $k) / (1 - $k);

        return [$c*100, $m*100, $y*100, $k*100, $a];
    }

    /**
     * @param array $rgb
     * @return array
     */
    public static function rgbToHsv(array $rgb)
    {
        list($r, $g, $b, $a) = self::alphaPush($rgb);

        $r = ((int) $r % 256) / 255;
        $g = ((int) $g % 256) / 255;
        $b = ((int) $b % 256) / 255;

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $d = $max - $min;

        $h = 0;
        $s = ($max === 0) ? 0 : $d / $max;
        $v = $max;

        if ($max !== $min) {
            switch ($max) {
                case $r:
                    $h = ($g - $b) / $d + (($g < $b) ? 6 : 0);
                    break;
                case $g:
                    $h = ($b - $r) / $d + 2;
                    break;
                case $b:
                    $h = ($r - $g) / $d + 4;
            }
            $h = $h / 6;
        }

        return [$h*360, $s*100, $v*100, $a];
    }

    /**
     * @param array $rgb
     * @return array
     */
    public static function rgbToHsl(array $rgb)
    {
        list($r, $g, $b, $a) = self::alphaPush($rgb);

        $r = ((int) $r % 256) / 255;
        $g = ((int) $g % 256) / 255;
        $b = ((int) $b % 256) / 255;

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);

        $l = ($max + $min ) / 2;
        $h = 0;
        $s = 0;

        if ($max !== $min) {
            $d = $max - $min;
            $s = ($l > 0.5) ? ($d / (2-$max-$min)) : ($d / ($max + $min));
            switch ($max) {
                case $r:
                    $h = ($g - $b) / $d + (($g < $b) ? 6 : 0);
                    break;
                case $g:
                    $h = ($b - $r) / $d + 2;
                    break;
                case $b:
                    $h = ($r - $g) / $d + 4;
            }
            $h = $h / 6;
        }

        return [$h*360, $s*100, $l*100, $a];
    }

    /**
     * @param array $cmyk
     * @return array
     */
    public static function cmykToRgb(array $cmyk)
    {
        list($c, $m, $y, $k, $a) = self::alphaPush($cmyk, 5);

        $c = ((int) $c % 100) / 100;
        $m = ((int) $m % 100) / 100;
        $y = ((int) $y % 100) / 100;
        $k = ((int) $k % 100) / 100;

        $r = 1 - min(1, $c * (1 - $k) + $k);
        $g = 1 - min(1, $m * (1 - $k) + $k);
        $b = 1 - min(1, $y * (1 - $k) + $k);

        return [
            (int) $r * 255,
            (int) $g * 255,
            (int) $b * 255,
            $a
        ];
    }

    /**
     * @param array $cmyk
     * @return array
     */
    public static function cmykToHsv(array $cmyk)
    {
        return self::rgbToHsv(self::cmykToRgb($cmyk));
    }

    /**
     * @param array $cmyk
     * @return array
     */
    public static function cmykToHsl(array $cmyk)
    {
        return self::rgbToHsl(self::cmykToRgb($cmyk));
    }

    /**
     * @param array $hsv
     * @return array
     */
    public static function hsvToRgb(array $hsv)
    {
        list($h, $s, $v, $a) = self::alphaPush($hsv);

        $h = ($h % 360) / 360;
        $s = ($s % 101) / 100;
        $v = ($v % 101) / 100;

        $i = $h * 6;
        $f = $h * 6 - $i;
        $p = $v * (1 - $s);
        $q = $v * (1 - $f * $s);
        $t = $v * (1 - (1 - $f) * $s);

        switch ($i % 6) {
            case 0:
                $r = $v;
                $g = $t;
                $b = $p;
                break;
            case 1:
                $r = $q;
                $g = $v;
                $b = $p;
                break;
            case 2:
                $r = $p;
                $g = $v;
                $b = $t;
                break;
            case 3:
                $r = $p;
                $g = $q;
                $b = $v;
                break;
            case 4:
                $r = $t;
                $g = $p;
                $b = $v;
                break;
            default:
                $r = $v;
                $g = $p;
                $b = $q;
        }

        return [$r * 255, $g * 255, $b * 255, $a];
    }

    /**
     * @param array $hsv
     * @return array
     */
    public static function hsvToCmyk(array $hsv)
    {
        return self::rgbToCmyk(self::hsvToRgb($hsv));
    }

    /**
     * @param array $hsv
     * @return array
     */
    public static function hsvToHsl(array $hsv)
    {
        return self::rgbToHsl(self::hsvToRgb($hsv));
    }

    /**
     * @param array $hsl [H, S, L, A]
     * @return array [R, G, B, A]
     */
    public static function hslToRgb(array $hsl)
    {
        if (count($hsl) < 4) {
            array_push($hsl, 1);
        }

        list($h, $s, $l, $a) = $hsl;

        $h = ($h % 360) / 360;
        $s = ($s % 101) / 100;
        $l = ($l % 101) / 100;

        if ($s === 0) {
            return [$l * 255, $l * 255, $l * 255, $a];
        }

        $q = ($l < 0.5) ? $l * (1 + $s) : $l + $s - $l * $s;
        $p = 2 * $l - $q;

        return [
            (int) static::hueToFactor($p, $q, $h + 1 / 3) * 255,
            (int) static::hueToFactor($p, $q, $h        ) * 255,
            (int) static::hueToFactor($p, $q, $h - 1 / 3) * 255,
            $a,
        ];
    }

    /**
     * @param array $hsl
     * @return array
     */
    public static function hslToCmyk(array $hsl)
    {
        return self::rgbToCmyk(self::hslToRgb($hsl));
    }

    /**
     * @param array $hsl
     * @return array
     */
    public static function hslToHsv(array $hsl)
    {
        return self::rgbToHsv(self::hslToRgb($hsl));
    }

    /**
     * @param int $p
     * @param int $q
     * @param int $t
     * @return float
     */
    private static function hueToFactor($p, $q, $t)
    {
        if ($t < 0) {
            $t += 1;
        }

        if ($t > 1) {
            $t -= 1;
        }

        if ($t < 1 / 6) {
            return $p + ($q - $p) * 6 * $t;
        }

        if ($t < 1 / 2){
            return $q;
        }

        if ($t < 2 / 3) {
            return $p + ($q - $p) * (2/3 - $t) * 6;
        }

        return $p;
    }

    /**
     * @param array $color
     * @param int $len
     * @return array
     */
    private static function alphaPush(array $color, $len = 4)
    {
        if (count($color) < $len) {
            array_push($color, 1);
        }

        return $color;
    }
} 