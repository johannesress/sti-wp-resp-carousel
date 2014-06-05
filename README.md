# STI Wordpress Resp Carousel
*v0.1*

## Installation

1. Clone or download repository to your wp-content/plugins folder
2. Activate plugin via Wordpress backend

## Usage

1. Add a carousel (its like a category for carouselpages)
2. Add a couple of carouselpages and define the carousels they belong to
3. Use the shortcode in an Wordpress editor or in your theme
4. Add your own CSS for .sti-carousel-container , .sti-carousel-item , .sti-carousel-prev and .sti-carousel-next

**In Wordpress editor**
```
[sti_carousel carousel_name="SLUG_OF_YOUR_CAROUSEL"]

or with optional size param

[sti_carousel carousel_name="SLUG_OF_YOUR_CAROUSEL" image_size="NAME_OF_YOUR_THUMBNAIL_SIZE"]

all parameters

image_size: name of a Worpress Thumbnail Size (define your own in functions.php)
fade: true or false 
autoplay: true or false 
autoplayspeed: interval in ms for autoplay
speed: speed of the transition in ms 
```

**In your theme**
```
<?php do_shortcode('[sti_carousel carousel_name="SLUG_OF_YOUR_CAROUSEL"]'); ?>
```

## Tipps

- You should install the "Simple Page Ordering" Plugin from the Wordpress Plugin Repository, for changing the order of carouselpages via drag and drop

## Thanks
This plugin includes the awesome **slick carousel**: http://kenwheeler.github.io/slick/