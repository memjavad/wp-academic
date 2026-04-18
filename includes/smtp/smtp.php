<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class WP_Academic_Post_Enhanced_SMTP {

    private static $instance;

    public static function get_instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        $enabled = get_option( 'wp_academic_post_enhanced_smtp_enabled', false );
        if ( $enabled ) {
            add_action( 'phpmailer_init', [ $this, 'configure_phpmailer' ] );
            add_filter( 'wp_mail_from', [ $this, 'set_from_email' ] );
            add_filter( 'wp_mail_from_name', [ $this, 'set_from_name' ] );
        }
    }

    private function get_active_smtp_account() {
        $order_type = get_option( 'wp_academic_post_enhanced_smtp_order_type', 'sequential' );
        $accounts = [];
        for ( $i = 1; $i <= 3; $i++ ) {
            $account = get_option( 'wp_academic_post_enhanced_smtp_account_' . $i );
            if ( ! empty( $account['host'] ) && ! empty( $account['username'] ) && ! empty( $account['password'] ) ) {
                $accounts[] = $account;
            }
        }

        if ( empty( $accounts ) ) {
            return false;
        }

        if ( 'random' === $order_type ) {
            return $accounts[ array_rand( $accounts ) ];
        } else {
            // Sequential logic: use a transient to store the last used account index
            $last_used_index = get_transient( 'wp_academic_post_enhanced_smtp_last_used_index' );
            if ( false === $last_used_index || $last_used_index >= count( $accounts ) - 1 ) {
                $next_index = 0;
            } else {
                $next_index = $last_used_index + 1;
            }
            set_transient( 'wp_academic_post_enhanced_smtp_last_used_index', $next_index, HOUR_IN_SECONDS ); // Store for 1 hour
            return $accounts[ $next_index ];
        }
    }

    public function configure_phpmailer( $phpmailer ) {
        $account = $this->get_active_smtp_account();

        if ( ! $account ) {
            return;
        }

        $phpmailer->isSMTP();
        $phpmailer->Host = $account['host'];
        $phpmailer->Port = $account['port'];
        $phpmailer->SMTPSecure = $account['encryption'];
        $phpmailer->SMTPAuth = $account['authentication'];
        $phpmailer->Username = $account['username'];
        $phpmailer->Password = $account['password'];
    }

    public function set_from_email( $email ) {
        $account = $this->get_active_smtp_account();
        return $account ? $account['from_email'] : $email;
    }

    public function set_from_name( $name ) {
        $account = $this->get_active_smtp_account();
        return $account ? $account['from_name'] : $name;
    }
}

WP_Academic_Post_Enhanced_SMTP::get_instance();
