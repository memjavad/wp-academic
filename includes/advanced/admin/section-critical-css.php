<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

function wp_academic_post_enhanced_render_critical_css_section() {
    $options = get_option( 'wp_academic_post_enhanced_page_optimization_options', [] );
    ?>
    <p><?php esc_html_e( 'Inline critical CSS to improve First Contentful Paint (FCP).', 'wp-academic-post-enhanced' ); ?></p>
    
    <form method="post" action="options.php">
        <?php settings_fields( 'wp_academic_post_enhanced_critical_css_options' ); ?>
        <input type="hidden" name="wp_academic_post_enhanced_page_optimization_options[critical_css_enabled]" value="0">
        <label for="critical_css_enabled">
            <input type="checkbox" name="wp_academic_post_enhanced_page_optimization_options[critical_css_enabled]" id="critical_css_enabled" value="1" <?php checked( isset( $options['critical_css_enabled'] ) && $options['critical_css_enabled'] ); ?>>
            <?php esc_html_e( 'Enable Critical CSS', 'wp-academic-post-enhanced' ); ?>
        </label>
        
        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row"><label for="global_critical_css"><?php esc_html_e( 'Global Critical CSS', 'wp-academic-post-enhanced' ); ?></label></th>
                    <td>
                        <textarea name="wp_academic_post_enhanced_page_optimization_options[global_critical_css]" id="global_critical_css" rows="10" cols="50" class="large-text code"><?php echo isset( $options['global_critical_css'] ) ? esc_textarea( $options['global_critical_css'] ) : ''; ?></textarea>
                        <p class="description"><?php esc_html_e( 'Applied to all pages (e.g., header/footer styles).', 'wp-academic-post-enhanced' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="front_page_critical_css"><?php esc_html_e( 'Front Page Critical CSS', 'wp-academic-post-enhanced' ); ?></label></th>
                    <td>
                        <textarea name="wp_academic_post_enhanced_page_optimization_options[front_page_critical_css]" id="front_page_critical_css" rows="10" cols="50" class="large-text code"><?php echo isset( $options['front_page_critical_css'] ) ? esc_textarea( $options['front_page_critical_css'] ) : ''; ?></textarea>
                        <p class="description"><?php esc_html_e( 'Applied only to the front page.', 'wp-academic-post-enhanced' ); ?></p>
                    </td>
                </tr>
                <?php
                $post_types = get_post_types( [ 'public' => true, '_builtin' => false ], 'objects' );
                $post_types['post'] = get_post_type_object( 'post' );
                $post_types['page'] = get_post_type_object( 'page' );
                
                foreach ( $post_types as $slug => $pt ) {
                    ?>
                    <tr>
                        <th scope="row"><label for="critical_css_<?php echo esc_attr( $slug ); ?>"><?php printf( esc_html__( '%s Critical CSS', 'wp-academic-post-enhanced' ), $pt->labels->singular_name ); ?></label></th>
                        <td>
                            <textarea name="wp_academic_post_enhanced_page_optimization_options[critical_css][<?php echo esc_attr( $slug ); ?>]" id="critical_css_<?php echo esc_attr( $slug ); ?>" rows="10" cols="50" class="large-text code"><?php echo isset( $options['critical_css'][$slug] ) ? esc_textarea( $options['critical_css'][$slug] ) : ''; ?></textarea>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
        
        <?php submit_button(); ?>
    </form>
    <?php
}
