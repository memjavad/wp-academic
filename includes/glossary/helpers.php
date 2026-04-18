<?php
/**
 * Core Functions for Glossary Module
 *
 * @package WP Academic Post Enhanced
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Glossary: Get Posts/Terms
 */
function wpa_glossary_get_terms( $type = '' ) {
	$cache_key = 'wpa_glossary_terms_data_' . $type;
	$terms = get_transient( $cache_key );

	if ( false !== $terms ) {
		return apply_filters( 'wpa_glossary_terms', $terms );
	}

	$terms = array();
	
	$query_args = array(
		'post_type'			=> 'wpa_glossary',
		'posts_per_page'	=> -1,
		'orderby'			=> 'title',
		'order'				=> 'ASC',
		'suppress_filters'	=> false
	);
	
	// Exclude Terms
	if ( $type == 'linkify' ) {
		$exclude_term_ids = get_posts(
			array(
				'fields'		=> 'ids',
				'post_type'		=> 'wpa_glossary',
				'numberposts'	=> -1,
				'meta_query'	=> array(
					array(
						'key'	=> 'wpa_glossary_exclude_from_linkify',
						'value'	=> '1'
					)
				)
			)
		);
		
		if ( ! empty( $exclude_term_ids ) ) {
			$query_args['post__not_in']	= $exclude_term_ids;
		}
	}
	
	$terms = get_posts( apply_filters( 'wpa_glossary_terms_query_args', $query_args, $type ) );
	
	if ( ! empty( $terms ) ) {
		foreach ( $terms as $key => $term_post ) {
			$glossary_tags = wp_get_post_terms( $term_post->ID, 'wpa_glossary_tag', array( 'fields' => 'names' ) );
			$terms[ $key ]->glossary_terms = $glossary_tags; 
		}
	}
	
	set_transient( $cache_key, $terms, 12 * HOUR_IN_SECONDS );
	return apply_filters( 'wpa_glossary_terms', $terms );
}

/**
 * Clear Glossary Transients
 */
function wpa_glossary_clear_cache() {
	delete_transient( 'wpa_glossary_terms_data_' );
	delete_transient( 'wpa_glossary_terms_data_linkify' );
	update_option( 'wpa_glossary_last_update', time() );
	
	// Also clear related posts transients
	global $wpdb;
	$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_wpa_glossary_related_%'" );
	$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_timeout_wpa_glossary_related_%'" );
}
add_action( 'save_post_wpa_glossary', 'wpa_glossary_clear_cache' );
add_action( 'deleted_post', 'wpa_glossary_clear_cache' );

/**
 * Glossary: Get Alphabet Type
 */
function wpa_glossary_get_alphabet_type() {
	$options = get_option( 'wpa_homepage_settings', [] );
	$type = isset( $options['wpa_glossary_alphabet_type'] ) ? $options['wpa_glossary_alphabet_type'] : get_option( 'wpa_glossary_alphabet_type', 'english' );
	return apply_filters( 'wpa_glossary_get_alphabet_type', $type );
}

/**
 * Glossary: Get Hardcoded Alphabet
 */
