<?php
/**
 * Field News Recent Posts Widget
 *
 * @package WP Academic Post Enhanced
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPA_Field_News_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'wpa_field_news_widget',
            __( 'WPA: Recent Field News', 'wp-academic-post-enhanced' ),
            [ 'description' => __( 'Displays latest news stories with thumbnails.', 'wp-academic-post-enhanced' ) ]
        );
    }

    public function widget( $args, $instance ) {
        $title = ! empty( $instance['title'] ) ? $instance['title'] : WPA_Theme_Labels::get('news_latest');
        $count = ! empty( $instance['count'] ) ? absint( $instance['count'] ) : 5;

        echo $args['before_widget'];
        if ( ! empty( $title ) ) {
            echo $args['before_title'] . esc_html( $title ) . $args['after_title'];
        }

        $q = new WP_Query([
            'post_type' => 'wpa_news',
            'posts_per_page' => $count,
            'post_status' => 'publish',
            'no_found_rows' => true
        ]);

        if ( $q->have_posts() ) {
            echo '<ul class="wpa-recent-news-list">';
            while ( $q->have_posts() ) {
                $q->the_post();
                ?>
                <li class="wpa-news-widget-item">
                    <?php if ( has_post_thumbnail() ) : ?>
                        <a href="<?php the_permalink(); ?>" class="wpa-news-widget-thumb">
                            <?php the_post_thumbnail( 'thumbnail' ); ?>
                        </a>
                    <?php endif; ?>
                    <div class="wpa-news-widget-content">
                        <a href="<?php the_permalink(); ?>" class="wpa-news-widget-title"><?php get_the_title() ? the_title() : the_ID(); ?></a>
                        <span class="wpa-news-widget-date"><?php echo get_the_date(); ?> &bull; <?php echo get_the_author(); ?></span>
                    </div>
                </li>
                <?php
            }
            echo '</ul>';
            wp_reset_postdata();
        } else {
            echo '<p>' . WPA_Theme_Labels::get('label_no_results') . '</p>';
        }

        echo $args['after_widget'];
    }

    public function form( $instance ) {
        $title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Recent News', 'wp-academic-post-enhanced' );
        $count = ! empty( $instance['count'] ) ? $instance['count'] : 5;
        ?>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'wp-academic-post-enhanced' ); ?></label>
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>"><?php esc_html_e( 'Number of posts:', 'wp-academic-post-enhanced' ); ?></label>
            <input class="tiny-text" id="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'count' ) ); ?>" type="number" step="1" min="1" value="<?php echo esc_attr( $count ); ?>" size="3">
        </p>
        <?php
    }

    public function update( $new_instance, $old_instance ) {
        $instance = [];
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? sanitize_text_field( $new_instance['title'] ) : '';
        $instance['count'] = ( ! empty( $new_instance['count'] ) ) ? absint( $new_instance['count'] ) : 5;
        return $instance;
    }
}
