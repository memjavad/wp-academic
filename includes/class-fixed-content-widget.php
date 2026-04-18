<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class WPA_Fixed_Content_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'wpa_fixed_content_widget',
            __( 'WPA Fixed Content Widget', 'wp-academic-post-enhanced' ),
            [ 'description' => __( 'A widget that is fixed to the right side of the screen and can hold any content.', 'wp-academic-post-enhanced' ) ]
        );
    }

    public function widget( $args, $instance ) {
        $title = apply_filters( 'widget_title', $instance['title'] );
        $content = $instance['content'] ?? '';

        $styles = [
            'position: fixed;',
            'top: 100px;',
            'right: 20px;',
            'z-index: 9999;',
        ];

        echo '<div class="wpa-fixed-right-widget-container" style="' . esc_attr(implode(' ', $styles)) . '">';
        echo $args['before_widget'];
        if ( ! empty( $title ) ) {
            echo $args['before_title'] . $title . $args['after_title'];
        }
        echo do_shortcode(wp_kses_post($content));
        echo $args['after_widget'];
        echo '</div>';
    }

    public function form( $instance ) {
        $title = $instance['title'] ?? '';
        $content = $instance['content'] ?? '';
        ?>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'wp-academic-post-enhanced' ); ?></label> 
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'content' ) ); ?>"><?php esc_attr_e( 'Content:', 'wp-academic-post-enhanced' ); ?></label>
            <textarea class="widefat" rows="10" id="<?php echo esc_attr( $this->get_field_id( 'content' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'content' ) ); ?>"><?php echo esc_textarea( $content ); ?></textarea>
        </p>
        <?php 
    }

    public function update( $new_instance, $old_instance ) {
        $instance = [];
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? sanitize_text_field( $new_instance['title'] ) : '';
        if ( current_user_can('unfiltered_html') ) {
            $instance['content'] = $new_instance['content'];
        } else {
            $instance['content'] = wp_kses_post( $new_instance['content'] );
        }
        return $instance;
    }
}
