<?php
/**
 * Global WPA Wrapper Template
 * Used for standard Pages and Posts when "Apply Globally" is enabled.
 *
 * @package WP Academic Post Enhanced
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$options = get_option( 'wpa_homepage_settings', [] );
$post_type = get_post_type();

// Determine Sidebar Position
$sidebar_pos = 'none';
if ( is_page() ) {
    $sidebar_pos = isset( $options['page_sidebar_pos'] ) ? $options['page_sidebar_pos'] : 'none';
} elseif ( is_single() ) {
    $sidebar_pos = isset( $options['post_sidebar_pos'] ) ? $options['post_sidebar_pos'] : 'none';
}

// Check for meta override
$meta_sidebar = get_post_meta( get_the_ID(), '_wpa_sidebar_position', true );
if ( $meta_sidebar && $meta_sidebar !== 'default' ) {
    $sidebar_pos = $meta_sidebar;
}

// Visibility Controls
$show_title = true;
$show_featured = true;
$show_meta = false;

if ( is_page() ) {
    $show_title = isset( $options['page_show_title'] ) ? ! empty( $options['page_show_title'] ) : true;
    $show_featured = isset( $options['page_show_featured'] ) ? ! empty( $options['page_show_featured'] ) : true;
} else {
    $show_title = isset( $options['post_show_title'] ) ? ! empty( $options['post_show_title'] ) : true;
    $show_meta = isset( $options['post_show_meta'] ) ? ! empty( $options['post_show_meta'] ) : true;
}

// Per-Page Overrides
if ( get_post_meta( get_the_ID(), '_wpa_hide_title', true ) === '1' ) {
    $show_title = false;
}
if ( get_post_meta( get_the_ID(), '_wpa_hide_featured', true ) === '1' ) {
    $show_featured = false;
}

if ( function_exists( 'wpa_get_header' ) ) {
    wpa_get_header();
} else {
    get_header();
}
?>

<div class="wpa-container wpa-global-content" style="margin-top: 90px; margin-bottom: 60px;">
    
    <div class="wpa-layout-row <?php echo ($sidebar_pos !== 'none') ? 'wpa-has-sidebar wpa-sidebar-' . esc_attr($sidebar_pos) : ''; ?>" style="display: flex; flex-wrap: wrap; gap: 40px;">
        
        <main class="wpa-main-content" style="flex: 1; min-width: 0;">
            <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
                
                <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                    
                    <?php if ( $show_title && ! is_front_page() ) : ?>
                        <header class="wpa-entry-header" style="margin-bottom: 30px;">
                            <h1 class="wpa-entry-title" style="font-size: 2.5rem; font-weight: 800; color: var(--wpa-text-main); margin: 0;"><?php the_title(); ?></h1>
                            
                            <?php if ( is_single() && $show_meta ) : ?>
                                <div class="wpa-entry-meta" style="margin-top: 10px; color: var(--wpa-text-muted); font-size: 0.9rem;">
                                    <span><?php echo get_the_date(); ?></span>
                                    <span style="margin: 0 5px;">&bull;</span>
                                    <span><?php the_author(); ?></span>
                                </div>
                            <?php endif; ?>
                        </header>
                    <?php endif; ?>

                    <div class="wpa-entry-content" style="font-size: 1.1rem; line-height: 1.8; color: var(--wpa-text-main);">
                        <?php 
                        if ( has_post_thumbnail() && $show_featured && ( is_single() || is_page() ) ) {
                            echo '<div class="wpa-featured-image" style="margin-bottom:30px; border-radius:12px; overflow:hidden;">';
                            the_post_thumbnail( 'large', ['style' => 'width:100%; height:auto; display:block;'] );
                            echo '</div>';
                        }
                        
                        the_content(); 
                        
                        wp_link_pages();
                        ?>
                    </div>

                    <?php if ( comments_open() || get_comments_number() ) : ?>
                        <div class="wpa-comments-area" style="margin-top: 60px; padding-top: 40px; border-top: 1px solid var(--wpa-border-color);">
                            <?php comments_template(); ?>
                        </div>
                    <?php endif; ?>

                </article>

            <?php endwhile; endif; ?>
        </main>

        <?php if ( $sidebar_pos !== 'none' ) : ?>
            <aside class="wpa-sidebar" style="width: 300px; flex-shrink: 0;">
                <?php if ( is_active_sidebar( 'wpa-main-sidebar' ) ) : ?>
                    <?php dynamic_sidebar( 'wpa-main-sidebar' ); ?>
                <?php else : ?>
                    <div class="wpa-card" style="padding: 20px;">
                        <h4><?php esc_html_e( 'Sidebar Widget Area', 'wp-academic-post-enhanced' ); ?></h4>
                        <p><?php esc_html_e( 'Add widgets to the "Academic Main Sidebar" area.', 'wp-academic-post-enhanced' ); ?></p>
                    </div>
                <?php endif; ?>
            </aside>
        <?php endif; ?>

    </div>

</div>

<style>
@media (max-width: 991px) {
    .wpa-layout-row { flex-direction: column !important; }
    .wpa-sidebar { width: 100% !important; }
    .wpa-sidebar-left { order: 2; }
}
</style>

<?php 
if ( function_exists( 'wpa_get_footer' ) ) {
    wpa_get_footer();
} else {
    get_footer();
}
?>
