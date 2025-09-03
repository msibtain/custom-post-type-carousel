<?php
/**
 * Plugin Name:       Custom Post Type Carousel
 * Description:       Display a lightweight carousel of any registered custom post type via shortcode. Uses Tiny Slider.
 * Version:           1.0.0
 * Author:            Sib
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Text Domain:       cpt-carousel
 */

if (!defined('ABSPATH')) {
    exit;
}

define('CPTC_VERSION', '1.0.0');
define('CPTC_PLUGIN_FILE', __FILE__);
define('CPTC_PLUGIN_DIR', plugin_dir_path(CPTC_PLUGIN_FILE));
define('CPTC_PLUGIN_URL', plugin_dir_url(CPTC_PLUGIN_FILE));

/**
 * Enqueue frontend scripts and styles when shortcode is present or on all front-end pages if desired.
 */
function cptc_enqueue_assets()
{
    // Tiny Slider from CDN (lightweight ~8kb gzipped)
    wp_register_style('tiny-slider', 'https://cdn.jsdelivr.net/npm/tiny-slider@2.9.4/dist/tiny-slider.css', array(), '2.9.4');
    wp_register_script('tiny-slider', 'https://cdn.jsdelivr.net/npm/tiny-slider@2.9.4/dist/min/tiny-slider.js', array(), '2.9.4', true);

    // Plugin assets
    wp_register_style('cptc-style', CPTC_PLUGIN_URL . 'assets/css/cpt-carousel.css', array('tiny-slider'), time());
    wp_register_script('cptc-script', CPTC_PLUGIN_URL . 'assets/js/cpt-carousel.js', array('tiny-slider'), time(), true);
}
add_action('wp_enqueue_scripts', 'cptc_enqueue_assets');

/**
 * Shortcode handler: [cpt_carousel]
 *
 * Attributes:
 * - type: post type slug (default: post)
 * - posts_per_page: number of items (default: 10)
 * - orderby, order: WP_Query params
 * - taxonomy, terms: optional taxonomy filter (comma-separated terms)
 * - items: items per view (default: 3)
 * - gutter: space between items in px (default: 16)
 * - autoplay, controls, nav, loop: true/false
 * - arrows: alias for controls
 * - image_size: thumbnail size (default: medium)
 * - show_title, show_excerpt: true/false
 * - link: none|permalink (default: permalink)
 */
function cptc_shortcode($atts)
{
    $atts = shortcode_atts(
        array(
            'type' => 'post',
            'posts_per_page' => 10,
            'orderby' => 'date',
            'order' => 'DESC',
            'taxonomy' => '',
            'terms' => '',
            'items' => 3,
            'gutter' => 16,
            'autoplay' => 'true',
            'controls' => 'true',
            'arrows' => '',
            'nav' => 'false',
            'loop' => 'true',
            'image_size' => 'medium',
            'show_title' => 'true',
            'show_excerpt' => 'false',
            'link' => 'permalink',
        ),
        $atts,
        'cpt_carousel'
    );

    // Allow arrows to alias controls if provided
    if ($atts['arrows'] !== '') {
        $atts['controls'] = $atts['arrows'];
    }

    // Build query
    $query_args = array(
        'post_type' => sanitize_key($atts['type']),
        'posts_per_page' => intval($atts['posts_per_page']),
        'orderby' => sanitize_key($atts['orderby']),
        'order' => (strtoupper($atts['order']) === 'ASC') ? 'ASC' : 'DESC',
        'post_status' => 'publish',
    );

    $taxonomy = sanitize_key($atts['taxonomy']);
    $terms_raw = trim(sanitize_text_field($atts['terms']));
    if ($taxonomy && $terms_raw !== '') {
        $terms = array_filter(array_map('trim', explode(',', $terms_raw)));
        if (!empty($terms)) {
            $query_args['tax_query'] = array(
                array(
                    'taxonomy' => $taxonomy,
                    'field' => 'slug',
                    'terms' => $terms,
                ),
            );
        }
    }

    $posts = new WP_Query($query_args);
    if (!$posts->have_posts()) {
        return '';
    }

    // Enqueue now that we know we will render
    wp_enqueue_style('cptc-style');
    wp_enqueue_script('cptc-script');

    $items_per_view = max(1, intval($atts['items']));
    $gutter = max(0, intval($atts['gutter']));
    $autoplay = filter_var($atts['autoplay'], FILTER_VALIDATE_BOOLEAN);
    $controls = filter_var($atts['controls'], FILTER_VALIDATE_BOOLEAN);
    $nav = filter_var($atts['nav'], FILTER_VALIDATE_BOOLEAN);
    $loop = filter_var($atts['loop'], FILTER_VALIDATE_BOOLEAN);

    	$wrapper_id = wp_generate_uuid4();
	$container_id = 'cptc-' . $wrapper_id;
	// Tiny Slider options via data attribute so multiple carousels work independently
	$tns_options = array(
		'items' => $items_per_view,
		'gutter' => $gutter,
		'autoplay' => $autoplay,
		'controls' => $controls,
		'nav' => $nav,
		'loop' => $loop,
		'slideBy' => 'page',
		'speed' => 400,
		'mouseDrag' => true,
		'controlsPosition' => 'bottom',
		'navPosition' => 'bottom',
	);

    $tns_json = wp_json_encode($tns_options);

    ob_start();
    ?>
    <div id="<?php echo esc_attr($wrapper_id); ?>" class="cpt-carousel"
        data-tns-options='<?php echo esc_attr($tns_json); ?>'>
        <div id="<?php echo esc_attr($container_id); ?>" class="cpt-carousel-track">
            <?php
            while ($posts->have_posts()):
                $posts->the_post();
                $post_id = get_the_ID();
                $link_mode = $atts['link'] === 'none' ? 'none' : 'permalink';
                $thumbnail = get_the_post_thumbnail($post_id, $atts['image_size'], array('class' => 'cpt-carousel-image'));
                $title = get_the_title();
                $excerpt = get_the_excerpt();
                ?>
                <div class="cpt-carousel-item">
                    <div class="cpt-carousel-card">
                        <?php if ($thumbnail): ?>
                            <?php if ($link_mode === 'permalink'): ?>
                                <a class="cpt-carousel-thumb" href="<?php the_permalink(); ?>"
                                    aria-label="<?php echo esc_attr($title); ?>">
                                    <?php echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                </a>
                            <?php else: ?>
                                <div class="cpt-carousel-thumb">
                                    <?php echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php if (filter_var($atts['show_title'], FILTER_VALIDATE_BOOLEAN)): ?>
                            <h3 class="cpt-carousel-title">
                                <?php if ($link_mode === 'permalink'): ?>
                                    <a href="<?php the_permalink(); ?>"><?php echo esc_html($title); ?></a>
                                <?php else: ?>
                                    <?php echo esc_html($title); ?>
                                <?php endif; ?>
                            </h3>
                        <?php endif; ?>

                        <?php if (filter_var($atts['show_excerpt'], FILTER_VALIDATE_BOOLEAN) && $excerpt): ?>
                            <div class="cpt-carousel-excerpt"><?php echo esc_html($excerpt); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
    <?php
    wp_reset_postdata();

    return ob_get_clean();
}
add_shortcode('cpt_carousel', 'cptc_shortcode');

/**
 * Activation/Deactivation hooks.
 */
function cptc_activate()
{
    // Nothing required for now.
}

function cptc_deactivate()
{
    // Nothing required for now.
}

register_activation_hook(__FILE__, 'cptc_activate');
register_deactivation_hook(__FILE__, 'cptc_deactivate');


