## Custom Post Type Carousel
Display a lightweight, responsive carousel for any registered post type using a shortcode. Powered by Tiny Slider (no jQuery).

### Installation
- Copy this folder `custom-post-type-carousel` into your WordPress `wp-content/plugins/` directory.
- In the WP admin, go to Plugins and activate "Custom Post Type Carousel".

### Shortcode
Use the shortcode anywhere (posts, pages, widgets, block editor shortcode block):

```
[cpt_carousel]
```

#### Attributes
- **type**: post type slug. Default: `post`
- **posts_per_page**: number of items to fetch. Default: `10`
- **orderby**: WP_Query `orderby`. Default: `date`
- **order**: `ASC` or `DESC`. Default: `DESC`
- **taxonomy**: taxonomy to filter by (e.g., `category`, `product_cat`). Default: empty
- **terms**: comma-separated term slugs (e.g., `news,events`). Default: empty
- **items**: items per view. Default: `3`
- **gutter**: space in px between items. Default: `16`
- **autoplay**: `true|false`. Default: `true`
- **controls** (or **arrows**): `true|false`. Default: `true`
- **nav**: dot navigation `true|false`. Default: `false`
- **loop**: `true|false`. Default: `true`
- **image_size**: thumbnail size (e.g., `thumbnail`, `medium`, `large`). Default: `medium`
- **show_title**: `true|false`. Default: `true`
- **show_excerpt**: `true|false`. Default: `false`
- **link**: `permalink|none`. Default: `permalink`

#### Examples
```
[cpt_carousel type="portfolio" posts_per_page="12" items="4" gutter="24" autoplay="true" controls="true" nav="false" loop="true" image_size="large" show_title="true" show_excerpt="true"]
```

Filter by taxonomy/terms:
```
[cpt_carousel type="portfolio" taxonomy="project_type" terms="web,branding"]
```

### Notes
- Uses Tiny Slider from CDN for minimal footprint. No jQuery is required.
- Multiple carousels per page are supported; each will initialize independently.
- Uses the featured image of each post; ensure your CPT supports `thumbnail`.
- Customize styles in `assets/css/cpt-carousel.css` or override in your theme.

### Uninstall
Simply deactivate and delete the plugin from the admin.
