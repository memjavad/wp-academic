<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Add the SMTP submenu page.
 */
function wp_academic_post_enhanced_add_smtp_admin_menu() {
    $enabled = get_option( 'wp_academic_post_enhanced_smtp_enabled', false );
    if ( $enabled ) {
        add_submenu_page(
            'wp-academic-post-enhanced',
            __( 'SMTP Settings', 'wp-academic-post-enhanced' ),
            __( 'SMTP', 'wp-academic-post-enhanced' ),
            'manage_options',
            'wp-academic-post-enhanced-smtp',
            'wp_academic_post_enhanced_smtp_page'
        );
    }
}
add_action( 'admin_menu', 'wp_academic_post_enhanced_add_smtp_admin_menu' );

/**
 * Display the SMTP settings page.
 */
function wp_academic_post_enhanced_smtp_page() {
    ?>
    <div class="wrap wpa-settings-wrapper">
        <div class="wpa-header">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        </div>
        
        <div class="wpa-vertical-layout">
            <div class="wpa-vertical-nav">
                <ul>
                    <li><a href="#group-smtp-main" class="wpa-vtab active" data-target="group-smtp-main"><?php esc_html_e( 'Main Configuration', 'wp-academic-post-enhanced' ); ?></a></li>
                    <li><a href="#group-smtp-accounts" class="wpa-vtab" data-target="group-smtp-accounts"><?php esc_html_e( 'SMTP Accounts', 'wp-academic-post-enhanced' ); ?></a></li>
                </ul>
            </div>

            <div class="wpa-vertical-content">
                <form action="options.php" method="post">
                    <?php settings_fields( 'wp_academic_post_enhanced_smtp_options' ); ?>

                    <!-- Group 1: Main -->
                    <div id="group-smtp-main" class="wpa-group-content active">
                        <h2 class="nav-tab-wrapper">
                            <a href="#tab-smtp-gen" class="nav-tab nav-tab-active"><?php esc_html_e( 'General Settings', 'wp-academic-post-enhanced' ); ?></a>
                        </h2>
                        <div id="tab-smtp-gen" class="tab-content active">
                            <?php wpa_render_specific_section( 'wp_academic_post_enhanced_smtp', 'wp_academic_post_enhanced_smtp_section_general' ); ?>
                        </div>
                    </div>

                    <!-- Group 2: Accounts -->
                    <div id="group-smtp-accounts" class="wpa-group-content">
                        <h2 class="nav-tab-wrapper">
                            <a href="#account1" class="nav-tab nav-tab-active"><?php esc_html_e( 'Account 1', 'wp-academic-post-enhanced' ); ?></a>
                            <a href="#account2" class="nav-tab"><?php esc_html_e( 'Account 2', 'wp-academic-post-enhanced' ); ?></a>
                            <a href="#account3" class="nav-tab"><?php esc_html_e( 'Account 3', 'wp-academic-post-enhanced' ); ?></a>
                        </h2>
                        
                        <?php for ( $i = 1; $i <= 3; $i++ ) : ?>
                            <div id="account<?php echo $i; ?>" class="tab-content <?php echo ($i===1) ? 'active' : ''; ?>" style="<?php echo ($i===1) ? '' : 'display:none;'; ?>">
                                <?php wpa_render_specific_section( 'wp_academic_post_enhanced_smtp', 'wp_academic_post_enhanced_smtp_section_account_' . $i ); ?>
                            </div>
                        <?php endfor; ?>
                    </div>

                    <?php submit_button(); ?>
                </form>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Register SMTP settings.
 */
function wp_academic_post_enhanced_register_smtp_settings() {
    register_setting(
        'wp_academic_post_enhanced_smtp_options',
        'wp_academic_post_enhanced_smtp_enabled',
        [
            'type' => 'boolean',
            'sanitize_callback' => 'rest_sanitize_boolean',
            'default' => false,
        ]
    );

    register_setting(
        'wp_academic_post_enhanced_smtp_options',
        'wp_academic_post_enhanced_smtp_order_type',
        [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => 'sequential',
        ]
    );

    // Register settings for multiple accounts (e.g., 3 accounts)
    for ( $i = 1; $i <= 3; $i++ ) {
        register_setting(
            'wp_academic_post_enhanced_smtp_options',
            'wp_academic_post_enhanced_smtp_account_' . $i,
            [
                'type' => 'array',
                'sanitize_callback' => 'wp_academic_post_enhanced_sanitize_smtp_account',
                'default' => [
                    'host' => '',
                    'port' => 587,
                    'encryption' => 'tls',
                    'authentication' => true,
                    'username' => '',
                    'password' => '',
                    'from_email' => '',
                    'from_name' => '',
                ],
            ]
        );
    }

    // Section: General
    add_settings_section(
        'wp_academic_post_enhanced_smtp_section_general',
        __( 'General Settings', 'wp-academic-post-enhanced' ),
        'wp_academic_post_enhanced_smtp_section_general_callback',
        'wp_academic_post_enhanced_smtp'
    );

    add_settings_field(
        'wp_academic_post_enhanced_smtp_enabled_field',
        __( 'Enable SMTP', 'wp-academic-post-enhanced' ),
        'wp_academic_post_enhanced_smtp_enabled_field_callback',
        'wp_academic_post_enhanced_smtp',
        'wp_academic_post_enhanced_smtp_section_general'
    );

    add_settings_field(
        'wp_academic_post_enhanced_smtp_order_type_field',
        __( 'Sending Order', 'wp-academic-post-enhanced' ),
        'wp_academic_post_enhanced_smtp_order_type_field_callback',
        'wp_academic_post_enhanced_smtp',
        'wp_academic_post_enhanced_smtp_section_general'
    );

    // Add sections and fields for each account
    for ( $i = 1; $i <= 3; $i++ ) {
        add_settings_section(
            'wp_academic_post_enhanced_smtp_section_account_' . $i,
            sprintf( __( 'Account %d Configuration', 'wp-academic-post-enhanced' ), $i ),
            'wp_academic_post_enhanced_smtp_section_account_callback',
            'wp_academic_post_enhanced_smtp'
        );

        add_settings_field(
            'wp_academic_post_enhanced_smtp_account_' . $i . '_field',
            sprintf( __( 'Account %d Details', 'wp-academic-post-enhanced' ), $i ),
            'wp_academic_post_enhanced_smtp_account_field_callback',
            'wp_academic_post_enhanced_smtp',
            'wp_academic_post_enhanced_smtp_section_account_' . $i,
            [ 'account_number' => $i ]
        );
    }
}
add_action( 'admin_init', 'wp_academic_post_enhanced_register_smtp_settings' );

function wp_academic_post_enhanced_sanitize_smtp_account( $input ) {
    $sanitized = [];
    $sanitized['host'] = sanitize_text_field( $input['host'] );
    $sanitized['port'] = absint( $input['port'] );
    $sanitized['encryption'] = sanitize_text_field( $input['encryption'] );
    $sanitized['authentication'] = rest_sanitize_boolean( $input['authentication'] );
    $sanitized['username'] = sanitize_email( $input['username'] );
    $sanitized['password'] = sanitize_text_field( $input['password'] );
    $sanitized['from_email'] = sanitize_email( $input['from_email'] );
    $sanitized['from_name'] = sanitize_text_field( $input['from_name'] );
    return $sanitized;
}

function wp_academic_post_enhanced_smtp_section_general_callback() {
    echo '<p>' . esc_html__( 'Configure general SMTP settings.', 'wp-academic-post-enhanced' ) . '</p>';
}

function wp_academic_post_enhanced_smtp_section_account_callback() {
    echo '<p>' . esc_html__( 'Enter the details for this SMTP account.', 'wp-academic-post-enhanced' ) . '</p>';
}

function wp_academic_post_enhanced_smtp_enabled_field_callback() {
    $option = get_option( 'wp_academic_post_enhanced_smtp_enabled', false );
    echo '<input type="checkbox" name="wp_academic_post_enhanced_smtp_enabled" value="1" ' . checked( 1, $option, false ) . ' />';
}

function wp_academic_post_enhanced_smtp_order_type_field_callback() {
    $option = get_option( 'wp_academic_post_enhanced_smtp_order_type', 'sequential' );
    ?>
    <select name="wp_academic_post_enhanced_smtp_order_type">
        <option value="sequential" <?php selected( $option, 'sequential' ); ?>><?php esc_html_e( 'Sequential', 'wp-academic-post-enhanced' ); ?></option>
        <option value="random" <?php selected( $option, 'random' ); ?>><?php esc_html_e( 'Random', 'wp-academic-post-enhanced' ); ?></option>
    </select>
    <?php
}

function wp_academic_post_enhanced_smtp_account_field_callback( $args ) {
    $account_number = $args['account_number'];
    $option = get_option( 'wp_academic_post_enhanced_smtp_account_' . $account_number, [] );
    $defaults = [
        'host' => '',
        'port' => 587,
        'encryption' => 'tls',
        'authentication' => true,
        'username' => '',
        'password' => '',
        'from_email' => '',
        'from_name' => '',
    ];
    $account_settings = wp_parse_args( $option, $defaults );
    ?>
    <div style="padding: 10px 0;">
        <p>
            <label for="smtp_host_<?php echo $account_number; ?>" style="display:block; margin-bottom:5px; font-weight:600;"><?php esc_html_e( 'Host:', 'wp-academic-post-enhanced' ); ?></label>
            <input type="text" id="smtp_host_<?php echo $account_number; ?>" name="wp_academic_post_enhanced_smtp_account_<?php echo $account_number; ?>[host]" value="<?php echo esc_attr( $account_settings['host'] ); ?>" class="regular-text" />
        </p>
        <p>
            <label for="smtp_port_<?php echo $account_number; ?>" style="display:block; margin-bottom:5px; font-weight:600;"><?php esc_html_e( 'Port:', 'wp-academic-post-enhanced' ); ?></label>
            <input type="number" id="smtp_port_<?php echo $account_number; ?>" name="wp_academic_post_enhanced_smtp_account_<?php echo $account_number; ?>[port]" value="<?php echo esc_attr( $account_settings['port'] ); ?>" class="small-text" />
        </p>
        <p>
            <label for="smtp_encryption_<?php echo $account_number; ?>" style="display:block; margin-bottom:5px; font-weight:600;"><?php esc_html_e( 'Encryption:', 'wp-academic-post-enhanced' ); ?></label>
            <select id="smtp_encryption_<?php echo $account_number; ?>" name="wp_academic_post_enhanced_smtp_account_<?php echo $account_number; ?>[encryption]">
                <option value="none" <?php selected( $account_settings['encryption'], 'none' ); ?>><?php esc_html_e( 'None', 'wp-academic-post-enhanced' ); ?></option>
                <option value="ssl" <?php selected( $account_settings['encryption'], 'ssl' ); ?>><?php esc_html_e( 'SSL', 'wp-academic-post-enhanced' ); ?></option>
                <option value="tls" <?php selected( $account_settings['encryption'], 'tls' ); ?>><?php esc_html_e( 'TLS', 'wp-academic-post-enhanced' ); ?></option>
            </select>
        </p>
        <p>
            <label for="smtp_authentication_<?php echo $account_number; ?>" style="display:inline-block; margin-right:10px; font-weight:600;"><?php esc_html_e( 'Authentication:', 'wp-academic-post-enhanced' ); ?></label>
            <input type="checkbox" id="smtp_authentication_<?php echo $account_number; ?>" name="wp_academic_post_enhanced_smtp_account_<?php echo $account_number; ?>[authentication]" value="1" <?php checked( 1, $account_settings['authentication'] ); ?> />
        </p>
        <p>
            <label for="smtp_username_<?php echo $account_number; ?>" style="display:block; margin-bottom:5px; font-weight:600;"><?php esc_html_e( 'Username:', 'wp-academic-post-enhanced' ); ?></label>
            <input type="email" id="smtp_username_<?php echo $account_number; ?>" name="wp_academic_post_enhanced_smtp_account_<?php echo $account_number; ?>[username]" value="<?php echo esc_attr( $account_settings['username'] ); ?>" class="regular-text" />
        </p>
        <p>
            <label for="smtp_password_<?php echo $account_number; ?>" style="display:block; margin-bottom:5px; font-weight:600;"><?php esc_html_e( 'Password:', 'wp-academic-post-enhanced' ); ?></label>
            <input type="password" id="smtp_password_<?php echo $account_number; ?>" name="wp_academic_post_enhanced_smtp_account_<?php echo $account_number; ?>[password]" value="<?php echo esc_attr( $account_settings['password'] ); ?>" class="regular-text" />
        </p>
        <p>
            <label for="smtp_from_email_<?php echo $account_number; ?>" style="display:block; margin-bottom:5px; font-weight:600;"><?php esc_html_e( 'From Email:', 'wp-academic-post-enhanced' ); ?></label>
            <input type="email" id="smtp_from_email_<?php echo $account_number; ?>" name="wp_academic_post_enhanced_smtp_account_<?php echo $account_number; ?>[from_email]" value="<?php echo esc_attr( $account_settings['from_email'] ); ?>" class="regular-text" />
        </p>
        <p>
            <label for="smtp_from_name_<?php echo $account_number; ?>" style="display:block; margin-bottom:5px; font-weight:600;"><?php esc_html_e( 'From Name:', 'wp-academic-post-enhanced' ); ?></label>
            <input type="text" id="smtp_from_name_<?php echo $account_number; ?>" name="wp_academic_post_enhanced_smtp_account_<?php echo $account_number; ?>[from_name]" value="<?php echo esc_attr( $account_settings['from_name'] ); ?>" class="regular-text" />
        </p>
    </div>
    <?php
}