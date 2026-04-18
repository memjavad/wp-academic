<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * WP_Academic_Post_Enhanced_Sitemap_Manager
 * 
 * Generates an Ultimate standalone XML sitemap system for all Post Types.
 * Structure: /sitemap-main.xml (Index) -> /sitemap-pt-{type}.xml (URLs)
 */
class WP_Academic_Post_Enhanced_Sitemap_Manager {

	private static $instance;

	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct() {
		// Register rewrite rules for the sitemap
		add_action( 'init', [ $this, 'add_rewrite_rules' ] );
		add_filter( 'query_vars', [ $this, 'add_query_vars' ] );
		add_action( 'template_redirect', [ $this, 'render_sitemap_endpoint' ] );
		add_filter( 'redirect_canonical', [ $this, 'disable_canonical_redirect' ], 10, 2 );

		// Universal discovery via robots.txt
		add_filter( 'robots_txt', [ $this, 'add_to_robots_txt' ], 10, 2 );

		// AJAX for manual ping
		add_action( 'wp_ajax_wpa_ping_academic_sitemap', [ $this, 'ajax_ping_sitemap' ] );

		// Auto-purge and ping on updates
		add_action( 'save_post', [ $this, 'auto_handle_updates' ], 10, 3 );
	}

	/**
	 * AJAX Handler: Ping Search Engines
	 */
	public function ajax_ping_sitemap() {
		check_ajax_referer( 'wpa_sitemap_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Unauthorized.', 'wp-academic-post-enhanced' ) );
		}

		$result = $this->ping_search_engines();

		if ( $result ) {
			update_option( 'wpa_last_sitemap_ping', time() );
			wp_send_json_success( __( 'Success! Google and Bing have been notified using multi-index protocol.', 'wp-academic-post-enhanced' ) );
		} else {
			wp_send_json_error( __( 'Failed to notify search engines. Please try again later.', 'wp-academic-post-enhanced' ) );
		}
	}

	/**
	 * Ping Google and Bing
	 */
	public function ping_search_engines() {
		$main_sitemap = home_url( '/sitemap-main.xml' );
		$news_sitemap = home_url( '/news-sitemap.xml' );
		
		$urls = [ $main_sitemap, $news_sitemap ];
		
		foreach ( $urls as $sitemap ) {
			$url_encoded = urlencode( $sitemap );
			wp_remote_get( "https://www.google.com/ping?sitemap={$url_encoded}", [ 'timeout' => 5 ] );
			wp_remote_get( "https://www.bing.com/ping?sitemap={$url_encoded}", [ 'timeout' => 5 ] );
		}

		return true;
	}

	/**
	 * Automatically purge cache and ping search engines on post publication.
	 */
	public function auto_handle_updates( $post_id, $post, $update ) {
		if ( wp_is_post_revision( $post_id ) || 'publish' !== $post->post_status ) {
			return;
		}

		$selected_types = get_option( 'wpa_sitemap_post_types', ['post', 'page', 'wpa_news', 'wpa_course', 'wpa_glossary'] );
		if ( ! in_array( $post->post_type, $selected_types ) ) {
			return;
		}

		// Purge Global Cache
		$this->purge_all_caches();

		// Ping Search Engines (Throttle)
		$last_ping = get_transient( 'wpa_sitemap_ping_lock' );
		if ( ! $last_ping ) {
			$this->ping_search_engines();
			update_option( 'wpa_last_sitemap_ping', time() );
			set_transient( 'wpa_sitemap_ping_lock', true, 5 * MINUTE_IN_SECONDS );
		}
	}

	/**
	 * Purge all sitemap transients
	 */
	private function purge_all_caches() {
		global $wpdb;
		$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_wpa_sitemap_%'" );
		$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_timeout_wpa_sitemap_%'" );
	}

	/**
	 * Add Sitemap link to robots.txt
	 */
	public function add_to_robots_txt( $output, $public ) {
		if ( '0' != $public ) {
			$output .= "\nSitemap: " . home_url( '/sitemap-main.xml' ) . "\n";
			$output .= "Sitemap: " . home_url( '/news-sitemap.xml' ) . "\n";
		}
		return $output;
	}

	/**
	 * Add custom rewrite rules
	 */
	public function add_rewrite_rules() {
		// Parent Index
		add_rewrite_rule( '^sitemap-main\.xml/?$', 'index.php?wpa_sitemap=main', 'top' );
		
		// Post Type Sitemaps (Paginated)
		add_rewrite_rule( '^sitemap-pt-([a-z0-9_-]+)(?:-([0-9]+))?\.xml/?$', 'index.php?wpa_sitemap=pt&wpa_pt=$matches[1]&wpa_paged=$matches[2]', 'top' );
		
		// News Sitemap - use a more specific rule to avoid homepage conflict
		add_rewrite_rule( '^news-sitemap\.xml/?$', 'index.php?wpa_sitemap=news', 'top' );

		// Legacy Redirects
		add_rewrite_rule( '^academic-sitemap\.xml/?$', 'index.php?wpa_sitemap=main', 'top' );
		add_rewrite_rule( '^sitemap_index\.xml/?$', 'index.php?wpa_sitemap=main', 'top' );
	}

	/**
	 * Add custom query vars
	 */
	public function add_query_vars( $vars ) {
		$vars[] = 'wpa_sitemap';
		$vars[] = 'wpa_pt';
		$vars[] = 'wpa_paged';
		return $vars;
	}

	/**
	 * Disable canonical redirects for sitemap query vars.
	 */
	public function disable_canonical_redirect( $redirect_url, $requested_url ) {
		if ( get_query_var( 'wpa_sitemap' ) ) {
			return false;
		}
		return $redirect_url;
	}

	/**
	 * Handle Template Redirect
	 */
	public function render_sitemap_endpoint() {
		$type  = get_query_var( 'wpa_sitemap' );
		$pt    = get_query_var( 'wpa_pt' );
		$paged = get_query_var( 'wpa_paged' );
		$paged = max( 1, (int) $paged ); // Force minimum 1

		if ( ! $type ) return;

		$xml = '';
		$transient_key = 'wpa_sitemap_' . $type . '_' . $pt . '_' . $paged;
		$xml = get_transient( $transient_key );

		if ( empty( $xml ) ) {
			if ( $type === 'main' ) {
				$xml = $this->generate_index_xml();
			} elseif ( $type === 'news' ) {
				$xml = $this->generate_news_xml();
			} elseif ( $type === 'pt' && ! empty( $pt ) ) {
				$xml = $this->generate_pt_xml( $pt, $paged );
			}
			
			if ( ! empty( $xml ) ) {
				set_transient( $transient_key, $xml, 12 * HOUR_IN_SECONDS );
			}
		}

		if ( ! empty( $xml ) ) {
			header( 'Content-Type: text/xml; charset=utf-8' );
			header( 'X-Robots-Tag: noindex, follow' );
			echo $xml;
			exit;
		}
	}

	/**
	 * Generate Sitemap Index (Parent)
	 */
	private function generate_index_xml() {
		$selected_types = get_option( 'wpa_sitemap_post_types', ['post', 'page', 'wpa_news', 'wpa_course', 'wpa_glossary'] );
		$xsl_url = plugin_dir_url( WP_ACADEMIC_POST_ENHANCED_FILE ) . 'assets/css/academic-sitemap.xsl';

		ob_start();
		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<?xml-stylesheet type="text/xsl" href="' . esc_url( $xsl_url ) . '"?>';
		echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

		// Always include News as its own logic
		echo '<sitemap>';
		echo '<loc>' . esc_url( home_url( '/news-sitemap.xml' ) ) . '</loc>';
		echo '<lastmod>' . date( 'c' ) . '</lastmod>';
		echo '</sitemap>';

		foreach ( $selected_types as $pt ) {
			// Check if post type actually has any published posts
			$count = wp_count_posts( $pt );
			$publish_count = isset( $count->publish ) ? (int) $count->publish : 0;
			
			if ( $publish_count > 0 ) {
				echo '<sitemap>';
				echo '<loc>' . esc_url( home_url( "/sitemap-pt-{$pt}.xml" ) ) . '</loc>';
				echo '<lastmod>' . date( 'c' ) . '</lastmod>';
				echo '</sitemap>';
			}
		}

		echo '</sitemapindex>';
		return ob_get_clean();
	}

	/**
	 * Generate URL Set for a specific Post Type (Paginated)
	 */
	private function generate_pt_xml( $pt, $paged ) {
		$limit = (int) get_option( 'wpa_sitemap_limit', 1000 );
		if ( $limit <= 0 ) $limit = 1000;
		$offset = ( (int)$paged - 1 ) * $limit;
		if ( $offset < 0 ) $offset = 0; // Final safety

		$query = new WP_Query([
			'post_type'      => $pt,
			'posts_per_page' => $limit,
			'offset'         => $offset,
			'post_status'    => 'publish',
			'orderby'        => 'modified',
			'order'          => 'DESC',
			'no_found_rows'  => true, // Performance
		]);

		$xsl_url = plugin_dir_url( WP_ACADEMIC_POST_ENHANCED_FILE ) . 'assets/css/academic-sitemap.xsl';

		ob_start();
		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<?xml-stylesheet type="text/xsl" href="' . esc_url( $xsl_url ) . '"?>';
		echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" 
                xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" 
                xmlns:xhtml="http://www.w3.org/1999/xhtml">';

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				
				// Standard tags
				echo '<url>';
				echo '<loc>' . esc_url( get_permalink() ) . '</loc>';
				echo '<lastmod>' . get_the_modified_date( 'c' ) . '</lastmod>';
				echo '<changefreq>weekly</changefreq>';
				echo '<priority>0.8</priority>';

				// Images
				$thumb_id = get_post_thumbnail_id();
				if ( $thumb_id ) {
					echo '<image:image><image:loc>' . esc_url( wp_get_attachment_image_url( $thumb_id, 'full' ) ) . '</image:loc></image:image>';
				}

				// Hreflang logic (Simplified site root variants for now, matching previous version)
				$variants = [
					'ar' => 'https://arabpsychology.com',
					'es' => 'https://spanish.arabpsychology.com',
					'tr' => 'https://tr-scales.arabpsychology.com',
					'en' => 'https://scales.arabpsychology.com',
				];
				foreach ( $variants as $lang => $url ) {
					echo '<xhtml:link rel="alternate" hreflang="' . esc_attr( $lang ) . '" href="' . esc_url( $url ) . '" />';
				}

				echo '</url>';
			}
			wp_reset_postdata();
		}

		echo '</urlset>';
		return ob_get_clean();
	}

	/**
	 * Generate News XML (Specialized)
	 */
	private function generate_news_xml() {
		$query = new WP_Query([
			'post_type'      => 'wpa_news',
			'posts_per_page' => 100, // Reduced from 1000 for standard news feed
			'post_status'    => 'publish',
			// Google News policy: last 48 hours is ideal, but show latest if none in 48h
			'date_query'     => [ [ 'after' => '48 hours ago' ] ],
			'orderby'        => 'date',
			'order'          => 'DESC',
		]);

		// Fallback: If no news in 48h, show latest 10
		if ( ! $query->have_posts() ) {
			$query = new WP_Query([
				'post_type'      => 'wpa_news',
				'posts_per_page' => 10,
				'post_status'    => 'publish',
				'orderby'        => 'date',
				'order'          => 'DESC',
			]);
		}

		$xsl_url = plugin_dir_url( WP_ACADEMIC_POST_ENHANCED_FILE ) . 'assets/css/academic-sitemap.xsl';

		ob_start();
		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<?xml-stylesheet type="text/xsl" href="' . esc_url( $xsl_url ) . '"?>';
		echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" 
                xmlns:news="http://www.google.com/schemas/sitemap-news/0.9"
                xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">';

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				echo '<url>';
				echo '<loc>' . esc_url( get_permalink() ) . '</loc>';
				echo '<news:news>';
				echo '<news:publication>';
				echo '<news:name>' . esc_html( get_bloginfo( 'name' ) ) . '</news:name>';
				echo '<news:language>ar</news:language>';
				echo '</news:publication>';
				echo '<news:publication_date>' . get_the_date( 'c' ) . '</news:publication_date>';
				echo '<news:title>' . esc_html( get_the_title() ) . '</news:title>';
				echo '</news:news>';
				
				$thumb_id = get_post_thumbnail_id();
				if ( $thumb_id ) {
					echo '<image:image><image:loc>' . esc_url( wp_get_attachment_image_url( $thumb_id, 'full' ) ) . '</image:loc></image:image>';
				}
				echo '</url>';
			}
			wp_reset_postdata();
		}

		echo '</urlset>';
		return trim( ob_get_clean() );
	}
}
