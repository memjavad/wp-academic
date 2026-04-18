<?php
/**
 * Linkify Content for Glossary Module
 *
 * @package WP Academic Post Enhanced
 */
 
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WPA_Glossary_Linkify {

	/**
	 * @vars
	 */
	public $is_active = '';
	public $is_linkify_tags = '';
	public $exclude_html_tags = '';
	public $is_disable_link = '';
	public $is_new_tab = '';
	public $linkify_sections = '';
	public $linkify_post_types = '';
	public $is_on_front_page = '';
	public $term_limit = '';
	public $is_term_limit_for_full_page = '';
	public $is_case_sensitive = '';
	public $is_tooltip = '';
	public $is_tooltip_content_shortcode = '';
	public $is_tooltip_content_read_more = '';
	public $disabled_linkify_on_posts = array ();
	public $disabled_tooltip_on_posts = array ();
	public $glossary_terms = '';
	public $glossary_term_titles = '';
	public $replaced_terms = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		// Setup Linkify Vars
		add_action( 'wp', array( $this, 'setup_vars' ) );
		
		// Initiate Linkify
		add_action( 'wp', array( $this, 'init_linkify' ) );
	}
	
	/**
	 * Setup Linkify Vars
	 */
	public function setup_vars() {
		
		$this->is_active = wpa_glossary_is_linkify();
		if( ! $this->is_active ) {
			return;
		}
		
		$this->is_on_front_page = wpa_glossary_is_linkify_on_front_page();
		if( is_front_page() && ! $this->is_on_front_page ) {
			return;
		}
		
		$this->linkify_sections = wpa_glossary_get_linkify_sections();
		if( empty( $this->linkify_sections ) ) {
			return;
		}
		
		$this->is_case_sensitive = wpa_glossary_is_linkify_case_sensitive();
		$this->is_linkify_tags = wpa_glossary_is_linkify_tags();
		
		$this->exclude_html_tags = wpa_glossary_get_linkify_exclude_html_tags();
		if( $this->exclude_html_tags != '' ) {
			$this->exclude_html_tags = explode( ',', $this->exclude_html_tags );
			foreach( $this->exclude_html_tags as $key => $html_tag ) {
				$html_tag = trim( $html_tag );
				if( $html_tag != '' ) {
					$this->exclude_html_tags[ $key ] = '[not(ancestor::'.$html_tag.')]';
				}
			}
			
			$this->exclude_html_tags = implode( '', $this->exclude_html_tags );
		}
		
		// Disabled Linkify on Posts
		$this->disabled_linkify_on_posts = get_posts (
			array (
				'fields'		=> 'ids',
				'post_type'		=> 'wpa_glossary',
				'numberposts'	=> -1,
				'meta_query'	=> array (
					array (
						'key'	=> 'wpa_glossary_disable_linkify',
						'value'	=> '1'
					)
				)
			)
		);
		
		// Disabled Tooltip on Posts
		$this->disabled_tooltip_on_posts = get_posts (
			array (
				'fields'		=> 'ids',
				'post_type'		=> 'wpa_glossary',
				'numberposts'	=> -1,
				'meta_query'	=> array (
					array (
						'key'	=> 'wpa_glossary_disable_tooltip',
						'value'	=> '1'
					)
				)
			)
		);
		
		$this->glossary_terms = wpa_glossary_get_terms( 'linkify' );
		if( empty( $this->glossary_terms ) ) {
			return;
		}
		
		usort( $this->glossary_terms, array( $this, 'sort_glossary_terms' ) );
		$this->format_glossary_terms();		
		if( empty( $this->glossary_terms ) ) {
			return;
		}
		
		$this->glossary_term_titles = array_keys( $this->glossary_terms );
		
		$this->is_tooltip = wpa_glossary_is_tooltip();
		$this->is_tooltip_content_shortcode = wpa_glossary_is_tooltip_content_shortcode();
		$this->is_tooltip_content_read_more = wpa_glossary_is_tooltip_content_read_more();
		$this->is_new_tab = wpa_glossary_is_linkify_new_tab();
		$this->is_disable_link = wpa_glossary_is_linkify_disable_link();
		$this->linkify_post_types = wpa_glossary_get_linkify_post_types();
		$this->term_limit = wpa_glossary_get_linkify_term_limit();
		$this->is_term_limit_for_full_page = wpa_glossary_is_linkify_limit_for_full_page();
	}
	
	/**
	 * Sort Glossary Terms Array: Fixed where a term contains another term
	 */
	public function sort_glossary_terms( $term1, $term2 ) {
		return strlen( $term2->post_title ) - strlen( $term1->post_title );
	}
	
	/**
	 * Format Glossary Terms Array
	 */
	public function format_glossary_terms() {
		$wpa_glossary_terms = array();
		
		global $post;
		foreach( $this->glossary_terms as $glossary_term ) {
			
			if( isset( $post->ID ) && $post->ID === $glossary_term->ID ) {
				continue;
			}
			
			$wpa_glossary_terms_key = array();
			
			// Term Title
			$wpa_glossary_terms_key[] = $this->format_glossary_term_string( $glossary_term->post_title );
			
			// Term Tags
			if( $this->is_linkify_tags && ! empty( $glossary_term->glossary_terms ) ) {
				foreach( $glossary_term->glossary_terms as $key => $term ) {
					$wpa_glossary_terms_key[] = $this->format_glossary_term_string( $term );
				}
			}
			
			$wpa_glossary_terms_key = implode( "|", $wpa_glossary_terms_key );
			
			if( ! isset( $wpa_glossary_terms[ $wpa_glossary_terms_key ] ) ) {			
				$wpa_glossary_terms[ $wpa_glossary_terms_key ] = $glossary_term;			
			}
		}
		
		$this->glossary_terms = $wpa_glossary_terms;
	}
	
	/**
	 * Format Linkify String
	 */
	public function format_glossary_term_string( $string='' ) {
		
		if( $string == '' ) {
			return;
		}
		
		$string = str_replace( '&#039;', "’", preg_quote( htmlspecialchars( trim( $string ), ENT_QUOTES, 'UTF-8' ), '/' ) );
		
		if ( ! $this->is_case_sensitive ) {
			$string = mb_strtolower( $string );
		}
		
		return $string;
	}

	/**
	 * Init Linkify
	 */
	public function init_linkify() {
		
		// Check if Linkify is enabled or not
		if( ! $this->is_active ) {
			return;
		}
		
		if( empty( $this->linkify_sections ) ) {
			return;
		}
		
		if( empty( $this->glossary_terms ) ) {
			return;
		}
		
		// Linkify Full Description
		if( in_array( 'post_content', $this->linkify_sections ) ) {
			if( ! wpa_glossary_is_bp_page() ) {
				add_filter( 'the_content', array( $this, 'linkify_content' ), 13, 2 );
			}
		}
		
		// Linkify Short Description
		if( in_array( 'post_excerpt', $this->linkify_sections ) ) {
			add_filter( 'the_excerpt', array( $this, 'linkify_content' ), 13, 2 );
		}
		
		// Linkify Categories / Terms Description
		if( in_array( 'term_content', $this->linkify_sections ) ) {
			add_filter( 'term_description', array( $this, 'linkify_term_content' ), 13, 2 );
		}
		
		// Linkify Widget
		if( in_array( 'widget', $this->linkify_sections ) ) {
			add_filter( 'widget_text', array( $this, 'linkify_widget' ), 13, 2 );
		}
		
		// Linkify Comment
		if( in_array( 'comment', $this->linkify_sections ) ) {
			add_filter( 'get_comment_text', array( $this, 'linkify_comment' ), 13, 2 );
			add_filter( 'get_comment_excerpt', array( $this, 'linkify_comment' ), 13, 2 );
		}
	}
	
	/**
	 * Linkify Full Description
	 */
	public function linkify_content ( $content ) {
		global $post;
		
		if ( empty ( $this->linkify_post_types ) || ( isset ( $post->post_type ) && ! in_array ( $post->post_type, $this->linkify_post_types ) ) ) {
			return $content;
		}
		
		if ( ! empty ( $this->disabled_linkify_on_posts ) && isset ( $post->ID ) && in_array ( $post->ID, $this->disabled_linkify_on_posts ) ) {
			return $content;
		}

		// PERFORMANCE: Check persistent cache
		$enable_cache = get_option( 'wpa_glossary_linkify_cache', 'yes' ) === 'yes';
		if ( $enable_cache ) {
			$cached = get_post_meta( $post->ID, '_wpa_glossary_linkify_cache', true );
			// Check if cache is still valid (based on glossary update time)
			$cache_time = get_post_meta( $post->ID, '_wpa_glossary_linkify_cache_time', true );
			$last_glossary_update = get_option( 'wpa_glossary_last_update', 0 );

			if ( $cached && $cache_time > $last_glossary_update ) {
				return $cached;
			}
		}
		
		$linkified = $this->filter_text( $content );

		if ( $enable_cache ) {
			update_post_meta( $post->ID, '_wpa_glossary_linkify_cache', $linkified );
			update_post_meta( $post->ID, '_wpa_glossary_linkify_cache_time', time() );
		}

		return $linkified;
	}
	
	/**
	 * Linkify Categories / Terms Description
	 */
	public function linkify_term_content( $content ) {
	
		if( ! is_category() && ! is_tax() ) {
			return $content;
		}
		
		if ( empty ( $this->linkify_post_types ) ) {
			return $content;
		}
		
		$queried_object = get_queried_object();
		
		if( empty( $queried_object ) || ! isset( $queried_object->term_id ) ) {
			return $content;
		}
		
		$taxonomy = get_taxonomy( $queried_object->taxonomy );
		
		$common_post_types = array_intersect ( $taxonomy->object_type, $this->linkify_post_types );
		if( empty( $common_post_types ) ) {
			return $content;
		}
		
		return $this->filter_text( $content );
	}
	
	/**
	 * Linkify Widget
	 */
	public function linkify_widget( $content ) {
		return $this->filter_text( $content );
	}
	
	/**
	 * Linkify Comment
	 */
	public function linkify_comment( $content, $comment ) {
		$comment_post = get_post ( $comment->comment_post_ID );
		
		if ( empty ( $this->linkify_post_types ) || ( isset ( $comment_post->post_type ) && ! in_array ( $comment_post->post_type, $this->linkify_post_types ) ) ) {
			return $content;
		}
		
		if ( ! empty ( $this->disabled_linkify_on_posts ) && isset ( $comment_post->ID ) && in_array ( $comment_post->ID, $this->disabled_linkify_on_posts ) ) {
			return $content;
		}
		
		return $this->filter_text( $content );
	}
	
	/**
	 * Linkify Text
	 */
	public function filter_text( $content ) {
		
		if( empty( $content ) || empty( $this->glossary_term_titles ) ) {
			return $content;
		}
		
		// 1. PERFORMANCE: Early exit check
		// If the content is small and doesn't contain any obvious term parts, skip heavy parsing.
		// This is a rough check but saves CPU on non-academic content.
		if ( strlen( $content ) < 100 ) {
			$found = false;
			foreach ( array_slice($this->glossary_term_titles, 0, 10) as $title ) {
				if ( mb_stripos( $content, $title ) !== false ) {
					$found = true;
					break;
				}
			}
			if ( ! $found ) return $content;
		}

		// Empty replaced terms everytime a new content block is there
		if( ! $this->is_term_limit_for_full_page ) {
			$this->replaced_terms = array();
		}
		
		// 2. PERFORMANCE: Load DOM only ONCE
		$dom = new DOMDocument();
		libxml_use_internal_errors( true );
		if ( ! $dom->loadHtml( mb_convert_encoding( $content, 'HTML-ENTITIES', "UTF-8" ) ) ) {
			libxml_clear_errors();
		}
		$xpath = new DOMXPath( $dom );
		$query = "//text()[not(ancestor::script)][not(ancestor::style)][not(ancestor::a)]{$this->exclude_html_tags}";

		$is_space_separated		 = TRUE;
		$glossary_term_titles_chunk = array_chunk( $this->glossary_term_titles, 150, TRUE );
		
		foreach( $glossary_term_titles_chunk as $glossary_term_titles ) {
			$regex = '/' . ( ( $is_space_separated ) ? '(?<=\P{L}|^)(?<!(\p{N}))' : '') . '(?!(<|&lt;))(' . ( ! $this->is_case_sensitive ? '(?i)' : '' ) . implode( '|', $glossary_term_titles ) . ')(?!(>|&gt;))' . ( ( $is_space_separated ) ? '(?=\P{L}|$)(?!(\p{N}))' : '') . '/u';
			
			foreach ( $xpath->query( $query ) as $node ) {
				$replaced = preg_replace_callback( $regex, array( $this, 'preg_replace_matches' ), htmlspecialchars( $node->wholeText, ENT_COMPAT ) );
				if ( ! empty( $replaced ) && $replaced !== $node->wholeText ) {
					$new_node = $dom->createDocumentFragment();
					// We wrap in CDATA to handle HTML generated by the callback
					if ( $new_node->appendXML( '<![CDATA[' . $replaced . ']]>' ) !== false ) {
						$node->parentNode->replaceChild( $new_node, $node );
					}
				}
			}
		}

		// 3. PERFORMANCE: Save and clean up
		$body_node = $xpath->query( '//body' )->item( 0 );
		if ( $body_node !== NULL ) {
			$new_dom = new DOMDocument();
			$new_dom->appendChild( $new_dom->importNode( $body_node, TRUE ) );
			$internal_html = $new_dom->saveHTML();
			$content = mb_substr( trim( $internal_html ), 6, (mb_strlen( $internal_html ) - 14 ), "UTF-8" );
			$content = preg_replace( '#(<img[^>]*[^/])>#Ui', '$1/>', $content );
		}
		
		return trim( $content );
	}
	
	/**
	 * Replace Matching Terms
	 */
	public function preg_replace_matches( $match ) {
		if ( ! empty( $match[0] ) ) {
			$title = htmlspecialchars_decode( $match[0], ENT_COMPAT );
			$glossary_term = array();
			
			if ( ! empty( $this->glossary_terms ) ) {
				$title_index = $this->format_glossary_term_string( $title );
				
				// First - look for exact keys
				if ( array_key_exists( $title_index, $this->glossary_terms ) ) {
					$glossary_term = $this->glossary_terms[ $title_index ];
				} else {
					// If not found - try the tags
					foreach ( $this->glossary_terms as $key => $value ) {
						if ( strstr( $key, '|' ) && strstr( $key, $title_index ) ) {
							$glossary_term_tags = explode( '|', $key );
							if ( in_array( $title_index, $glossary_term_tags ) ) {
								$glossary_term = $value;
								break;
							}
						}
					}
				}
			}
			
			if( ! empty( $glossary_term ) ) {
				
				// Check Limit per Term
				if( $this->term_limit > 0 ) {
					$this->replaced_terms[ $glossary_term->ID ] = ( isset( $this->replaced_terms[ $glossary_term->ID ] ) && $this->replaced_terms[ $glossary_term->ID ] > 0 ) ? ( $this->replaced_terms[ $glossary_term->ID ] + 1 ) : 1;
					
					if( $this->replaced_terms[ $glossary_term->ID ] > $this->term_limit ) {
						return $title;
					}
				}
				
				global $post;
				$current_post	= $post;
				$post			= $glossary_term;
				setup_postdata( $post );
				
				$title_place_holder = '##TITLE_GOES_HERE##';
				
				if( $this->is_disable_link ) {
					$href = '';
				} else {
					$href = 'href="' . esc_url( get_permalink() ) . '"';
				}
								
				if ( $this->is_tooltip && ! ( ! empty ( $this->disabled_tooltip_on_posts ) && isset ( $current_post->ID ) && in_array ( $current_post->ID, $this->disabled_tooltip_on_posts ) ) ) {
					$attr_title = wpa_glossary_get_tooltip_content( $this->is_tooltip_content_shortcode, $this->is_tooltip_content_read_more );
					
					$new_text = '<a class="wpa-glossary-linkify wpa-glossary-tooltip" title="' . $attr_title . '" ' . $href . ' '. ( $this->is_new_tab ? 'target="_blank"' : '' ) .'>' . $title_place_holder . '</a>';
				} else {
					$new_text = '<a class="wpa-glossary-linkify" ' . $href . ' '. ( $this->is_new_tab ? 'target="_blank"' : '' ) .'>' . $title_place_holder . '</a>';
				}
				
				wp_reset_postdata();
				$post = $current_post; // Restore original post
				
				$new_text = str_replace( $title_place_holder, $title, $new_text );
				return $new_text;
			}
		}
	}
}

new WPA_Glossary_Linkify();
