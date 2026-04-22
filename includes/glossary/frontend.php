<?php
/**
 * Frontend Features for Glossary Module
 *
 * @package WP Academic Post Enhanced
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * WPA_Glossary_Frontend Class
 */
class WPA_Glossary_Frontend {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ) );
		add_shortcode( 'wpa_glossary_list', array( $this, 'glossary_list_shortcode' ) );
		add_shortcode( 'wpa_glossary_term', array( $this, 'glossary_single_term_shortcode' ) );
		add_shortcode( 'wpa_glossary_random', array( $this, 'glossary_random_term_shortcode' ) );
		add_action( 'widgets_init', array( $this, 'register_widgets' ) );

		// AJAX for Tooltips
		add_action( 'wp_ajax_wpa_get_glossary_tooltip', array( $this, 'ajax_get_tooltip_content' ) );
		add_action( 'wp_ajax_nopriv_wpa_get_glossary_tooltip', array( $this, 'ajax_get_tooltip_content' ) );

		// AJAX for Letter Groups
		add_action( 'wp_ajax_wpa_get_glossary_group', array( $this, 'ajax_get_letter_group' ) );
		add_action( 'wp_ajax_nopriv_wpa_get_glossary_group', array( $this, 'ajax_get_letter_group' ) );
	}

	/**
	 * AJAX Handler: Get terms for a specific letter
	 */
	public function ajax_get_letter_group() {
		$letter = isset( $_GET['letter'] ) ? sanitize_text_field( $_GET['letter'] ) : 'all';
		$style  = isset( $_GET['style'] ) ? sanitize_text_field( $_GET['style'] ) : 'modern';
		
		$args = array(
			'post_type'      => 'wpa_glossary',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
		);

		if ( $letter !== 'all' ) {
			// Filter by first letter using meta query or title logic
			// Using a more efficient approach: query by title prefix
			global $wpdb;
			$post_ids = $wpdb->get_col( $wpdb->prepare(
				"SELECT ID FROM $wpdb->posts WHERE post_type = 'wpa_glossary' AND post_status = 'publish' AND post_title LIKE %s",
				$wpdb->esc_like( $letter ) . '%'
			) );
			
			if ( empty($post_ids) ) {
				wp_send_json_success('<div class="wpa-glossary-no-results">' . esc_html( WPA_Theme_Labels::get('glossary_no_terms_letter') ) . '</div>');
			}
			$args['post__in'] = $post_ids;
		}

		$posts = get_posts( $args );
		
		ob_start();
		if ( ! empty( $posts ) ) {
			echo '<section class="wpa-glossary-group group-dynamic">';
			echo '<h3>' . esc_html( mb_strtoupper($letter) ) . '</h3>';
			echo '<ul>';
			foreach ( $posts as $item_post ) {
				$link = get_permalink( $item_post->ID );
				if ( $style === 'images' ) {
					$excerpt = ! empty( $item_post->post_excerpt ) ? $item_post->post_excerpt : wp_trim_words( $item_post->post_content, 12 );
					$img_url = get_the_post_thumbnail_url( $item_post->ID, 'medium' );
					$has_img = ! empty( $img_url );
					echo '<li class="wpa-glossary-card-item ' . ( $has_img ? 'has-image' : 'no-image' ) . '">';
					echo '<a href="' . esc_url( $link ) . '" class="wpa-glossary-visual-card">';
					if ( $has_img ) echo '<div class="wpa-card-image" style="background-image: url(' . esc_url( $img_url ) . ');"></div>';
					echo '<div class="wpa-card-content"><h4><dfn>' . esc_html( $item_post->post_title ) . '</dfn></h4><p>' . esc_html( $excerpt ) . '</p></div>';
					echo '</a></li>';
				} else {
					echo '<li><a href="' . esc_url( $link ) . '" data-term-id="' . $item_post->ID . '"><dfn>' . esc_html( $item_post->post_title ) . '</dfn></a></li>';
				}
			}
			echo '</ul></section>';
		} else {
			echo '<div class="wpa-glossary-no-results">' . esc_html( WPA_Theme_Labels::get('glossary_no_terms') ) . '</div>';
		}
		
		$html = ob_get_clean();
		wp_send_json_success( $html );
	}

	/**
	 * AJAX Handler: Get Tooltip Content
	 */
	public function ajax_get_tooltip_content() {
		$term_id = isset( $_GET['term_id'] ) ? absint( $_GET['term_id'] ) : 0;
		if ( ! $term_id ) wp_send_json_error();

		$post_item = get_post( $term_id );
		if ( ! $post_item || $post_item->post_type !== 'wpa_glossary' ) wp_send_json_error();

		// Set global post context
		global $post;
		$post = $post_item;
		setup_postdata( $post );

		$content = wpa_glossary_get_tooltip_content( false, true, 30 );
		wp_reset_postdata();

		wp_send_json_success( $content );
	}

	/**
	 * Register Scripts & Styles
	 */
	public function register_scripts() {
		$plugin_url = plugin_dir_url( dirname( dirname( __FILE__ ) ) ); // Root plugin URL

		// Tooltipster
		wp_register_style( 'wpa-tooltipster-css', $plugin_url . 'assets/css/glossary/wpa-tooltipster.css' );
		wp_register_script( 'wpa-tooltipster-js', $plugin_url . 'assets/js/glossary/wpa-tooltipster.js', array( 'jquery' ), '3.3.0', true );

		// MixitUp
		wp_register_script( 'wpa-mixitup-js', $plugin_url . 'assets/js/glossary/wpa-mixitup.js', array( 'jquery' ), '3.3.1', true );

		// Main Glossary Assets
		// wp_register_style( 'wpa-glossary-css', $plugin_url . 'assets/css/glossary/glossary-frontend.css' ); // Unified
		wp_register_script( 'wpa-glossary-js', $plugin_url . 'assets/js/glossary/glossary-frontend.js', array( 'jquery', 'wpa-mixitup-js', 'wpa-tooltipster-js' ), '1.0', true );

		// Localize Script
		$params = array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'animation' => wpa_glossary_is_animation(),
			'is_tooltip' => wpa_glossary_is_tooltip(),
			'tooltip_theme' => wpa_glossary_get_tooltip_theme(),
			'tooltip_animation' => wpa_glossary_get_tooltip_animation(),
			'tooltip_position' => wpa_glossary_get_tooltip_position(),
			'tooltip_is_arrow' => wpa_glossary_is_tooltip_arrow(),
			'tooltip_min_width' => wpa_glossary_get_tooltip_min_width(),
			'tooltip_max_width' => wpa_glossary_get_tooltip_max_width(),
			'tooltip_speed' => wpa_glossary_get_tooltip_speed(),
			'tooltip_delay' => wpa_glossary_get_tooltip_delay(),
			'tooltip_is_touch_devices' => wpa_glossary_is_tooltip_touch_devices(),
		);
		wp_localize_script( 'wpa-glossary-js', 'wpa_glossary', $params );

		// Custom Styling from Theme Builder
		$options = get_option( 'wpa_homepage_settings', [] );
		$accent = isset( $options['accent_color'] ) ? $options['accent_color'] : get_option( 'wpa_glossary_accent_color', '#2563eb' );
		$head_bg = isset( $options['glossary_heading_bg'] ) ? $options['glossary_heading_bg'] : get_option( 'wpa_glossary_heading_bg', '#f4f4f4' );
		$per_row = isset( $options['glossary_terms_per_row'] ) ? $options['glossary_terms_per_row'] : get_option( 'wpa_glossary_terms_per_row', '3' );

		$custom_css = "
			.wpa-glossary-filter button.active { background: var(--wpa-glossary-accent) !important; color: #fff !important; border-color: var(--wpa-glossary-accent) !important; }
			.wpa-glossary-group h3 { background: var(--wpa-glossary-head-bg); padding: 10px; border-radius: 5px; color: var(--wpa-glossary-accent); }
			.wpa-glossary-container a { color: var(--wpa-glossary-accent); font-weight: 500; }
			.wpa-glossary-term-card { border: 1px solid #eee; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
			.wpa-glossary-term-card h4 { margin-top: 0; color: var(--wpa-glossary-accent); }
		";
		wp_add_inline_style( 'wpa-glossary-css', $custom_css );
	}

	/**
	 * Register Widget & Sidebars
	 */
	public function register_widgets() {
		require_once plugin_dir_path( __FILE__ ) . 'class-widget-related-posts.php';
		require_once plugin_dir_path( __FILE__ ) . 'class-widget-glossary-search.php';
		register_widget( 'WPA_Glossary_Widget_Related_Posts' );
		register_widget( 'WPA_Glossary_Widget_Search' );

		register_sidebar( array(
			'name'          => __( 'Glossary Sidebar', 'wp-academic-post-enhanced' ),
			'id'            => 'wpa-glossary-sidebar',
			'description'   => __( 'Sidebar for the custom Glossary single post template.', 'wp-academic-post-enhanced' ),
			'before_widget' => '<div id="%1$s" class="widget %2$s wpa-widget">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		) );
	}

	/**
	 * Single Term Shortcode [wpa_glossary_term id="123"]
	 */
	public function glossary_single_term_shortcode( $atts ) {
		$atts = shortcode_atts( array(
			'id' => 0,
		), $atts, 'wpa_glossary_term' );

		if ( ! $atts['id'] ) return '';

		$post_item = get_post( $atts['id'] );
		if ( ! $post_item || $post_item->post_type !== 'wpa_glossary' ) return '';

		return $this->render_term_card( $post_item );
	}

	/**
	 * Random Term Shortcode [wpa_glossary_random]
	 */
	public function glossary_random_term_shortcode( $atts ) {
		$posts = get_posts( array(
			'post_type' => 'wpa_glossary',
			'posts_per_page' => 1,
			'orderby' => 'rand',
		) );

		if ( empty( $posts ) ) return '';

		return $this->render_term_card( $posts[0] );
	}

	/**
	 * Helper: Render Term Card
	 */
	private function render_term_card( $post_item ) {
		ob_start();
		echo '<div class="wpa-glossary-term-card wpa-card">';
		echo '<h4>' . esc_html( $post_item->post_title ) . '</h4>';
		echo '<div class="wpa-glossary-term-excerpt">' . wp_trim_words( $post_item->post_content, 30 ) . '</div>';
		echo '<a href="' . get_permalink( $post_item->ID ) . '" class="wpa-btn wpa-btn-sm wpa-btn-outline" style="margin-top:10px; display:inline-flex;">' . esc_html__( 'Read More', 'wp-academic-post-enhanced' ) . ' ' . WPA_Icons::get('arrow-right-alt2') . '</a>';
		echo '</div>';
		return ob_get_clean();
	}

	/**
	 * Glossary List Shortcode [wpa_glossary_list]
	 */
	public function glossary_list_shortcode( $atts ) {
		// Enqueue Assets
		wp_enqueue_script( 'wpa-glossary-js' );

		$atts = shortcode_atts( array(
			'post_type' => 'wpa_glossary',
			'layout' => 'three_column',
			'template' => 'alphabet',
			'hide_empty' => 'no',
			'hide_all' => 'no',
			'hide_numeric' => 'no',
			'taxonomy' => 'wpa_glossary_cat',
			'search' => '', 
			'style' => '',  
			'columns' => '',
			'show_title' => '',
			'title' => '', 
		), $atts, 'wpa_glossary_list' );

		$options = get_option( 'wpa_homepage_settings', [] );
		$is_search = ( $atts['search'] !== '' ) ? ( $atts['search'] === 'yes' ) : wpa_glossary_is_search();
		$style = ( $atts['style'] !== '' ) ? $atts['style'] : ( isset( $options['glossary_style'] ) ? $options['glossary_style'] : 'modern' );
		$columns = ( $atts['columns'] !== '' ) ? intval( $atts['columns'] ) : ( isset( $options['glossary_terms_per_row'] ) ? intval( $options['glossary_terms_per_row'] ) : 3 );
		
		$show_title = ( $atts['show_title'] !== '' ) ? ( $atts['show_title'] === 'yes' ) : ( isset( $options['glossary_show_title'] ) ? (bool)$options['glossary_show_title'] : true );
		$title_text = ( $atts['title'] !== '' ) ? $atts['title'] : wpa_glossary_get_title();

		$cache_key = 'wpa_glossary_list_full_v2_' . md5( serialize( $atts ) . $style . $columns );
		$output = get_transient( $cache_key );

		if ( false === $output || ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ) {
			ob_start();
			
			// 1. Fetch ALL posts - Optimized: Only fetch what we need
			$posts = get_posts( array(
				'post_type'      => $atts['post_type'],
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
			) );

			$groups = array();
			$alphabet = wpa_glossary_get_alphabet();
			$alphabet_upper = array_map( 'mb_strtoupper', $alphabet );

			// 2. Grouping
			foreach ( $posts as $post_item ) {
				$char = mb_strtoupper( mb_substr( $post_item->post_title, 0, 1 ) );
				if ( in_array( $char, array( 'آ', 'إ', 'أ' ) ) ) $char = 'أ';
				
				if ( in_array( $char, $alphabet_upper ) ) {
					$groups[ $char ][] = $post_item;
				} elseif ( is_numeric( $char ) ) {
					$groups[ '0-9' ][] = $post_item;
				} else {
					$groups[ '#' ][] = $post_item;
				}
			}

			// 3. Sort Groups
			$sorted_groups = array();
			foreach ( $alphabet_upper as $letter ) {
				if ( isset( $groups[$letter] ) ) $sorted_groups[$letter] = $groups[$letter];
			}
			if ( isset( $groups['0-9'] ) ) $sorted_groups['0-9'] = $groups['0-9'];
			if ( isset( $groups['#'] ) ) $sorted_groups['#'] = $groups['#'];
			$groups = $sorted_groups;

			// 4. Render HTML
			echo '<div class="wpa-glossary-wrapper" data-style="' . esc_attr($style) . '">';

			if ( $show_title && ! empty( $title_text ) ) {
				echo '<h2 class="wpa-glossary-index-title" style="margin-bottom: 30px; text-align: center;">' . esc_html( $title_text ) . '</h2>';
			}
			
			// SEO Schema
			$schema_items = [];
			foreach ( $posts as $p ) {
				$schema_items[] = [ '@type' => 'DefinedTerm', 'name' => $p->post_title, 'url' => get_permalink($p->ID) ];
			}
			$set_schema = [ '@context' => 'https://schema.org', '@type' => 'DefinedTermSet', 'name' => wpa_glossary_get_title(), 'hasDefinedTerm' => $schema_items ];
			echo '<script type="application/ld+json">' . wp_json_encode( $set_schema ) . '</script>';

			if ( $is_search ) {
				echo '<div class="wpa-glossary-search-wrapper">';
				echo '<input type="text" class="wpa-glossary-search-input" placeholder="' . esc_attr( wpa_glossary_get_search_label() ) . '">';
				echo '</div>';
			}

			echo '<nav class="wpa-glossary-filter">';
			if ( $atts['hide_all'] !== 'yes' ) {
				echo '<button class="filter active" data-filter="all">' . esc_html( WPA_Theme_Labels::get('glossary_all') ) . '</button>';
			}
			foreach ( array_keys($groups) as $key ) {
				$slug = sanitize_title($key);
				echo '<button class="filter" data-filter=".group-' . esc_attr($slug) . '">' . esc_html($key ) . '</button>';
			}
			echo '</nav>';

			echo '<div id="wpa-glossary-list" class="wpa-glossary-container ' . esc_attr( $atts['layout'] ) . ' wpa-glossary-style-' . esc_attr( $style ) . '" aria-live="polite">';
			
			foreach ( $groups as $key => $items ) {
				$slug = sanitize_title( $key );
				echo '<section id="group-' . esc_attr( $slug ) . '" class="wpa-glossary-group group-' . esc_attr( $slug ) . '">';
				echo '<h3>' . esc_html( $key ) . '</h3>';
				echo '<ul>';
				foreach ( $items as $item_post ) {
					$link = get_permalink( $item_post->ID );
					if ( $style === 'images' ) {
						$excerpt = ! empty( $item_post->post_excerpt ) ? $item_post->post_excerpt : wp_trim_words( $item_post->post_content, 12 );
						$img_url = get_the_post_thumbnail_url( $item_post->ID, 'medium' );
						echo '<li class="wpa-glossary-card-item ' . ( $img_url ? 'has-image' : 'no-image' ) . '">';
						echo '<a href="' . esc_url( $link ) . '" class="wpa-glossary-visual-card">';
						if ( $img_url ) echo '<div class="wpa-card-image" style="background-image: url(' . esc_url( $img_url ) . ');"></div>';
						echo '<div class="wpa-card-content"><h4><dfn>' . esc_html( $item_post->post_title ) . '</dfn></h4><p>' . esc_html( $excerpt ) . '</p></div>';
						echo '</a></li>';
					} else {
						echo '<li><a href="' . esc_url( $link ) . '"><dfn>' . esc_html( $item_post->post_title ) . '</dfn></a></li>';
					}
				}
				echo '</ul></section>';
			}
			echo '</div></div>';

			$output = ob_get_clean();
			set_transient( $cache_key, $output, 12 * HOUR_IN_SECONDS );
		}

		return $output;
	}
}

new WPA_Glossary_Frontend();
