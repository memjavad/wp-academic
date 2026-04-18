<?php
/**
 * Custom 404 Error Template - Full Screen & Elegant
 *
 * @package WP Academic Post Enhanced
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( function_exists( 'wpa_get_header' ) ) {
    wpa_get_header();
} else {
    get_header();
}

$options = get_option( 'wpa_homepage_settings', [] );

// 404 Settings
$title    = ! empty( $options['error_404_title'] ) ? $options['error_404_title'] : __( 'Page Not Found', 'wp-academic-post-enhanced' );
$desc     = ! empty( $options['error_404_desc'] ) ? $options['error_404_desc'] : __( 'Sorry, the page you are looking for does not exist or has been moved.', 'wp-academic-post-enhanced' );
$btn_text = ! empty( $options['error_404_btn_text'] ) ? $options['error_404_btn_text'] : __( 'Return Home', 'wp-academic-post-enhanced' );
$btn_url  = ! empty( $options['error_404_btn_url'] ) ? $options['error_404_btn_url'] : home_url( '/' );
$image    = ! empty( $options['error_404_image'] ) ? $options['error_404_image'] : '';

?>

<main class="wpa-404-full-screen" style="flex: 1; display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden; padding: 40px 20px;">
    
    <!-- Elegant Background Elements -->
    <div class="wpa-404-bg-glow" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; z-index: -1; opacity: 0.4;">
        <div style="position: absolute; top: -10%; left: -10%; width: 50%; height: 50%; background: radial-gradient(circle, var(--wpa-accent) 0%, transparent 70%); filter: blur(100px); opacity: 0.15;"></div>
        <div style="position: absolute; bottom: -10%; right: -10%; width: 50%; height: 50%; background: radial-gradient(circle, var(--wpa-success) 0%, transparent 70%); filter: blur(100px); opacity: 0.1;"></div>
    </div>

    <div class="wpa-container" style="max-width: 800px; text-align: center; position: relative; z-index: 1; margin-top: 20px;">
        
        <div class="wpa-404-card" style="background: rgba(255, 255, 255, 0.6); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.4); border-radius: 40px; padding: clamp(40px, 8vw, 80px); box-shadow: 0 40px 100px rgba(0,0,0,0.08);">
            
            <?php if ( $image ) : ?>
                <div class="wpa-404-visual" style="margin-bottom: 40px;">
                    <img src="<?php echo esc_url( $image ); ?>" alt="404" style="max-width: 280px; height: auto; border-radius: 24px; box-shadow: 0 20px 40px rgba(0,0,0,0.1);">
                </div>
            <?php else : ?>
                <div class="wpa-404-hero-text" style="font-size: clamp(6rem, 15vw, 10rem); font-weight: 900; line-height: 1; margin-bottom: 20px; background: linear-gradient(135deg, var(--wpa-text-main) 30%, var(--wpa-accent) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; letter-spacing: -0.05em;">
                    404
                </div>
            <?php endif; ?>

            <h1 class="wpa-404-title" style="font-size: clamp(2rem, 4vw, 3rem); font-weight: 800; color: var(--wpa-text-main); margin-bottom: 20px; line-height: 1.2; letter-spacing: -0.02em;">
                <?php echo esc_html( $title ); ?>
            </h1>

            <p class="wpa-404-description" style="font-size: 1.25rem; color: var(--wpa-text-muted); margin-bottom: 40px; line-height: 1.7; max-width: 500px; margin-inline: auto;">
                <?php echo esc_html( $desc ); ?>
            </p>

            <div class="wpa-404-actions">
                <a href="<?php echo esc_url( $btn_url ); ?>" class="wpa-btn wpa-btn-primary wpa-btn-lg" style="padding: 18px 45px; font-size: 1.15rem; box-shadow: 0 15px 30px rgba(5, 114, 236, 0.2);">
                    <?php echo esc_html( $btn_text ); ?>
                </a>
            </div>

        </div>

    </div>

</main>

<style>
/* Smooth float animation for the card */
@keyframes wpaFloat {
    0% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
    100% { transform: translateY(0px); }
}

.wpa-404-card {
    animation: wpaFloat 6s ease-in-out infinite;
}

/* Ensure header/footer don't cramp the style */
.wpa-custom-template {
    min-height: 100vh;
}
</style>

<?php 
if ( function_exists( 'wpa_get_footer' ) ) {
    wpa_get_footer();
} else {
    get_footer();
}
?>