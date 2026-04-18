<?php
/**
 * Admin Features for Glossary Module
 *
 * @package WP Academic Post Enhanced
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * WPA_Glossary_Admin Class
 */
class WPA_Glossary_Admin {

	/**
	 * Constructor
	 */
	public function __construct() {
		// Add Settings Menu
		add_action( 'admin_menu', array( $this, 'add_settings_menu' ) );
		add_action( 'admin_init', array( $this, 'save_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'load_scripts' ) );
		
		// Meta Boxes for other Post Types
		add_action( 'add_meta_boxes', array( $this, 'add_post_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_post_meta_box' ) );
	}

	/**
	 * Add Admin Sub Menu
	 */
	public function add_settings_menu() {
		add_submenu_page( 'edit.php?post_type=wpa_glossary', __( 'Settings', 'wp-academic-post-enhanced' ), __( 'Settings', 'wp-academic-post-enhanced' ), 'manage_options', 'wpa-glossary-settings', array( $this, 'add_settings_page' ) );
		add_submenu_page( 'edit.php?post_type=wpa_glossary', __( 'User Guide', 'wp-academic-post-enhanced' ), __( 'User Guide', 'wp-academic-post-enhanced' ), 'manage_options', 'wpa-glossary-guide', array( $this, 'add_guide_page' ) );
	}
	
	/**
	 * Load Admin Scripts
	 */
	public function load_scripts() {
		// Only load on our pages
		$screen = get_current_screen();
		if ( ! $screen || $screen->post_type !== 'wpa_glossary' ) {
			return;
		}

		// Tabs
		wp_enqueue_script( 'jquery-ui-tabs' );
		
		// Custom Admin CSS/JS can go here if needed
	}

	/**
	 * Render Settings Page
	 */
	public function add_settings_page() {
		$option_sections = self::get_settings();
		?>
		<div class="wrap wpa-settings-wrapper">
			<h1><?php esc_html_e( 'WP Glossary Settings', 'wp-academic-post-enhanced' ); ?></h1>
			
            <div class="wpa-vertical-layout">
                <div class="wpa-vertical-nav">
                    <ul>
                        <li><a href="#group-glossary-visuals" class="wpa-vtab active" data-target="group-glossary-visuals"><?php esc_html_e( 'Visuals & UI', 'wp-academic-post-enhanced' ); ?></a></li>
                        <li><a href="#group-glossary-automation" class="wpa-vtab" data-target="group-glossary-automation"><?php esc_html_e( 'Automation', 'wp-academic-post-enhanced' ); ?></a></li>
                    </ul>
                </div>

                <div class="wpa-vertical-content">
                    <form method="post" action="">
                        <!-- Group 1: Visuals -->
                        <div id="group-glossary-visuals" class="wpa-group-content active">
                            <h2 class="nav-tab-wrapper">
                                <a href="#tab-appearance" class="nav-tab nav-tab-active"><?php echo esc_html( $option_sections['section_appearance']['heading'] ); ?></a>
                                <a href="#tab-tooltip" class="nav-tab"><?php echo esc_html( $option_sections['section_tooltip']['heading'] ); ?></a>
                            </h2>
                            
                            <div id="tab-appearance" class="tab-content active">
                                <?php $this->render_options_table( $option_sections['section_appearance']['options'] ); ?>
                            </div>
                            <div id="tab-tooltip" class="tab-content" style="display:none;">
                                <?php $this->render_options_table( $option_sections['section_tooltip']['options'] ); ?>
                            </div>
                        </div>

                        <!-- Group 2: Automation -->
                        <div id="group-glossary-automation" class="wpa-group-content">
                            <h2 class="nav-tab-wrapper">
                                <a href="#tab-linkify" class="nav-tab nav-tab-active"><?php echo esc_html( $option_sections['section_linkify']['heading'] ); ?></a>
                            </h2>
                            
                            <div id="tab-linkify" class="tab-content active">
                                <?php $this->render_options_table( $option_sections['section_linkify']['options'] ); ?>
                            </div>
                        </div>

                        <input type="hidden" name="action" value="wpa_glossary_save_settings">
                        <?php wp_nonce_field( 'wpa_glossary_settings_action', 'wpa_glossary_settings_nonce' ); ?>
                        <?php submit_button(); ?>
                    </form>
                </div>
            </div>
		</div>
		<?php
	}

    /**
     * Helper to render options table
     */
    private function render_options_table( $options ) {
        ?>
        <table class="form-table">
            <tbody>
                <?php foreach( $options as $option ) : 
                    $value = get_option( $option['name'], $option['default'] );
                ?>
                    <tr>
                        <th scope="row"><label for="<?php echo esc_attr( $option['name'] ); ?>"><?php echo esc_html( $option['label'] ); ?></label></th>
                        <td>
                            <?php if ( $option['type'] == 'text' ) : ?>
                                <input type="text" name="<?php echo esc_attr( $option['name'] ); ?>" id="<?php echo esc_attr( $option['name'] ); ?>" value="<?php echo esc_attr( $value ); ?>" class="regular-text">
                            <?php elseif ( $option['type'] == 'number' ) : ?>
                                <input type="number" name="<?php echo esc_attr( $option['name'] ); ?>" id="<?php echo esc_attr( $option['name'] ); ?>" value="<?php echo esc_attr( $value ); ?>" class="small-text">
                            <?php elseif ( $option['type'] == 'checkbox' ) : ?>
                                <input type="checkbox" name="<?php echo esc_attr( $option['name'] ); ?>" id="<?php echo esc_attr( $option['name'] ); ?>" value="yes" <?php checked( $value, 'yes' ); ?>>
                            <?php elseif ( $option['type'] == 'textarea' ) : ?>
                                <textarea name="<?php echo esc_attr( $option['name'] ); ?>" id="<?php echo esc_attr( $option['name'] ); ?>" rows="5" cols="50" class="large-text"><?php echo esc_textarea( $value ); ?></textarea>
                            <?php elseif ( $option['type'] == 'select' ) : ?>
                                <select name="<?php echo esc_attr( $option['name'] ); ?>" id="<?php echo esc_attr( $option['name'] ); ?>">
                                    <?php foreach ( $option['opts'] as $k => $v ) : ?>
                                        <option value="<?php echo esc_attr( $k ); ?>" <?php selected( $value, $k ); ?>><?php echo esc_html( $v ); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            <?php elseif ( $option['type'] == 'colour' ) : ?>
                                <input type="text" name="<?php echo esc_attr( $option['name'] ); ?>" id="<?php echo esc_attr( $option['name'] ); ?>" value="<?php echo esc_attr( $value ); ?>" class="wpa-color-picker" data-default-color="<?php echo esc_attr( $option['default'] ); ?>">
                            <?php elseif ( $option['type'] == 'checkbox_group' ) : ?>
                                <fieldset>
                                    <?php 
                                    if( ! is_array( $value ) ) $value = (array) $value;
                                    foreach( $option['opts'] as $k => $v ) : ?>
                                        <label><input type="checkbox" name="<?php echo esc_attr( $option['name'] ); ?>[]" value="<?php echo esc_attr( $k ); ?>" <?php checked( in_array( $k, $value ) ); ?>> <?php echo esc_html( $v ); ?></label><br>
                                    <?php endforeach; ?>
                                </fieldset>
                            <?php endif; ?>
                            
                            <?php if ( ! empty( $option['desc'] ) ) : ?>
                                <p class="description"><?php echo wp_kses_post( $option['desc'] ); ?></p>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    }

	/**
	 * Save Settings
	 */
	public function save_settings() {
		if( ! isset( $_POST['action'] ) || $_POST['action'] !== 'wpa_glossary_save_settings' ) {
			return;
		}
		
		if( ! check_admin_referer( 'wpa_glossary_settings_action', 'wpa_glossary_settings_nonce' ) ) {
			return;
		}
		
		if( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$option_sections = self::get_settings();
		
		foreach( $option_sections as $section ) {
			foreach( $section['options'] as $option ) {
				if( isset( $_POST[ $option['name'] ] ) ) {
					$val = $_POST[ $option['name'] ];
					if( $option['type'] == 'textarea' ) {
						$val = wp_kses_post( trim( $val ) );
					} elseif ( $option['type'] == 'checkbox_group' ) {
						$val = array_map( 'sanitize_text_field', $val );
					} elseif( is_string( $val ) ) {
						$val = sanitize_text_field( $val );
					}
					update_option( $option['name'], $val );
				} else {
					// Handle unchecked checkboxes
					if ( $option['type'] == 'checkbox' ) {
						update_option( $option['name'], 'no' );
					}
				}
			}
		}
	}

	/**
	 * Get Settings Array
	 */
	public static function get_settings() {
		return array(
			'section_appearance' => array(
				'heading' => __( 'Appearance', 'wp-academic-post-enhanced' ),
				'options' => array(
					array( 'name' => 'wpa_glossary_alphabet_type', 'label' => __( 'Alphabet Type', 'wp-academic-post-enhanced' ), 'type' => 'select', 'opts' => array( 'english' => 'English (A-Z)', 'arabic' => 'Arabic (أ-ي)' ), 'default' => 'english', 'desc' => __( 'Choose which alphabet set to use for grouping and filtering.', 'wp-academic-post-enhanced' ) ),
					array( 'name' => 'wpa_glossary_accent_color', 'label' => __( 'Accent Color', 'wp-academic-post-enhanced' ), 'type' => 'colour', 'default' => '#2563eb' ),
					array( 'name' => 'wpa_glossary_heading_bg', 'label' => __( 'Heading Background', 'wp-academic-post-enhanced' ), 'type' => 'colour', 'default' => '#f4f4f4' ),
					array( 'name' => 'wpa_glossary_show_latest_news', 'label' => __( 'Show Latest News', 'wp-academic-post-enhanced' ), 'type' => 'checkbox', 'default' => 'no', 'desc' => __( 'Show a "Latest News" card in the glossary single post sidebar.', 'wp-academic-post-enhanced' ) ),
					array( 'name' => 'wpa_glossary_show_latest_courses', 'label' => __( 'Show Latest Courses', 'wp-academic-post-enhanced' ), 'type' => 'checkbox', 'default' => 'no', 'desc' => __( 'Show a "Latest Courses" card in the glossary single post sidebar.', 'wp-academic-post-enhanced' ) ),
				)
			),
			'section_linkify' => array(
				'heading' => __( 'Auto Linkify', 'wp-academic-post-enhanced' ),
				'options' => array(
					array( 'name' => 'wpa_glossary_activate_linkify', 'label' => __( 'Enable Linkify', 'wp-academic-post-enhanced' ), 'type' => 'checkbox', 'default' => 'yes', 'desc' => __( 'Automatically link terms in content.', 'wp-academic-post-enhanced' ) ),
					array( 'name' => 'wpa_glossary_linkify_cache', 'label' => __( 'Enable Persistent Cache', 'wp-academic-post-enhanced' ), 'type' => 'checkbox', 'default' => 'yes', 'desc' => __( 'Significantly improves performance by caching the processed links for each post.', 'wp-academic-post-enhanced' ) ),
					array( 'name' => 'wpa_glossary_linkify_tags', 'label' => __( 'Linkify Synonyms', 'wp-academic-post-enhanced' ), 'type' => 'checkbox', 'default' => 'yes', 'desc' => __( 'Link words found in Glossary Tags to the main term.', 'wp-academic-post-enhanced' ) ),
					array( 'name' => 'wpa_glossary_linkify_case_sensitive', 'label' => __( 'Case Sensitive', 'wp-academic-post-enhanced' ), 'type' => 'checkbox', 'default' => 'no', 'desc' => __( 'Match terms case-sensitively.', 'wp-academic-post-enhanced' ) ),
					array( 'name' => 'wpa_glossary_linkify_sections', 'label' => __( 'Linkify Sections', 'wp-academic-post-enhanced' ), 'type' => 'checkbox_group', 'opts' => array( 'post_content' => 'Content', 'post_excerpt' => 'Excerpt', 'widget' => 'Widgets', 'comment' => 'Comments' ), 'default' => array( 'post_content' ) ),
					array( 'name' => 'wpa_glossary_linkify_post_types', 'label' => __( 'Post Types', 'wp-academic-post-enhanced' ), 'type' => 'checkbox_group', 'opts' => wpa_glossary_get_public_post_types(), 'default' => array( 'post' ) ),
				)
			),
			'section_tooltip' => array(
				'heading' => __( 'Advanced Tooltips', 'wp-academic-post-enhanced' ),
				'options' => array(
					array( 'name' => 'wpa_glossary_activate_tooltip', 'label' => __( 'Enable Tooltips', 'wp-academic-post-enhanced' ), 'type' => 'checkbox', 'default' => 'yes', 'desc' => __( 'Show definition on hover.', 'wp-academic-post-enhanced' ) ),
					array( 'name' => 'wpa_glossary_activate_tooltip_title', 'label' => __( 'Show Title in Tooltip', 'wp-academic-post-enhanced' ), 'type' => 'checkbox', 'default' => 'yes' ),
					array( 'name' => 'wpa_glossary_tooltip_title_format', 'label' => __( 'Title Format', 'wp-academic-post-enhanced' ), 'type' => 'text', 'default' => '{TITLE}' ),
					array( 'name' => 'wpa_glossary_tooltip_content_length', 'label' => __( 'Max Word Count', 'wp-academic-post-enhanced' ), 'type' => 'number', 'default' => 0 ),
					array( 'name' => 'wpa_glossary_activate_tooltip_arrow', 'label' => __( 'Show Arrow', 'wp-academic-post-enhanced' ), 'type' => 'checkbox', 'default' => 'yes' ),
				)
			),
		);
	}

	/**
	 * Add Meta Box to Disable Glossary Features per Post
	 */
	public function add_post_meta_box() {
		$post_types = get_post_types( array( 'public' => true ) );
		foreach ( $post_types as $post_type ) {
			add_meta_box( 'wpa_glossary_post_settings', __( 'WP Glossary Settings', 'wp-academic-post-enhanced' ), array( $this, 'post_meta_box_callback' ), $post_type, 'side' );
		}
	}

	public function post_meta_box_callback( $post ) {
		wp_nonce_field( 'wpa_glossary_post_settings', 'wpa_glossary_post_settings_nonce' );
		
		$disable_tooltip = get_post_meta( $post->ID, 'wpa_glossary_disable_tooltip', true );
		$disable_linkify = get_post_meta( $post->ID, 'wpa_glossary_disable_linkify', true );
		$exclude_index = get_post_meta( $post->ID, 'wpa_glossary_exclude_from_glossary_index', true );
		$exclude_linkify = get_post_meta( $post->ID, 'wpa_glossary_exclude_from_linkify', true );
		?>
		<p>
			<label>
				<input type="checkbox" name="wpa_glossary_disable_tooltip" value="1" <?php checked( $disable_tooltip, '1' ); ?>>
				<?php esc_html_e( 'Disable Tooltips on this page', 'wp-academic-post-enhanced' ); ?>
			</label>
		</p>
		<p>
			<label>
				<input type="checkbox" name="wpa_glossary_disable_linkify" value="1" <?php checked( $disable_linkify, '1' ); ?>>
				<?php esc_html_e( 'Disable Linkify on this page', 'wp-academic-post-enhanced' ); ?>
			</label>
		</p>
		<p>
			<label>
				<input type="checkbox" name="wpa_glossary_exclude_from_glossary_index" value="1" <?php checked( $exclude_index, '1' ); ?>>
				<?php esc_html_e( 'Exclude from Glossary Index', 'wp-academic-post-enhanced' ); ?>
			</label>
		</p>
		<?php if ( $post->post_type == 'wpa_glossary' ) : ?>
		<p>
			<label>
				<input type="checkbox" name="wpa_glossary_exclude_from_linkify" value="1" <?php checked( $exclude_linkify, '1' ); ?>>
				<?php esc_html_e( 'Exclude this term from Linkify', 'wp-academic-post-enhanced' ); ?>
			</label>
		</p>
		<?php endif; ?>
		<?php
	}

	public function save_post_meta_box( $post_id ) {
		if ( ! isset( $_POST['wpa_glossary_post_settings_nonce'] ) || ! wp_verify_nonce( $_POST['wpa_glossary_post_settings_nonce'], 'wpa_glossary_post_settings' ) ) return;
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

		$fields = array( 'wpa_glossary_disable_tooltip', 'wpa_glossary_disable_linkify', 'wpa_glossary_exclude_from_glossary_index', 'wpa_glossary_exclude_from_linkify' );
		foreach ( $fields as $field ) {
			if ( isset( $_POST[ $field ] ) ) {
				update_post_meta( $post_id, $field, '1' );
			} else {
				delete_post_meta( $post_id, $field );
			}
		}
	}

	/**
	 * User Guide Page
	 */
	public function add_guide_page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'WP Glossary User Guide', 'wp-academic-post-enhanced' ); ?></h1>
			<div class="card">
				<h3><?php esc_html_e( 'Shortcode', 'wp-academic-post-enhanced' ); ?></h3>
				<p>Use <code>[wpa_glossary_list]</code> to display the glossary index.</p>
				<p><strong>Attributes:</strong></p>
				<ul>
					<li><code>layout</code>: one_column, two_column, three_column (default)</li>
					<li><code>template</code>: alphabet (default), category</li>
					<li><code>hide_empty</code>: yes/no</li>
				</ul>
			</div>
		</div>
		<?php
	}
}

new WPA_Glossary_Admin();