function wpa_glossary_get_alphabet() {
	$type = wpa_glossary_get_alphabet_type();
	
	if ( $type === 'arabic' ) {
		$alphabet = array( 'أ', 'ب', 'ت', 'ث', 'ج', 'ح', 'خ', 'د', 'ذ', 'ر', 'ز', 'س', 'ش', 'ص', 'ض', 'ط', 'ظ', 'ع', 'غ', 'ف', 'ق', 'ك', 'ل', 'م', 'ن', 'ه', 'و', 'ي' );
	} else {
		$alphabet = array( 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z' );
	}
	
	return apply_filters( 'wpa_glossary_alphabet', $alphabet );
}

/**
 * Glossary: Get Title
 */
function wpa_glossary_get_title() {
	$options = get_option( 'wpa_homepage_settings', [] );
	$title = isset( $options['wpa_glossary_title'] ) ? $options['wpa_glossary_title'] : get_option( 'wpa_glossary_title', __( 'Glossary', 'wp-academic-post-enhanced' ) );
	return apply_filters( 'wpa_glossary_title', $title );
}

/**
 * Glossary: Get Slug
 */
function wpa_glossary_get_slug() {
	$options = get_option( 'wpa_homepage_settings', [] );
	$slug = isset( $options['wpa_glossary_slug'] ) ? $options['wpa_glossary_slug'] : get_option( 'wpa_glossary_slug', 'glossary' );
	return apply_filters( 'wpa_glossary_slug', $slug );
}

/**
 * Glossary: Archive?
 */
function wpa_glossary_is_archive() {
	$options = get_option( 'wpa_homepage_settings', [] );
	$is_archive = isset( $options['wpa_glossary_archive'] ) ? $options['wpa_glossary_archive'] : get_option( 'wpa_glossary_archive', 'yes' );
	return apply_filters( 'wpa_glossary_is_archive', ( $is_archive === 'yes' || $is_archive === '1' ) );
}

/**
 * Glossary: Get Page/Post ID
 */
function wpa_glossary_get_page_id() {
	$options = get_option( 'wpa_homepage_settings', [] );
	$page_id = isset( $options['wpa_glossary_page_id'] ) ? $options['wpa_glossary_page_id'] : get_option( 'wpa_glossary_page_id' );
	
	if ( $page_id ) {
		$page = get_post( $page_id );
		
		if ( empty( $page ) ) {
			$pages = get_posts( array(
				'name'			=> $page_id,
				'post_type'		=> get_post_types( array( 'public' => true ) ),
				'post_status'	=> 'publish',
				'numberposts'	=> 1
			) );
			
			if ( ! empty( $pages ) ) {
				$page = $pages[0];
			}
		}
		
		if ( ! empty( $page ) ) {
			$page_id = $page->ID;
		}
	}
	
	return apply_filters( 'wpa_glossary_page_id', $page_id );
}

/**
 * Glossary: Search?
 */
function wpa_glossary_is_search() {
	$options = get_option( 'wpa_homepage_settings', [] );
	$is_search = isset( $options['wpa_glossary_search'] ) ? $options['wpa_glossary_search'] : get_option( 'wpa_glossary_search', 'no' );
	return apply_filters( 'wpa_glossary_is_search', ( $is_search === 'yes' || $is_search === '1' ) );
}

/**
 * Glossary: Search Position
 */
function wpa_glossary_get_search_position() {
	$options = get_option( 'wpa_homepage_settings', [] );
	$position = isset( $options['wpa_glossary_search_position'] ) ? $options['wpa_glossary_search_position'] : get_option( 'wpa_glossary_search_position', 'above' );
	return apply_filters( 'wpa_glossary_search_position', $position );
}

/**
 * Glossary: Search Label
 */
function wpa_glossary_get_search_label() {
	$options = get_option( 'wpa_homepage_settings', [] );
	$label = isset( $options['wpa_glossary_search_label'] ) ? $options['wpa_glossary_search_label'] : get_option( 'wpa_glossary_search_label', 'Search by Keyword ...' );
	return apply_filters( 'wpa_glossary_search_label', $label );
}

/**
 * Glossary: Animation?
 */
function wpa_glossary_is_animation() {
	$options = get_option( 'wpa_homepage_settings', [] );
	$animation = isset( $options['glossary_animation'] ) ? $options['glossary_animation'] : get_option( 'wpa_glossary_animation', 'yes' );
	return apply_filters( 'wpa_glossary_is_animation', ( $animation === 'yes' || $animation === '1' ) );
}

/**
 * Glossary: Get Label - ALL
 */
function wpa_glossary_get_label_all() {
	$label = get_option( 'wpa_glossary_label_all', __( 'All', 'wp-academic-post-enhanced' ) );
	return apply_filters( 'wpa_glossary_label_all', $label );
}

/**
 * Glossary: Is Disable Link?
 */
function wpa_glossary_is_disable_link() {
	$disable = get_option( 'wpa_glossary_disable_link', 'no' );
	return apply_filters( 'wpa_glossary_is_disable_link', ( $disable === 'yes' ) );
}

/**
 * Glossary: New Tab?
 */
function wpa_glossary_is_new_tab() {
	$new_tab = get_option( 'wpa_glossary_new_tab', 'no' );
	return apply_filters( 'wpa_glossary_is_new_tab', ( $new_tab === 'yes' ) );
}

/**
 * Glossary: Is Back Link?
 */
function wpa_glossary_is_back_link() {
	$back_link = get_option( 'wpa_glossary_back_link', 'yes' );
	return apply_filters( 'wpa_glossary_is_back_link', ( $back_link === 'yes' ) );
}

/**
 * Glossary: Get Label - Back Link
 */
function wpa_glossary_get_label_back_link() {
	return WPA_Theme_Labels::get( 'glossary_back_index' );
}

/**
 * Tooltip: Is Active?
 */
function wpa_glossary_is_tooltip() {
	$active = get_option( 'wpa_glossary_activate_tooltip', 'yes' );
	return apply_filters( 'wpa_glossary_is_tooltip', ( $active === 'yes' ) );
}

/**
 * Tooltip: Is Disable on Index Page?
 */
function wpa_glossary_is_tooltip_disable_on_index() {
	$disable = get_option( 'wpa_glossary_disable_tooltip_on_index', 'no' );
	return apply_filters( 'wpa_glossary_is_tooltip_disable_on_index', ( $disable === 'yes' ) );
}

/**
 * Tooltip: Get Theme
 */
function wpa_glossary_get_tooltip_theme() {
	$options = get_option( 'wpa_homepage_settings', [] );
	$theme = isset( $options['glossary_tooltip_theme'] ) ? $options['glossary_tooltip_theme'] : get_option( 'wpa_glossary_tooltip_theme', 'default' );
	return apply_filters( 'wpa_glossary_tooltip_theme', $theme );
}

/**
 * Tooltip: Append Title before Content?
 */
function wpa_glossary_is_tooltip_title() {
	$active = get_option( 'wpa_glossary_activate_tooltip_title', 'yes' );
	return apply_filters( 'wpa_glossary_is_tooltip_title', ( $active === 'yes' ) );
}

/**
 * Tooltip: Title Format before Content?
 */
function wpa_glossary_tooltip_title_format() {
	$format = get_option( 'wpa_glossary_tooltip_title_format', '{TITLE}' );
	return apply_filters( 'wpa_glossary_tooltip_title_format', $format );
}

/**
 * Tooltip: Get Content Type
 */
function wpa_glossary_get_tooltip_content_type() {
	$options = get_option( 'wpa_homepage_settings', [] );
	$type = isset( $options['glossary_tooltip_content_type'] ) ? $options['glossary_tooltip_content_type'] : get_option( 'wpa_glossary_tooltip_content_type', 'excerpt' );
	return apply_filters( 'wpa_glossary_tooltip_content_type', $type );
}

/**
 * Tooltip: Get Content Length
 */
function wpa_glossary_get_tooltip_content_length() {
	$length = get_option( 'wpa_glossary_tooltip_content_length', 0 );
	return apply_filters( 'wpa_glossary_tooltip_content_length', $length );
}

/**
 * Tooltip: Is Content Filter
 */
function wpa_glossary_is_tooltip_content_shortcode() {
	$active = get_option( 'wpa_glossary_tooltip_content_shortcode', 'yes' );
	return apply_filters( 'wpa_glossary_is_tooltip_content_shortcode', ( $active === 'yes' ) );
}

/**
 * Tooltip: Is Read More Link
 */
function wpa_glossary_is_tooltip_content_read_more() {
	$active = get_option( 'wpa_glossary_tooltip_content_read_more', 'no' );
	return apply_filters( 'wpa_glossary_is_tooltip_content_read_more', ( $active === 'yes' ) );
}

/**
 * Tooltip: Get Label - Read More
 */
function wpa_glossary_get_label_tooltip_content_read_more() {
	$label = get_option( 'wpa_glossary_label_tooltip_content_read_more', __( 'Read More', 'wp-academic-post-enhanced' ) );
	return apply_filters( 'wpa_glossary_label_tooltip_content_read_more', $label );
}

/**
 * Tooltip: Get Animation Type
 */
function wpa_glossary_get_tooltip_animation() {
	$animation = get_option( 'wpa_glossary_tooltip_animation', 'fade' );
	return apply_filters( 'wpa_glossary_tooltip_animation', $animation );
}

/**
 * Tooltip: Get Position
 */
function wpa_glossary_get_tooltip_position() {
	$position = get_option( 'wpa_glossary_tooltip_position', 'top' );
	return apply_filters( 'wpa_glossary_tooltip_position', $position );
}

/**
 * Tooltip: Is Bubble Arrow?
 */
function wpa_glossary_is_tooltip_arrow() {
	$active = get_option( 'wpa_glossary_activate_tooltip_arrow', 'yes' );
	return apply_filters( 'wpa_glossary_is_tooltip_arrow', ( $active === 'yes' ) );
}

/**
 * Tooltip: Get Min Width
 */
function wpa_glossary_get_tooltip_min_width() {
	$width = get_option( 'wpa_glossary_tooltip_min_width', 250 );
	return apply_filters( 'wpa_glossary_tooltip_min_width', $width );
}

/**
 * Tooltip: Get Max Width
 */
function wpa_glossary_get_tooltip_max_width() {
	$width = get_option( 'wpa_glossary_tooltip_max_width', 500 );
	return apply_filters( 'wpa_glossary_tooltip_max_width', $width );
}

/**
 * Tooltip: Get Speed
 */
function wpa_glossary_get_tooltip_speed() {
	$speed = get_option( 'wpa_glossary_tooltip_speed', 350 );
	return apply_filters( 'wpa_glossary_tooltip_speed', $speed );
}

/**
 * Tooltip: Get Delay
 */
function wpa_glossary_get_tooltip_delay() {
	$delay = get_option( 'wpa_glossary_tooltip_delay', 200 );
	return apply_filters( 'wpa_glossary_tooltip_delay', $delay );
}

/**
 * Tooltip: Is Touch Device Support?
 */
function wpa_glossary_is_tooltip_touch_devices() {
	$active = get_option( 'wpa_glossary_activate_touch_devices', 'yes' );
	return apply_filters( 'wpa_glossary_is_tooltip_touch_devices', ( $active === 'yes' ) );
}

/**
 * Tooltip: Get Content
 */
function wpa_glossary_get_tooltip_content( $use_shortcode = false, $use_read_more = false, $length = null ) {
	
	$tooltip_content = '';
	
	// Append Title
	if ( wpa_glossary_is_tooltip_title() ) {
		$title_format = wpa_glossary_tooltip_title_format();
		if ( $title_format != '' ) {
			$title_html = $title_format;
			
			if ( strstr( $title_html, "{TITLE}" ) ) {
				$title = apply_filters( 'wpa_glossary_tooltip_term_title_start', '<span class="wpa-glossary-tooltip-term-title">' ) . get_the_title() . apply_filters( 'wpa_glossary_tooltip_term_title_end', '</span>' );
				$title_html = str_replace( "{TITLE}", $title, $title_html );
			}
			
			$tooltip_content .= apply_filters( 'wpa_glossary_tooltip_title_start', '<h3 class="wpa-glossary-tooltip-title">' ) . $title_html . apply_filters( 'wpa_glossary_tooltip_title_end', '</h3>' );
		}
	}
	
	global $post;
	
	if ( ! $post || ! is_a( $post, 'WP_Post' ) ) {
		return '';
	}
	
	// Execute Required Filters On Content
	$content_type = wpa_glossary_get_tooltip_content_type();
	$content = '';

	if ( $content_type == 'content' ) {
		$content = $post->post_content;
		$content = apply_filters( 'wpa_glossary_tooltip_content_filter', $content, $use_shortcode, $use_read_more );
	} else {
		$content = $post->post_excerpt;
		if ( empty( $content ) ) {
			$content = $post->post_content;
		}
		$content = apply_filters( 'wpa_glossary_tooltip_excerpt_filter', $content, $use_shortcode, $use_read_more );
	}
	
	// Limit Number Of Content Words
	$limit = ( $length !== null ) ? intval( $length ) : wpa_glossary_get_tooltip_content_length();
	if ( $limit > 0 ) {
		$content = wp_trim_words( $content, $limit, '...' );
	}
	
	// Read More Link
	if ( $use_read_more ) {
		$content .= apply_filters( 'wpa_glossary_tooltip_read_more_start', '<p class="wpa-glossary-read-more"><a href="'. get_permalink( $post->ID ) .'">', get_permalink( $post->ID ) );
		$content .= wpa_glossary_get_label_tooltip_content_read_more();
		$content .= apply_filters( 'wpa_glossary_tooltip_read_more_end', '</a><p>' );
	}
	
	// Wrap The Content
	$tooltip_content .= apply_filters( 'wpa_glossary_tooltip_content_start', '<div class="wpa-glossary-tooltip-content">' ) . $content . apply_filters( 'wpa_glossary_tooltip_content_end', '</div>' );
	
	return apply_filters( 'wpa_glossary_tooltip_content_final', $tooltip_content );
}

/**
 * Linkify: Is Active?
 */
function wpa_glossary_is_linkify() {
	$active = get_option( 'wpa_glossary_activate_linkify', 'yes' );
	return apply_filters( 'wpa_glossary_is_linkify', ( $active === 'yes' ) );
}

/**
 * Linkify: Get HTML Tags to Exclude
 */
function wpa_glossary_get_linkify_exclude_html_tags() {
	$tags = get_option( 'wpa_glossary_linkify_exclude_html_tags' );
	return apply_filters( 'wpa_glossary_linkify_exclude_html_tags', $tags );
}

/**
 * Linkify: Tags?
 */
function wpa_glossary_is_linkify_tags() {
	$active = get_option( 'wpa_glossary_linkify_tags', 'yes' );
	return apply_filters( 'wpa_glossary_is_linkify_tags', ( $active === 'yes' ) );
}

/**
 * Linkify: Is Disable Link?
 */
function wpa_glossary_is_linkify_disable_link() {
	$disable = get_option( 'wpa_glossary_linkify_disable_link', 'no' );
	return apply_filters( 'wpa_glossary_is_linkify_disable_link', ( $disable === 'yes' ) );
}

/**
 * Linkify: New Tab?
 */
function wpa_glossary_is_linkify_new_tab() {
	$new_tab = get_option( 'wpa_glossary_linkify_new_tab', 'no' );
	return apply_filters( 'wpa_glossary_is_linkify_new_tab', ( $new_tab === 'yes' ) );
}

/**
 * Linkify: Get Zones
 */
function wpa_glossary_get_linkify_sections() {
	$sections = get_option( 'wpa_glossary_linkify_sections', array( 'post_content', 'widget' ) );
	return apply_filters( 'wpa_glossary_linkify_sections', $sections );
}

/**
 * Linkify: Get Post Types
 */
function wpa_glossary_get_linkify_post_types() {
	$post_types = get_option( 'wpa_glossary_linkify_post_types', array( 'post', 'wpa_news', 'wpa_lesson', 'wpa_course' ) );
	return apply_filters( 'wpa_glossary_linkify_post_types', $post_types );
}

/**
 * Linkify: Is on Front Page?
 */
function wpa_glossary_is_linkify_on_front_page() {
	$active = get_option( 'wpa_glossary_linkify_on_front_page', 'no' );
	return apply_filters( 'wpa_glossary_is_linkify_on_front_page', ( $active === 'yes' ) );
}

/**
 * Linkify: Get Limit per Term
 */
function wpa_glossary_get_linkify_term_limit() {
	$limit = get_option( 'wpa_glossary_linkify_term_limit', 0 );
	if ( empty( $limit ) || $limit == 0 ) {
		$limit = -1;
	}
	return apply_filters( 'wpa_glossary_linkify_term_limit', $limit );
}

/**
 * Linkify: Is Limit for Full Page Content?
 */
function wpa_glossary_is_linkify_limit_for_full_page() {
	$active = get_option( 'wpa_glossary_linkify_limit_for_full_page', 'no' );
	return apply_filters( 'wpa_glossary_is_linkify_limit_for_full_page', ( $active === 'yes' ) );
}

/**
 * Linkify: Is Case Sensitive?
 */
function wpa_glossary_is_linkify_case_sensitive() {
	$active = get_option( 'wpa_glossary_linkify_case_sensitive', 'no' );
	return apply_filters( 'wpa_glossary_is_linkify_case_sensitive', ( $active === 'yes' ) );
}

/**
 * BuddyPress: Is BuddyPress Page?
 */
function wpa_glossary_is_bp_page() {
	$is_bp_page = false;
	
	if ( function_exists( 'bp_is_members_component' ) ) {
		if ( bp_is_members_component() || bp_is_user() || bp_is_groups_component() || bp_attachments_cover_image_is_edit() ) {
			$is_bp_page = true;
		}
	}
	
	return apply_filters( 'wpa_glossary_is_bp_page', $is_bp_page );
}

/**
 * Glossary Term Custom Title
 */
function wpa_glossary_term_title( $post_id = '', $title = '' ) {
	$custom_title = esc_attr( get_post_meta( $post_id, 'wpa_glossary_custom_post_title', true ) );
	if ( $custom_title != '' ) {
		$title = $custom_title;
	}
	return $title;
}

/**
 * Glossary Term Custom Permalink
 */
function wpa_glossary_term_permalink( $post_id = '', $permalink = '' ) {
	$custom_permalink = esc_attr( get_post_meta( $post_id, 'wpa_glossary_custom_post_permalink', true ) );
	if ( $custom_permalink != '' ) {
		// Enforce absolute URL to prevent relative linking 404 errors during Glossary Linkify
		if ( ! preg_match( '~^(?:f|ht)tps?://~i', $custom_permalink ) && substr( $custom_permalink, 0, 1 ) !== '/' && substr( $custom_permalink, 0, 1 ) !== '#' && substr( $custom_permalink, 0, 7 ) !== 'mailto:' ) {
			$custom_permalink = home_url( '/' . ltrim( $custom_permalink, '/' ) );
		}
		$permalink = $custom_permalink;
	}
	return $permalink;
}

/**
 * Get Post Types for Settings
 */
function wpa_glossary_get_public_post_types() {
	$wpa_post_types = array();
	$post_types = get_post_types( array( 'public' => true ), 'objects' );
	if ( ! empty( $post_types ) ) {
		foreach ( $post_types as $post_type ) {
			$wpa_post_types[ $post_type->name ] = $post_type->labels->name;
		}
	}
	return $wpa_post_types;
}

/**
 * Filter Tooltip Content
 */
function wpa_glossary_tooltip_content_filter_cb( $content, $use_shortcode = false, $use_read_more = false ) {
	$content = wpautop( $content );
	if ( $use_shortcode ) {
		$content = do_shortcode( $content );
	}
	return $content;
}
add_filter( 'wpa_glossary_tooltip_content_filter', 'wpa_glossary_tooltip_content_filter_cb', 10, 3 );

/**
 * Filter Tooltip Excerpt
 */
function wpa_glossary_tooltip_excerpt_filter_cb( $content, $use_shortcode = false, $use_read_more = false ) {
	$content = wpautop( $content );
	
	$content_chunks = get_extended( $content );
	if ( ! empty( $content_chunks ) && ! empty( $content_chunks['main'] ) ) {
		$content = $content_chunks['main'];
	}
	
	if ( $use_shortcode ) {
		$content = do_shortcode( $content );
	}
	return $content;
}
add_filter( 'wpa_glossary_tooltip_excerpt_filter', 'wpa_glossary_tooltip_excerpt_filter_cb', 10, 3 );

/**
 * Hook: Override Glossary Term Title
 */
function wpa_glossary_term_title_filter_cb( $title, $post_id = '' ) {
	if ( ! is_admin() ) {
		$post = get_post( $post_id );
		if ( $post && get_post_type( $post ) == 'wpa_glossary' ) {
			$title = wpa_glossary_term_title( $post_id, $title );
		}
	}
	return $title;
}
add_filter( 'the_title', 'wpa_glossary_term_title_filter_cb', 10, 2 );

/**
 * Hook: Override Glossary Term Permalink
 */
function wpa_glossary_term_permalink_filter_cb( $permalink, $post ) {
	if ( ! is_admin() ) {
		if ( $post && get_post_type( $post ) == 'wpa_glossary' ) {
			$permalink = wpa_glossary_term_permalink( $post->ID, $permalink );
		}
	}
	return $permalink;
}
add_filter( 'post_link', 'wpa_glossary_term_permalink_filter_cb', 10, 2 );
add_filter( 'post_type_link', 'wpa_glossary_term_permalink_filter_cb', 10, 2 );

/**
 * Hook: Add Back Link On Glossary Details Page
 */
function wpa_glossary_after_post_content_cb( $content ) {
	if ( ! is_admin() ) {
		global $post;
		if ( $post && is_singular( 'wpa_glossary' ) && in_the_loop() && $post->ID == get_the_ID() ) {
			if ( wpa_glossary_is_back_link() ) {
				$page_id = wpa_glossary_get_page_id();
				if ( $page_id > 0 ) {
					$content .= apply_filters( 'wpa_glossary_back_link_start', '<p class="wpa-glossary-back-link"><a href="'. get_permalink( $page_id ) .'">', get_permalink( $page_id ) );
					$content .= wpa_glossary_get_label_back_link();
					$content .= apply_filters( 'wpa_glossary_back_link_end', '</a><p>' );
				}
			}
		}
	}
	return $content;
}
add_filter( 'the_content', 'wpa_glossary_after_post_content_cb', 15 );

/**
 * Hook: Terms Sort Order on Glossary Archive Page
 */
function wpa_glossary_terms_order_cb( $query ) {
	if ( ! is_admin() && $query->is_main_query() ) {
		if ( $query->is_post_type_archive( 'wpa_glossary' ) || is_tax( 'wpa_glossary_cat' ) || is_tax( 'wpa_glossary_tag' ) ) {
			$query->set( 'orderby', 'title' );
			$query->set( 'order', 'ASC' );
		}
	}
}
add_filter( 'pre_get_posts', 'wpa_glossary_terms_order_cb' );

/**
 * Hook: Inject Glossary CSS Variables into Core Theme
 */
function wpa_glossary_inject_css_variables( $css ) {
	$options = get_option( 'wpa_homepage_settings' );
	
	// Glossary Variables
	$glossary_accent = ! empty( $options['accent_color'] ) ? $options['accent_color'] : '#2563eb';
	$glossary_head_bg = ! empty( $options['glossary_heading_bg'] ) ? $options['glossary_heading_bg'] : '#f4f4f4';
	$glossary_per_row = ! empty( $options['glossary_terms_per_row'] ) ? $options['glossary_terms_per_row'] : '3';

	$css .= "
                    --wpa-glossary-accent: {$glossary_accent};
                    --wpa-glossary-head-bg: {$glossary_head_bg};
                    --wpa-glossary-columns: {$glossary_per_row};";
	
	return $css;
}
add_filter( 'wpa_core_css_variables', 'wpa_glossary_inject_css_variables' );
