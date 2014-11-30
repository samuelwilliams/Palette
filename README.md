Palette
=======

A library to represent colors and convert between different color spaces.

The library currently comprises of two classes: the `Converter` class, which converts colors from one space to another, and the `Color` class, which is an immutable object representation of a color.

Usage
-----

To use the converter:

    use Badcow\Palette\Converter;
    
    $cmyk = Converter::rgbToCmyk([42, 53, 122, 0.3]); \\ array(65.6, 56.6, 0, 52.2)
  
To use the color class:

    use Badcow\Palette\Color;
    
    $sky_blue = new Color([43, 12, 0, 8], Color::CMYK);
    print_r($sky_blue->rgb());
  
Warning!
--------
This is still a work in progress. Be careful if you want to send it live!

