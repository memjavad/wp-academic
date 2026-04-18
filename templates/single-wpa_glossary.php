<?php
/**
 * The template for displaying single glossary terms.
 *
 * @package WP Academic Post Enhanced
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

wpa_get_header([ 'show_progress' => true ]);

$post_id = get_the_ID();
$options = get_option( 'wpa_homepage_settings' );

// Custom Attributes
$custom_title = get_post_meta( $post_id, 'wpa_glossary_custom_post_title', true );
$audio_url    = get_post_meta( $post_id, 'wpa_glossary_audio_url', true );
$display_title = ! empty( $custom_title ) ? $custom_title : get_the_title();

// Sidebar Setting
$sidebar_pos = isset( $options['glossary_sidebar_pos'] ) ? $options['glossary_sidebar_pos'] : 'right';
$show_sidebar = ( $sidebar_pos !== 'none' );
$show_label   = isset( $options['glossary_show_single_label'] ) ? (bool)$options['glossary_show_single_label'] : true;

// Get Related Terms (by Tag)
$tags = wp_get_post_terms( $post_id, 'wpa_glossary_tag', ['fields' => 'ids'] );
$related_terms = [];
if ( ! empty( $tags ) && ! is_wp_error( $tags ) ) {
    $related_terms = get_posts([
        'post_type' => 'wpa_glossary',
        'post__not_in' => [$post_id],
        'posts_per_page' => 5,
        'tax_query' => [
            [
                'taxonomy' => 'wpa_glossary_tag',
                'field' => 'term_id',
                'terms' => $tags,
            ]
        ]
    ]);
}
?>

<main class="wpa-main-content">
    <!-- Hero Section -->
    <div class="wpa-glossary-hero">
        <div class="wpa-container">
            <?php if ( $show_label ) : ?>
                <span class="wpa-meta-item wpa-glossary-label">
                    <?php echo wpa_glossary_get_title(); ?>
                </span>
            <?php endif; ?>
            <div class="wpa-title-wrapper">
                <h1 class="wpa-post-title"><?php echo esc_html( $display_title ); ?></h1>
                <?php if ( $audio_url ) : ?>
                    <button class="wpa-audio-btn" onclick="document.getElementById('wpa-term-audio').play()" aria-label="<?php esc_attr_e( 'Play Pronunciation', 'wp-academic-post-enhanced' ); ?>">
                        <?php echo WPA_Icons::get('megaphone'); ?>
                    </button>
                    <audio id="wpa-term-audio" src="<?php echo esc_url( $audio_url ); ?>" style="display:none;"></audio>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="wpa-container">
        <!-- Academic Breadcrumbs with Schema.org -->
        <nav class="wpa-breadcrumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'wp-academic-post-enhanced' ); ?>" itemscope itemtype="https://schema.org/BreadcrumbList" style="margin-top: 20px; margin-bottom: 10px;">
            <span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                <a itemprop="item" href="<?php echo esc_url( home_url( '/' ) ); ?>"><span itemprop="name"><?php echo WPA_Theme_Labels::get('label_home'); ?></span></a>
                <meta itemprop="position" content="1" />
            </span>
            <span class="wpa-breadcrumb-sep">›</span>
            <span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                <a itemprop="item" href="<?php echo esc_url( get_post_type_archive_link('wpa_glossary') ); ?>"><span itemprop="name"><?php echo esc_html( wpa_glossary_get_title() ); ?></span></a>
                <meta itemprop="position" content="2" />
            </span>
            <span class="wpa-breadcrumb-sep">›</span>
            <span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                <span itemprop="name" class="wpa-breadcrumb-current"><?php echo esc_html( $display_title ); ?></span>
                <meta itemprop="position" content="3" />
            </span>
        </nav>

        <div class="wpa-content-layout">
            
            <!-- Main Content -->
            <article class="wpa-article-body <?php echo ! $show_sidebar ? 'no-sidebar' : ''; ?>">
                <div class="wpa-post-content">
                    <?php 
                    if ( have_posts() ) {
                        while ( have_posts() ) : the_post();
                            the_content();
                        endwhile; 
                    } else {
                        // Fallback for direct post access if query is broken
                        echo apply_filters( 'the_content', get_post_field( 'post_content', $post_id ) );
                    }
                    ?>
                </div>

                <!-- Related Posts Hook (Widget usually handles this but we can add space) -->
                <?php if ( is_active_sidebar( 'wpa-main-sidebar' ) ) : ?>
                    <div class="wpa-post-footer">
                        <?php // Extra space for widgets or social sharing ?>
                    </div>
                <?php endif; ?>
            </article>

            <!-- Sidebar -->
            <?php if ( $show_sidebar ) : ?>
                <aside class="wpa-sidebar">
                    
                    <?php if ( ! empty( $related_terms ) ) : ?>
                        <div class="wpa-card wpa-sidebar-card wpa-glossary-widget">
                            <h4 class="wpa-widget-title">
                                <?php echo WPA_Icons::get('list-view'); ?> <?php esc_html_e( 'Related Terms', 'wp-academic-post-enhanced' ); ?>
                            </h4>
                            <ul class="wpa-glossary-side-list">
                                <?php foreach( $related_terms as $r_term ) : ?>
                                    <li>
                                        <a href="<?php echo get_permalink( $r_term->ID ); ?>">
                                            <span class="wpa-side-title"><?php echo esc_html( $r_term->post_title ); ?></span>
                                            <span class="wpa-side-arrow">→</span>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php 
                    // Built-in Widget: Latest News
                    if ( get_option( 'wpa_glossary_show_latest_news', 'no' ) === 'yes' ) : 
                        $news_posts = get_posts([ 'post_type' => 'wpa_news', 'posts_per_page' => 3 ]);
                        if ( ! empty( $news_posts ) ) :
                    ?>
                        <div class="wpa-card wpa-sidebar-card wpa-glossary-widget">
                            <h4 class="wpa-widget-title">
                                <?php echo WPA_Icons::get('megaphone'); ?> <?php esc_html_e( 'Latest News', 'wp-academic-post-enhanced' ); ?>
                            </h4>
                            <div class="wpa-side-items-compact">
                                <?php foreach( $news_posts as $n_post ) : ?>
                                    <a href="<?php echo get_permalink( $n_post->ID ); ?>" class="wpa-side-item">
                                        <?php if ( has_post_thumbnail( $n_post->ID ) ) : ?>
                                            <div class="wpa-side-thumb">
                                                <?php echo get_the_post_thumbnail( $n_post->ID, 'thumbnail' ); ?>
                                            </div>
                                        <?php endif; ?>
                                        <div class="wpa-side-details">
                                            <span class="wpa-side-title"><?php echo esc_html( $n_post->post_title ); ?></span>
                                            <span class="wpa-side-meta"><?php echo get_the_date( '', $n_post->ID ); ?></span>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; endif; ?>

                    <?php 
                    // Built-in Widget: Latest Courses
                    if ( get_option( 'wpa_glossary_show_latest_courses', 'no' ) === 'yes' ) : 
                        $course_posts = get_posts([ 'post_type' => 'wpa_course', 'posts_per_page' => 3 ]);
                        if ( ! empty( $course_posts ) ) :
                    ?>
                        <div class="wpa-card wpa-sidebar-card wpa-glossary-widget">
                            <h4 class="wpa-widget-title">
                                <?php echo WPA_Icons::get('welcome-learn-more'); ?> <?php esc_html_e( 'Latest Courses', 'wp-academic-post-enhanced' ); ?>
                            </h4>
                            <div class="wpa-side-items-compact">
                                <?php foreach( $course_posts as $c_post ) : ?>
                                    <a href="<?php echo get_permalink( $c_post->ID ); ?>" class="wpa-side-item">
                                        <?php if ( has_post_thumbnail( $c_post->ID ) ) : ?>
                                            <div class="wpa-side-thumb">
                                                <?php echo get_the_post_thumbnail( $c_post->ID, 'thumbnail' ); ?>
                                            </div>
                                        <?php endif; ?>
                                        <div class="wpa-side-details">
                                            <span class="wpa-side-title"><?php echo esc_html( $c_post->post_title ); ?></span>
                                            <span class="wpa-side-meta"><?php echo esc_html( WPA_Theme_Labels::get('glossary_course_label') ); ?></span>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; endif; ?>

                    <?php if ( is_active_sidebar( 'wpa-glossary-sidebar' ) ) : ?>
                        <?php dynamic_sidebar( 'wpa-glossary-sidebar' ); ?>
                    <?php elseif ( is_active_sidebar( 'wpa-main-sidebar' ) ) : ?>
                        <?php dynamic_sidebar( 'wpa-main-sidebar' ); ?>
                    <?php else : ?>
                        <!-- Default Sidebar Content if no widgets -->
                        <div class="wpa-card wpa-sidebar-card wpa-glossary-widget">
                            <h4 class="wpa-widget-title">
                                <?php echo WPA_Icons::get('text-page'); ?> <?php echo esc_html( WPA_Theme_Labels::get('glossary_term_details') ); ?>
                            </h4>
                            <ul class="wpa-side-details-list">
                                <?php 
                                $term_cats = get_the_term_list( $post_id, 'wpa_glossary_cat', '', ', ' );
                                if ( $term_cats && ! is_wp_error( $term_cats ) ) : ?>
                                <li>
                                    <span class="wpa-label"><?php echo esc_html( WPA_Theme_Labels::get('glossary_category') ); ?></span>
                                    <span class="wpa-value"><?php echo $term_cats; ?></span>
                                </li>
                                <?php endif; ?>
                                <?php 
                                $term_tags = get_the_term_list( $post_id, 'wpa_glossary_tag', '', ', ' );
                                if ( $term_tags && ! is_wp_error( $term_tags ) ) : ?>
                                <li>
                                    <span class="wpa-label"><?php echo esc_html( WPA_Theme_Labels::get('glossary_tags') ); ?></span>
                                    <span class="wpa-value"><?php echo $term_tags; ?></span>
                                </li>
                                <?php endif; ?>
                                <li>
                                    <span class="wpa-label"><?php echo esc_html( WPA_Theme_Labels::get('glossary_last_updated') ); ?></span>
                                    <span class="wpa-value"><?php echo get_the_modified_date(); ?></span>
                                </li>
                            </ul>
                        </div>
                    <?php endif; ?>
                </aside>
            <?php endif; ?>

        </div>
    </div>
</main>

<?php
wpa_get_footer();
