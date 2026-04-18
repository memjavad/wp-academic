<?php
/**
 * Glossary Widget - Related Posts
 *
 * @package WP Academic Post Enhanced
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * WPA_Glossary_Widget_Related_Posts Class
 */
class WPA_Glossary_Widget_Related_Posts extends WP_Widget {

	/**
	 * Constructor
	 */
	public function __construct() {
		$widget_ops = array(
			'classname' => 'wpa_glossary_widget_related_posts',
			'description' => __( 'Displays a list of post links on the glossary term details page where the glossary term is actually found.', 'wp-academic-post-enhanced' ),
		);
		parent::__construct( 'wpa_glossary_related_posts', __( 'WP Glossary - Related Posts', 'wp-academic-post-enhanced' ), $widget_ops );
	}

	/**
	 * Widget Output
	 */
	public function widget( $args, $instance ) {
		if ( ! is_singular( 'wpa_glossary' ) ) {
			return;
		}

		if ( empty( $instance['post_types'] ) ) {
			return;
		}

		global $post;
		
		// 1. Check for Transient Cache
		$cache_key = 'wpa_glossary_related_' . $post->ID;
		$cached_output = get_transient( $cache_key );

		if ( false !== $cached_output ) {
			echo $cached_output;
			return;
		}

		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );
		$number = ! empty( $instance['number_of_posts'] ) ? absint( $instance['number_of_posts'] ) : 5;
		$post_types = $instance['post_types'];

		// 2. Query Args: Exclude current post and set post types
		$query_args = array(
			'posts_per_page' => $number,
			'post_type'      => $post_types,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post__not_in'   => array( $post->ID ), // Exclude self
			'wpa_term_search'=> $post->post_title // Pass term for regex filter
		);
		
		add_filter( 'posts_where', array( $this, 'where_content_filter' ), 10, 2 );
		$related_query = new WP_Query( $query_args );
		remove_filter( 'posts_where', array( $this, 'where_content_filter' ), 10, 2 );

		// 3. Render and Buffer Output
		if ( $related_query->have_posts() ) {
			ob_start();
			echo $args['before_widget'];
			if ( $title ) {
				echo $args['before_title'] . $title . $args['after_title'];
			}
			echo '<ul>';
			while ( $related_query->have_posts() ) {
				$related_query->the_post();
				echo '<li><a href="' . get_permalink() . '">' . get_the_title() . '</a></li>';
			}
			echo '</ul>';
			echo $args['after_widget'];
			wp_reset_postdata();
			
			$output = ob_get_clean();
			
			// 4. Save to Transient (Cache for 12 hours)
			set_transient( $cache_key, $output, 12 * HOUR_IN_SECONDS );
			
			echo $output;
		}
	}

	/**
	 * Where Filter
	 */
	public function where_content_filter( $where, $wp_query ) {
		global $wpdb;
		if ( $term = $wp_query->get( 'wpa_term_search' ) ) {
			// Match exact word boundary for the term within post_content
			// Uses standard MySQL regex boundaries [[:<:]] and [[:>:]]
			$where .= ' AND ' . $wpdb->posts . '.post_content REGEXP \'[[:<:]]' . esc_sql( $wpdb->esc_like( $term ) ) . '[[:>:]]\'';
		}
		return $where;
	}

	/**
	 * Update Widget
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = sanitize_text_field( $new_instance['title'] );
		$instance['number_of_posts'] = (int) $new_instance['number_of_posts'];
		$instance['post_types'] = isset( $new_instance['post_types'] ) ? (array) $new_instance['post_types'] : array();
		return $instance;
	}

	/**
	 * Widget Form
	 */
	public function form( $instance ) {
		$title = isset( $instance['title'] ) ? $instance['title'] : __( 'Related Posts', 'wp-academic-post-enhanced' );
		$number = isset( $instance['number_of_posts'] ) ? $instance['number_of_posts'] : 5;
		$post_types = isset( $instance['post_types'] ) ? $instance['post_types'] : array( 'post' );
		
		$available_post_types = get_post_types( array( 'public' => true ), 'objects' );
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'wp-academic-post-enhanced' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'number_of_posts' ); ?>"><?php _e( 'Number of Posts:', 'wp-academic-post-enhanced' ); ?></label>
			<input class="tiny-text" id="<?php echo $this->get_field_id( 'number_of_posts' ); ?>" name="<?php echo $this->get_field_name( 'number_of_posts' ); ?>" type="number" step="1" min="1" value="<?php echo esc_attr( $number ); ?>" size="3">
		</p>
		<p>
			<label><?php _e( 'Post Types:', 'wp-academic-post-enhanced' ); ?></label><br>
			<?php foreach ( $available_post_types as $pt ) : ?>
				<input type="checkbox" id="<?php echo $this->get_field_id( 'post_types' ) . '_' . $pt->name; ?>" name="<?php echo $this->get_field_name( 'post_types' ); ?>[]" value="<?php echo esc_attr( $pt->name ); ?>" <?php checked( in_array( $pt->name, $post_types ) ); ?>>
				<label for="<?php echo $this->get_field_id( 'post_types' ) . '_' . $pt->name; ?>"><?php echo esc_html( $pt->labels->name ); ?></label><br>
			<?php endforeach; ?>
		</p>
		<?php
	}
}
