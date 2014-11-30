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

class Color
{
    const RGB = 'RGB';

    const HSV = 'HSV';

    const CMYK = 'CMYK';

    const HSL = 'HSL';

    /**
     * @var array
     */
    private $rgb;

    /**
     * @var array
     */
    private $hsl;

    /**
     * @var array
     */
    private $cmyk;

    /**
     * @var array
     */
    private $hsv;

    /**
     * @var string
     */
    private $originalSpace;

    /**
     * @param array $value
     * @param string $space
     * @throws \Exception
     */
    public function __construct(array $value, $space = self::RGB)
    {
        $this->originalSpace = $space;

        if (count($value) < 4 && $value !== self::CMYK) {
            array_push($value, 1);
        }

        if (count($value) < 5 && $value === self::CMYK) {
            array_push($value, 1);
        }

        switch ($space) {
            case self::RGB:
                $this->rgb = $value;
                break;
            case self::CMYK:
                $this->cmyk= $value;
                break;
            case self::HSV:
                $this->hsv = $value;
                break;
            case self::HSL:
                $this->hsl = $value;
                break;
            default:
                throw new \Exception('No such space');
        }
    }

    /**
     * @return array
     */
    public function cmyk()
    {
        return $this->convert(static::CMYK);
    }

    /**
     * @return array
     */
    public function rgb()
    {
        return $this->convert(static::RGB);
    }

    /**
     * @return array
     */
    public function hsv()
    {
        return $this->convert(static::HSV);
    }

    /**
     * @return array
     */
    public function hsl()
    {
        return $this->convert(static::HSL);
    }

    /**
     * @param $space
     * @return array
     */
    private function convert($space)
    {
        if (isset($this->{strtolower($space)})) {
            return $this->{strtolower($space)};
        }

        return Converter::convert($this->originalSpace, $space, $this->{strtolower($this->originalSpace)});
    }
}