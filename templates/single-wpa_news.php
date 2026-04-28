<?php
/**
 * Custom Template for Field News
 *
 * @package WP Academic Post Enhanced
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( function_exists( 'wpa_get_header' ) ) {
    wpa_get_header( [ 'show_progress' => true ] );
} else {
    get_header();
}


$theme_opts = get_option( 'wpa_homepage_settings', [] );
$news_opts = get_option( 'wpa_field_news_settings', [] );

// Mappings (Theme > News Layout)
$show_date = wpa_get_setting('news_show_meta_date', 'display_show_date', 1, $theme_opts, $news_opts);
$show_read = isset($news_opts['display_show_reading_time']) ? $news_opts['display_show_reading_time'] : 1; 
$show_sidebar = wpa_get_setting('news_show_sidebar', 'display_show_sidebar', 1, $theme_opts, $news_opts);
$show_recent = wpa_get_setting('news_sidebar_recent', 'display_show_recent_widget', 1, $theme_opts, $news_opts);
$show_share = wpa_get_setting('news_sidebar_share', 'display_show_share', 1, $theme_opts, $news_opts);
$show_google = wpa_get_setting('news_show_google_news', 'display_show_google_news', 0, $theme_opts, $news_opts);
$google_url = wpa_get_setting('news_google_news_url', 'google_news_url', '', $theme_opts, $news_opts);
$img_style = wpa_get_setting('news_img_style', 'featured_image_style', 'standard', $theme_opts, $news_opts);

// Metadata Visibility from Theme
$show_facts  = wpa_get_setting('news_show_meta_type', 'display_show_study_facts', 1, $theme_opts, $news_opts);
$show_author = wpa_get_setting('news_show_meta_authors', 'display_show_author_spotlight', 1, $theme_opts, $news_opts);
$show_toc    = wpa_get_setting('news_show_meta_concepts', 'display_show_toc', 1, $theme_opts, $news_opts); // Map ToC to AI Concepts toggle or just keep legacy for now? Let's use a new setting if we want.
// For now, I'll use the legacy news setting if theme setting doesn't specifically have a 'TOC' toggle.
$show_toc = isset($news_opts['display_show_toc']) ? $news_opts['display_show_toc'] : 1; 


$meta = get_post_meta( get_the_ID(), '_wpa_news_metadata', true );

// Main Content
?>
<main class="wpa-news-main">
    <div class="wpa-container" style="margin-top: 30px; margin-bottom: 60px;">
        
        <!-- Academic Breadcrumbs with Schema.org -->
        <nav class="wpa-breadcrumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'wp-academic-post-enhanced' ); ?>" itemscope itemtype="https://schema.org/BreadcrumbList">
            <span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                <a itemprop="item" href="<?php echo esc_url( home_url( '/' ) ); ?>"><span itemprop="name"><?php echo WPA_Theme_Labels::get('label_home'); ?></span></a>
                <meta itemprop="position" content="1" />
            </span>
            <span class="wpa-breadcrumb-sep">›</span>
            <span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                <a itemprop="item" href="<?php echo esc_url( get_post_type_archive_link('wpa_news') ); ?>"><span itemprop="name"><?php echo esc_html( get_post_type_object('wpa_news')->labels->name ); ?></span></a>
                <meta itemprop="position" content="2" />
            </span>
            <span class="wpa-breadcrumb-sep">›</span>
            <span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                <span itemprop="name" class="wpa-breadcrumb-current"><?php the_title(); ?></span>
                <meta itemprop="position" content="3" />
            </span>
        </nav>
        <?php while ( have_posts() ) : the_post(); ?>
            <div class="<?php echo $show_sidebar ? 'wpa-layout-grid' : ''; ?>">
                
                <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                    
                    <header class="wpa-article-header">
                        <?php 
                        $cats = get_the_category();
                        if ( ! empty( $cats ) ) {
                            echo '<span class="wpa-category-label">' . esc_html( $cats[0]->name ) . '</span>';
                        }
                        ?>
                        <h1 class="wpa-article-title"><?php the_title(); ?></h1>
                        
                        <div class="wpa-article-meta">
                            <?php if ( $show_date ) : ?>
                                <span class="wpa-date"><?php echo get_the_date(); ?></span>
                            <?php endif; ?>

                            <span class="wpa-author-meta">
                                <?php if ( $show_date ) echo '<span class="wpa-sep">&bull;</span> '; ?>
                                <?php the_author(); ?>
                            </span>
                            
                            <?php if ( $show_read ) : ?>
                                <span class="wpa-sep">&bull;</span>
                                <span class="wpa-read-time"><?php if ( function_exists( 'wpa_reading_time_shortcode' ) ) echo wpa_reading_time_shortcode([]); ?></span>
                            <?php endif; ?>
                        </div>
                    </header>

                    <?php if ( has_post_thumbnail() && $img_style !== 'hidden' ) : ?>
                        <div class="wpa-featured-image wpa-img-<?php echo esc_attr( $img_style ); ?>">
                            <?php the_post_thumbnail( 'large', [ 'fetchpriority' => 'high', 'class' => 'wpa-lcp-image' ] ); ?>
                        </div>
                    <?php endif; ?>

                    <div class="wpa-article-content">
                        <?php if ( $show_google && $google_url ) : ?>
                            <a href="<?php echo esc_url( $google_url ); ?>" target="_blank" class="wpa-btn wpa-btn-google" style="margin-bottom:30px;">
                                <?php echo WPA_Icons::get('earth'); ?>
                                <?php echo WPA_Field_News_Frontend::get_label('follow_google_news'); ?>
                            </a>
                        <?php endif; ?>
                        <?php the_content(); ?>
                        
                        <?php 
                        // Dynamically inject Recommended Training if news generator exists
                        if ( class_exists( 'WPA_News_Generator' ) ) {
                            $tags = wp_get_post_tags( get_the_ID(), ['fields' => 'names'] );
                            $gen = new WPA_News_Generator();
                            // Access the private method via a filter or just use the logic directly here if needed.
                            // For simplicity and immediate effect on production, let's call the helper if we can.
                            // Since it's private, I'll move it to a static public helper or just use the filter I'll add.
                            echo apply_filters( 'wpa_news_after_content_training', '', get_the_ID() );
                        }
                        ?>
                    </div>

                </article>

                <?php if ( $show_sidebar ) : ?>
                    <aside class="wpa-sidebar">
                        
                        <?php if ( $show_toc ) : ?>
                            <div id="wpa-sidebar-toc" class="wpa-sidebar-card wpa-toc-card" style="display:none;">
                                <h3 class="widget-title"><?php echo WPA_Icons::get('list-view'); ?> <?php echo WPA_Field_News_Frontend::get_label('toc_title'); ?></h3>
                                <ul class="wpa-toc-list"></ul>
                            </div>
                        <?php endif; ?>

                        <?php if ( $show_facts && ! empty( $meta ) ) : ?>
                            <div class="wpa-sidebar-card wpa-facts-card">
                                <h3 class="widget-title"><?php echo WPA_Icons::get('text-page'); ?> <?php echo WPA_Field_News_Frontend::get_label('facts_title'); ?></h3>
                                <ul class="wpa-facts-list">
                                    <?php if ( ! empty( $meta['publication'] ) ) : ?>
                                        <li><strong><?php echo WPA_Field_News_Frontend::get_label('journal'); ?></strong> <?php echo esc_html( $meta['publication'] ); ?></li>
                                    <?php endif; ?>
                                    <?php if ( ! empty( $meta['date'] ) ) : ?>
                                        <li><strong><?php echo WPA_Field_News_Frontend::get_label('year'); ?></strong> <?php echo esc_html( substr( $meta['date'], 0, 4 ) ); ?></li>
                                    <?php endif; ?>
                                    <?php if ( ! empty( $meta['citations'] ) ) : ?>
                                        <li><strong><?php echo WPA_Field_News_Frontend::get_label('citations'); ?></strong> <?php echo esc_html( $meta['citations'] ); ?></li>
                                    <?php endif; ?>
                                    <?php if ( ! empty( $meta['type'] ) ) : ?>
                                        <li><strong><?php echo WPA_Field_News_Frontend::get_label('type'); ?></strong> <?php echo esc_html( $meta['type'] ); ?></li>
                                    <?php endif; ?>
                                    <?php if ( ! empty( $meta['doi'] ) ) : ?>
                                        <li><strong>DOI:</strong> <span dir="ltr" style="display:inline-block; unicode-bidi:embed;"><a href="https://doi.org/<?php echo esc_attr($meta['doi']); ?>" target="_blank" style="word-break: break-all;"><?php echo esc_html( $meta['doi'] ); ?></a></span></li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <?php if ( $show_author && ! empty( $meta['authors_list'][0] ) ) : 
                            $auth = $meta['authors_list'][0];
                        ?>
                            <div class="wpa-sidebar-card wpa-author-card">
                                <h3 class="widget-title"><?php echo WPA_Icons::get('admin-users'); ?> <?php echo WPA_Field_News_Frontend::get_label('author_title_sidebar'); ?></h3>
                                <div class="wpa-author-profile">
                                    <div class="wpa-author-avatar">
                                        <?php echo WPA_Icons::get('admin-users'); ?>
                                    </div>
                                    <div class="wpa-author-details">
                                        <strong>
                                            <?php echo esc_html( $auth['name'] ); ?>
                                            <?php if ( ! empty( $auth['orcid'] ) ) : ?>
                                                <a href="<?php echo esc_url( $auth['orcid'] ); ?>" target="_blank" class="wpa-orcid-icon" style="margin-left: 5px; color: #a3e635;"><?php echo WPA_Icons::get('orcid'); ?></a>
                                            <?php endif; ?>
                                        </strong>
                                        <?php if ( ! empty( $auth['affiliation'] ) ) : ?>
                                            <span class="wpa-author-org"><?php echo esc_html( $auth['affiliation'] ); ?></span>
                                        <?php endif; ?>
                                        <?php if ( ! empty( $auth['country'] ) ) : ?>
                                            <span class="wpa-author-country" style="display:block; font-size:0.8rem; opacity:0.7; margin-top:2px;">
                                                <?php echo WPA_Icons::get('earth'); ?> <?php echo esc_html( $auth['country'] ); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ( $show_share ) : 
                            $url = get_permalink();
                            $title = get_the_title();
                        ?>
                            <div class="wpa-sidebar-card wpa-share-card">
                                <h3 class="widget-title"><?php echo WPA_Field_News_Frontend::get_label('share_title'); ?></h3>
                                <div class="wpa-share-buttons">
                                                                <a href="https://twitter.com/intent/tweet?text=<?php echo urlencode($title); ?>&url=<?php echo urlencode($url); ?>" target="_blank" class="wpa-btn-icon wpa-btn-twitter"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg></a>
                                                                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($url); ?>" target="_blank" class="wpa-btn-icon wpa-btn-facebook"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="currentColor"><path d="M14 13.5h2.5l1-4H14v-2c0-1.03 0-2 2-2h1.5V2.14c-.326-.043-1.557-.14-2.857-.14C11.928 2 10 3.657 10 6.7v2.8H7v4h3V22h4v-8.5z"/></svg></a>
                                                                <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo urlencode($url); ?>" target="_blank" class="wpa-btn-icon wpa-btn-linkedin"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="currentColor"><path d="M6.94 5a2 2 0 1 1-4-.002 2 2 0 0 1 4 .002zM7 8.48H3V21h4V8.48zm6.32 0H9.34V21h3.94v-6.57c0-3.66 4.77-4 4.77 0V21H22v-7.93c0-6.17-7.06-5.94-8.72-2.91l.04-1.68z"/></svg></a>
                                                                <a href="https://wa.me/?text=<?php echo urlencode($title . ' ' . $url); ?>" target="_blank" class="wpa-btn-icon wpa-btn-whatsapp"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="currentColor"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.976.554 2.058.947 2.796.947 3.181 0 5.768-2.587 5.768-5.766-.001-3.181-2.587-5.768-5.768-5.766zm.002-4.172c5.488 0 9.949 4.462 9.949 9.948 0 1.745-.458 3.492-1.332 5.038l1.38 5.014-5.265-1.38c-1.487.817-3.153 1.276-4.732 1.276-5.488 0-9.949-4.461-9.949-9.948 0-5.486 4.461-9.948 9.949-9.948z"/></svg></a>                                </div>
                            </div>
                        <?php endif; ?>

                        <?php 
                        if ( $show_recent ) {
                            the_widget( 'WPA_Field_News_Widget', [ 'title' => WPA_Field_News_Frontend::get_label('latest_news'), 'count' => 5 ] );
                        }
                        
                        if ( is_active_sidebar( 'wpa-field-news-sidebar' ) ) {
                            dynamic_sidebar( 'wpa-field-news-sidebar' ); 
                        }
                        ?>
                    </aside>
                <?php endif; ?>

            </div>
            
            <?php 
            if ( comments_open() || get_comments_number() ) {
                comments_template();
            }
            ?>

        <?php endwhile; ?>
    </div>
</main>

<?php 
if ( function_exists( 'wpa_get_footer' ) ) {
    wpa_get_footer();
} else {
    get_footer();
}
?>

<?php if ( $show_sidebar && $show_toc ) : ?>
<script>
document.addEventListener("DOMContentLoaded", function() {
    var toc = document.querySelector('.wpa-toc-list');
    var container = document.getElementById('wpa-sidebar-toc');
    if (!toc || !container) return;
    
    var headers = document.querySelectorAll('.wpa-article-content h2');
    if (headers.length < 2) return; 
    
    container.style.display = 'block';
    
    headers.forEach(function(header, index) {
        var id = header.id;
        if (!id) {
            id = 'wpa-header-' + index;
            header.id = id;
        }
        
        // Find reading time badge if engine already injected it
        var timeBadge = header.nextElementSibling && header.nextElementSibling.classList && header.nextElementSibling.classList.contains('wpa-toc-time') ? header.nextElementSibling.outerHTML : '';
        
        // Alternative: The engine might have placed it inside the header if it was parsed that way, 
        // but our engine replaces the whole tag. Let's check for the .wpa-toc-time class within the content logic.
        
        var li = document.createElement('li');
        li.className = 'wpa-toc-item';
        li.dataset.anchor = id;
        
        var a = document.createElement('a');
        a.href = '#' + id;
        
        var titleSpan = document.createElement('span');
        titleSpan.className = 'wpa-toc-item-title';
        titleSpan.textContent = header.textContent;
        
        a.appendChild(titleSpan);
        
        // If we found a time in the data-metrics or similar, we could add it. 
        // For now, let's keep it elegant and consistent with the sidebar style.
        
        li.appendChild(a);
        toc.appendChild(li);
    });
});
</script>
<?php endif; ?>
