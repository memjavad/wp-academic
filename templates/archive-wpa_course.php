<?php
/**
 * Template for Course Archive
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

<main class="wpa-archive-main-wrapper">
    <div class="wpa-container" style="margin-top: 90px; margin-bottom: 60px;">
        
        <header class="wpa-archive-header" style="margin-bottom: 40px; text-align: center;">
        <h1 class="wpa-archive-title"><?php post_type_archive_title(); ?></h1>
        <?php the_archive_description( '<div class="wpa-archive-description">', '</div>' ); ?>
    </header>

    <?php if ( have_posts() ) : ?>
        <div class="wpa-grid wpa-course-wrapper wpa-cols-3">
            <?php while ( have_posts() ) : the_post(); 
                $course_id = get_the_ID();
                $price = get_post_meta( $course_id, '_wpa_course_price', true );
                $duration = get_post_meta( $course_id, '_wpa_course_duration', true );
            ?>
                <article id="post-<?php the_ID(); ?>" class="wpa-card wpa-course-card">
                    <div class="wpa-card-img">
                        <a href="<?php the_permalink(); ?>">
                            <?php has_post_thumbnail() ? the_post_thumbnail('medium_large') : echo_placeholder_icon('welcome-learn-more'); ?>
                        </a>
                    </div>
                    <div class="wpa-card-body">
                        <h3 class="wpa-card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                        <div class="wpa-card-meta">
                            <?php if($duration): ?><span class="wpa-meta-item"><?php echo WPA_Icons::get('clock'); ?> <?php echo esc_html($duration); ?></span><?php endif; ?>
                        </div>
                        <div class="wpa-card-excerpt">
                            <?php echo wp_trim_words( get_the_excerpt(), 15 ); ?>
                        </div>
                    </div>
                    <div class="wpa-card-footer">
                        <span class="wpa-card-price <?php echo empty($price) ? 'free' : ''; ?>">
                            <?php echo empty($price) ? __( 'Free', 'wp-academic-post-enhanced' ) : esc_html($price); ?>
                        </span>
                        <a href="<?php the_permalink(); ?>" class="wpa-btn wpa-btn-sm wpa-btn-outline">
                            <?php echo WPA_Theme_Labels::get('label_view_course'); ?>
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
        <p><?php echo WPA_Theme_Labels::get('label_no_results'); ?></p>
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
