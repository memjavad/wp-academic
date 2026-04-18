<?php
/**
 * Glossary Search Widget
 *
 * @package WP Academic Post Enhanced
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPA_Glossary_Widget_Search extends WP_Widget {

	public function __construct() {
		parent::__construct(
			'wpa_glossary_search',
			__( 'WP Glossary - Search', 'wp-academic-post-enhanced' ),
			array( 'description' => __( 'A search box for glossary terms.', 'wp-academic-post-enhanced' ) )
		);
	}

	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );
		$glossary_page_id = get_option( 'wpa_glossary_page_id' );
		$glossary_url = $glossary_page_id ? get_permalink( $glossary_page_id ) : home_url( '/glossary' );

		echo $args['before_widget'];
		if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
		?>
		<form role="search" method="get" class="wpa-glossary-widget-search-form" action="<?php echo esc_url( $glossary_url ); ?>">
			<input type="search" class="wpa-glossary-search-input" placeholder="<?php echo esc_attr( wpa_glossary_get_search_label() ); ?>" value="" name="wpa_s" />
			<button type="submit" class="wpa-btn wpa-btn-primary" style="width:100%; margin-top:10px;"><?php esc_html_e( 'Search', 'wp-academic-post-enhanced' ); ?></button>
		</form>
		<?php
		echo $args['after_widget'];
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = sanitize_text_field( $new_instance['title'] );
		return $instance;
	}

	public function form( $instance ) {
		$title = isset( $instance['title'] ) ? $instance['title'] : __( 'Search Glossary', 'wp-academic-post-enhanced' );
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'wp-academic-post-enhanced' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<?php
	}
}
