<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Render the Sitemap Optimization section.
 */
function wp_academic_post_enhanced_render_sitemap_section() {
	$sitemap_url      = home_url( '/sitemap-main.xml' );
	$news_sitemap_url = home_url( '/news-sitemap.xml' );
	$last_ping        = get_option( 'wpa_last_sitemap_ping', 0 );
	
	// Get current settings
	$selected_types = get_option( 'wpa_sitemap_post_types', ['post', 'page', 'wpa_news', 'wpa_course', 'wpa_glossary'] );
	$sitemap_limit  = get_option( 'wpa_sitemap_limit', 1000 );
	
	// Get all public post types
	$public_post_types = get_post_types( ['public' => true], 'objects' );
	unset( $public_post_types['attachment'] ); // Usually not wanted in main list
	?>
	<div class="wpa-card wpa-settings-card">
		<div class="wpa-card-header">
			<h3><?php echo WPA_Icons::get('link'); ?> <?php esc_html_e( 'Ultimate Academic Sitemap Engine', 'wp-academic-post-enhanced' ); ?></h3>
			<p><?php esc_html_e( 'Configure your professional multi-file sitemap index.', 'wp-academic-post-enhanced' ); ?></p>
		</div>
		<div class="wpa-card-body">
			
			<form action="options.php" method="post">
				<?php settings_fields( 'wp_academic_post_enhanced_sitemap_options' ); ?>

				<div class="wpa-meta-row" style="margin-bottom: 30px; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));">
					<div class="wpa-meta-item">
						<label><?php esc_html_e( 'Main Sitemap Index URL:', 'wp-academic-post-enhanced' ); ?></label>
						<code><a href="<?php echo esc_url( $sitemap_url ); ?>" target="_blank"><?php echo esc_url( $sitemap_url ); ?></a></code>
					</div>
					<div class="wpa-meta-item">
						<label><?php esc_html_e( 'Google News Feed:', 'wp-academic-post-enhanced' ); ?></label>
						<code><a href="<?php echo esc_url( $news_sitemap_url ); ?>" target="_blank"><?php echo esc_url( $news_sitemap_url ); ?></a></code>
					</div>
				</div>

				<div class="wpa-settings-section">
					<h4><?php esc_html_e( 'Included Content Types', 'wp-academic-post-enhanced' ); ?></h4>
					<p class="wpa-help-text"><?php esc_html_e( 'Select which post types to include in your main sitemap index.', 'wp-academic-post-enhanced' ); ?></p>
					
					<div class="wpa-checkbox-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px; margin: 20px 0;">
						<?php foreach ( $public_post_types as $pt_slug => $pt_obj ) : ?>
							<label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
								<input type="checkbox" name="wpa_sitemap_post_types[]" value="<?php echo esc_attr( $pt_slug ); ?>" <?php checked( in_array( $pt_slug, $selected_types ) ); ?>>
								<span><?php echo esc_html( $pt_obj->labels->name ); ?> (<code><?php echo esc_html( $pt_slug ); ?></code>)</span>
							</label>
						<?php endforeach; ?>
					</div>
				</div>

				<div class="wpa-meta-row" style="margin-top: 30px;">
					<div class="wpa-meta-item">
						<label style="display: block; margin-bottom: 10px;"><?php esc_html_e( 'Items Per Sitemap File:', 'wp-academic-post-enhanced' ); ?></label>
						<input type="number" name="wpa_sitemap_limit" value="<?php echo esc_attr( $sitemap_limit ); ?>" min="100" max="5000" class="regular-text">
						<p class="wpa-help-text"><?php esc_html_e( 'Google allows up to 50,000, but lower values (1000) are better for server performance.', 'wp-academic-post-enhanced' ); ?></p>
					</div>
				</div>

				<div class="wpa-action-row" style="margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
					<?php submit_button( __( 'Save Sitemap Settings', 'wp-academic-post-enhanced' ), 'wpa-btn wpa-btn-primary', 'submit', false ); ?>
					
					<button id="wpa-ping-sitemap" class="wpa-btn wpa-btn-outline" style="margin-right: 15px;">
						<?php echo WPA_Icons::get('send'); ?> <?php esc_html_e( 'Manual Instant Ping', 'wp-academic-post-enhanced' ); ?>
					</button>
				</div>
                
                <p class="wpa-status-text" style="font-size: 13px; color: #666; margin-top: 15px;">
                    <?php echo WPA_Icons::get('check-circle'); ?> <?php esc_html_e( 'Real-Time Pinging protocol is ACTIVE. Search engines are notified automatically on every new post.', 'wp-academic-post-enhanced' ); ?>
                </p>
			</form>
		</div>
	</div>

	<script>
	jQuery(document).ready(function($) {
		$('#wpa-ping-sitemap').on('click', function(e) {
			e.preventDefault();
			const $btn = $(this);
			
			$btn.prop('disabled', true).addClass('loading');

			$.post(ajaxurl, {
				action: 'wpa_ping_academic_sitemap',
				nonce: '<?php echo wp_create_nonce("wpa_sitemap_nonce"); ?>'
			}, function(response) {
				$btn.prop('disabled', false).removeClass('loading');
				if (response.success) {
					alert(response.data);
				} else {
					alert(response.data);
				}
			});
		});
	});
	</script>
	<?php
}
