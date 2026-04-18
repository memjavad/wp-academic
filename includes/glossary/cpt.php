<?php
/**
 * Custom Post Types for Glossary Module
 *
 * @package WP Academic Post Enhanced
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * WPA_Glossary_CPT Class
 */
class WPA_Glossary_CPT {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', array( __CLASS__, 'register_post_types' ) );
		add_action( 'admin_init', array( __CLASS__, 'disable_tags_auto_suggestion' ) );
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_boxes' ) );
		add_action( 'save_post', array( __CLASS__, 'save_meta_boxes' ) );
	}

	/**
	 * Register Post Types
	 */
	public static function register_post_types() {
			
		// Post Type: wpa_glossary
		$title = wpa_glossary_get_title();
		
		$labels = array(
			'name'					=> $title,
			'singular_name'			=> $title,
			'menu_name'				=> $title,
			'name_admin_bar'		=> $title,
			'add_new'				=> __( 'Add New Term', 'wp-academic-post-enhanced' ),
			'add_new_item'			=> __( 'Add New Term', 'wp-academic-post-enhanced' ),
			'new_item'				=> __( 'New Term', 'wp-academic-post-enhanced' ),
			'edit_item'				=> __( 'Edit Term', 'wp-academic-post-enhanced' ),
			'view_item'				=> __( 'View Term', 'wp-academic-post-enhanced' ),
			'all_items'				=> __( 'All Terms', 'wp-academic-post-enhanced' ),
			'search_items'			=> __( 'Search Terms', 'wp-academic-post-enhanced' ),
			'parent_item_colon'		=> __( 'Parent Terms:', 'wp-academic-post-enhanced' ),
			'not_found'				=> __( 'No terms found.', 'wp-academic-post-enhanced' ),
			'not_found_in_trash'	=> __( 'No terms found in Trash.', 'wp-academic-post-enhanced' )
		);
		
		$args = array(
			'labels'				=> $labels,
			'description'			=> __( 'Glossary Terms.', 'wp-academic-post-enhanced' ),
			'menu_icon'				=> 'dashicons-editor-spellcheck',
			'capability_type'		=> 'post',
			'rewrite'				=> array( 'slug' => wpa_glossary_get_slug(), 'with_front' => false ),
			'public'				=> true,
			'publicly_queryable'	=> true,
			'show_ui'				=> true,
			'show_in_nav_menus'		=> false,
			'show_in_menu'			=> true,
			'query_var'				=> true,
			'has_archive'			=> wpa_glossary_is_archive(),
			'hierarchical'			=> false,
			'menu_position'			=> 58,
			'supports'				=> array( 'title', 'excerpt', 'editor', 'thumbnail', 'author', 'comments' )
		);

		register_post_type( 'wpa_glossary', apply_filters( 'wpa_glossary_post_type_args', $args ) );
		
		// Taxonomy: wpa_glossary_cat
		$cat_labels = array(
			'name'						=> __( 'Categories', 'wp-academic-post-enhanced' ),
			'singular_name'				=> __( 'Category', 'wp-academic-post-enhanced' ),
			'search_items'				=> __( 'Search Glossary Categories', 'wp-academic-post-enhanced' ),
			'popular_items'				=> __( 'Popular Glossary Categories', 'wp-academic-post-enhanced' ),
			'all_items'					=> __( 'All Glossary Categories', 'wp-academic-post-enhanced' ),
			'parent_item'				=> null,
			'parent_item_colon'			=> null,
			'edit_item'					=> __( 'Edit Glossary Category', 'wp-academic-post-enhanced' ),
			'update_item'				=> __( 'Update Glossary Category', 'wp-academic-post-enhanced' ),
			'add_new_item'				=> __( 'Add New Glossary Category', 'wp-academic-post-enhanced' ),
			'new_item_name'				=> __( 'New Glossary Category Name', 'wp-academic-post-enhanced' ),
			'menu_name'					=> __( 'Categories', 'wp-academic-post-enhanced' ),
		);

		$cat_args = array(
			'hierarchical'          => true,
			'labels'                => $cat_labels,
			'show_ui'               => true,
			'show_in_nav_menus'		=> false,
			'show_admin_column'     => true,
			'query_var'             => true,
			'rewrite'               => array( 'slug' => 'wpa_glossary_cat' ),
		);

		register_taxonomy( 'wpa_glossary_cat', 'wpa_glossary', apply_filters( 'wpa_glossary_cat_args', $cat_args ) );
		
		// Taxonomy: wpa_glossary_tag
		$tag_labels = array(
			'name'						=> __( 'Tags', 'wp-academic-post-enhanced' ),
			'singular_name'				=> __( 'Tag', 'wp-academic-post-enhanced' ),
			'search_items'				=> __( 'Search Glossary Tags', 'wp-academic-post-enhanced' ),
			'popular_items'				=> __( 'Popular Glossary Tags', 'wp-academic-post-enhanced' ),
			'all_items'					=> __( 'All Glossary Tags', 'wp-academic-post-enhanced' ),
			'edit_item'					=> __( 'Edit Glossary Tag', 'wp-academic-post-enhanced' ),
			'update_item'				=> __( 'Update Glossary Tag', 'wp-academic-post-enhanced' ),
			'add_new_item'				=> __( 'Add New Glossary Tag', 'wp-academic-post-enhanced' ),
			'new_item_name'				=> __( 'New Glossary Tag Name', 'wp-academic-post-enhanced' ),
			'menu_name'					=> __( 'Tags', 'wp-academic-post-enhanced' ),
		);

		$tag_args = array(
			'hierarchical'          => false,
			'labels'                => $tag_labels,
			'show_ui'               => true,
			'show_in_menu'			=> true,
			'show_in_nav_menus'		=> false,
			'show_admin_column'     => true,
			'query_var'             => true,
			'rewrite'               => array( 'slug' => 'wpa_glossary_tag' ),
		);

		register_taxonomy( 'wpa_glossary_tag', 'wpa_glossary', apply_filters( 'wpa_glossary_tag_args', $tag_args ) );
	}
	
	/**
	 * Disable Auto Suggestion for Glossary Tags
	 */
	public static function disable_tags_auto_suggestion() {
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			if ( isset( $_GET['action'] ) && $_GET['action'] == 'ajax-tag-search' && isset( $_GET['tax'] ) && $_GET['tax'] == 'wpa_glossary_tag' ) {
				die;
			}
		}
	}
	
	/**
	 * Add Custom Meta Boxes
	 */
	public static function add_meta_boxes() {
		add_meta_box( 'wpa-glossary-attributes', __( 'Custom Attributes', 'wp-academic-post-enhanced' ), array( __CLASS__, 'meta_box_glossary_attributes' ), 'wpa_glossary', 'normal', 'high' );
	}
	
	/**
	 * Custom Meta Box Callback
	 */
	public static function meta_box_glossary_attributes( $post ) {
		wp_nonce_field( 'wpa_glossary_meta_box', 'wpa_glossary_meta_box_nonce' );
		
		?>
        <table class="form-table">
			<tbody>
				<tr>
					<th scope="row"><label for="wpa_glossary_custom_post_title"><?php esc_html_e( 'Post Title', 'wp-academic-post-enhanced' ); ?></label></th>
					<td>
						<input type="text" class="large-text" id="wpa_glossary_custom_post_title" name="wpa_glossary_custom_post_title" value="<?php echo esc_attr( get_post_meta( $post->ID, 'wpa_glossary_custom_post_title', true ) ); ?>" />
						<p class="description"><?php esc_html_e( 'This option allows you to use custom post title for current glossary term. This option works with glossary details page and tooltip only.', 'wp-academic-post-enhanced' ); ?></p>
					</td>
				</tr>
				
				<tr>
					<th scope="row"><label for="wpa_glossary_custom_post_permalink"><?php esc_html_e( 'Custom URL', 'wp-academic-post-enhanced' ); ?></label></th>
					<td>
						<input type="text" class="large-text" id="wpa_glossary_custom_post_permalink" name="wpa_glossary_custom_post_permalink" value="<?php echo esc_attr( get_post_meta( $post->ID, 'wpa_glossary_custom_post_permalink', true ) ); ?>" />
						<p class="description"><?php esc_html_e( 'This option allows you to use external URL for current glossary term. This option is usefull when you want user to redirect on wikipedia or some other dictionary URL for this particular term rather than having complete description on your website.', 'wp-academic-post-enhanced' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><label for="wpa_glossary_audio_url"><?php esc_html_e( 'Audio Pronunciation URL', 'wp-academic-post-enhanced' ); ?></label></th>
					<td>
						<input type="text" class="large-text" id="wpa_glossary_audio_url" name="wpa_glossary_audio_url" value="<?php echo esc_attr( get_post_meta( $post->ID, 'wpa_glossary_audio_url', true ) ); ?>" placeholder="https://example.com/audio/term.mp3" />
						<p class="description"><?php esc_html_e( 'Enter a URL to an audio file (MP3) to display a pronunciation player next to the term.', 'wp-academic-post-enhanced' ); ?></p>
					</td>
				</tr>
			</tbody>
		</table>
        <?php
	}
	
	/**
	 * Save Custom Meta Boxes
	 */
	public static function save_meta_boxes( $post_id ) {
		if ( ! isset( $_POST['wpa_glossary_meta_box_nonce'] ) ) {
			return $post_id;
		}

		if ( ! wp_verify_nonce( $_POST['wpa_glossary_meta_box_nonce'], 'wpa_glossary_meta_box' ) ) {
			return $post_id;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}

		if ( isset( $_POST['wpa_glossary_custom_post_title'] ) ) {
			update_post_meta( $post_id, 'wpa_glossary_custom_post_title', sanitize_text_field( $_POST['wpa_glossary_custom_post_title'] ) );
		}
		
		if ( isset( $_POST['wpa_glossary_custom_post_permalink'] ) ) {
			update_post_meta( $post_id, 'wpa_glossary_custom_post_permalink', esc_url_raw( $_POST['wpa_glossary_custom_post_permalink'] ) );
		}

		if ( isset( $_POST['wpa_glossary_audio_url'] ) ) {
			update_post_meta( $post_id, 'wpa_glossary_audio_url', esc_url_raw( $_POST['wpa_glossary_audio_url'] ) );
		}
	}
}

new WPA_Glossary_CPT();
