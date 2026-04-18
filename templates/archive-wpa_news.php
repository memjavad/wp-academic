<?php
/**
 * Template for Field News Archive
 *
 * @package WP Academic Post Enhanced
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( function_exists( 'wpa_get_header' ) ) {
    wpa_get_header();
} else {
    get_header();
}
?>

<main class="wpa-news-archive-main-wrapper">
    <div class="wpa-container" style="margin-top: 90px; margin-bottom: 60px;">
        
        <!-- NEW: Academic Breadcrumbs -->
        <nav class="wpa-breadcrumbs" style="font-size: 0.85em; margin-bottom: 20px; opacity: 0.7;">
            <a href="<?php echo home_url(); ?>"><?php _e('Home', 'wp-academic-post-enhanced'); ?></a> &raquo; 
            <span><?php post_type_archive_title(); ?></span>
        </nav>

        <h1 class="wpa-archive-title" style="font-size: 2.5em; font-weight: 800; color: var(--wpa-text-main);"><?php post_type_archive_title(); ?></h1>
        
        <!-- NEW: Topical Hub Introduction -->
        <div class="wpa-archive-hub-intro" style="max-width: 800px; margin: 30px auto; padding: 25px; background: #fff; border: 1px solid var(--wpa-border-color); border-radius: var(--wpa-radius-lg); text-align: right; line-height: 1.8;">
            <div style="display: flex; align-items: start; gap: 15px;">
                <div style="width: 50px; height: 50px; background: var(--wpa-accent-soft); border-radius: 50%; flex-shrink: 0; display: flex; align-items: center; justify-content: center; color: var(--wpa-accent);">📖</div>
                <div>
                    <h3 style="margin-top:0; color: var(--wpa-accent);"><?php _e('Scientific Focus', 'wp-academic-post-enhanced'); ?></h3>
                    <?php if ( is_category() || is_tag() || is_tax() ) : ?>
                        <?php the_archive_description( '<div class="wpa-archive-description">', '</div>' ); ?>
                    <?php else : ?>
                        <p><?php _e('Explore the latest evidence-based research and clinical developments in Arabic psychology. This hub centralizes academic citations, peer-reviewed studies, and expert analysis to provide a comprehensive look at modern mental health and behavioral science.', 'wp-academic-post-enhanced'); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        </div>
        <!-- END NEW: Topical Hub Introduction -->

    <?php if ( have_posts() ) : ?>
        <div class="wpa-grid wpa-news-wrapper wpa-cols-3">
            <?php while ( have_posts() ) : the_post(); ?>
                <article id="post-<?php the_ID(); ?>" class="wpa-card wpa-news-card">
                    <div class="wpa-card-img">
                        <a href="<?php the_permalink(); ?>">
                            <?php has_post_thumbnail() ? the_post_thumbnail('medium_large') : echo_placeholder_icon('format-image'); ?>
                        </a>
                    </div>
                    <div class="wpa-card-body">
                        <div class="wpa-card-meta">
                            <span class="wpa-meta-item"><?php echo get_the_date(); ?></span>
                            <span class="wpa-meta-sep">&bull;</span>
                            <span class="wpa-meta-item"><?php the_author(); ?></span>
                        </div>
                        <h3 class="wpa-card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                        <div class="wpa-card-excerpt">
                            <?php echo wp_trim_words( get_the_excerpt(), 20 ); ?>
                        </div>
                    </div>
                    <div class="wpa-card-footer">
                        <a href="<?php the_permalink(); ?>" class="wpa-btn-link">
                            <?php esc_html_e( 'Read More', 'wp-academic-post-enhanced' ); ?> &rarr;
                        </a>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>

        <div class="wpa-pagination" style="margin-top: 40px; text-align: center;">
            <?php echo paginate_links([
                'prev_text' => '&larr;',
                'next_text' => '&rarr;',
                'type' => 'list',
            ]); ?>
        </div>

    <?php else : ?>
        <p><?php esc_html_e( 'No news found.', 'wp-academic-post-enhanced' ); ?></p>
    <?php endif; ?>

</div>
</main>

<?php 
if ( function_exists( 'wpa_get_footer' ) ) {
    wpa_get_footer();
} else {
    get_footer();
}

// Helper if not defined in theme scope yet
if (!function_exists('echo_placeholder_icon')) {
    function echo_placeholder_icon($icon) {
        echo '<div style="width:100%;height:100%;background:var(--wpa-bg-light);display:flex;align-items:center;justify-content:center;color:#cbd5e1;"><div style="width:40px;height:40px;">' . WPA_Icons::get($icon) . '</div></div>';
    }
}
?>
