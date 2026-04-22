<?php

use PHPUnit\Framework\TestCase;
use Brain\Monkey;
use Brain\Monkey\Functions;

class FeatureToggleTest extends TestCase {

    public static function setUpBeforeClass(): void {
        Monkey\setUp();

        Functions\when('__')->returnArg();
        Functions\when('sanitize_key')->returnArg();
        Functions\when('get_option')->justReturn(false);
        Functions\when('update_option')->justReturn(true);
        Functions\when('wp_verify_nonce')->justReturn(true);
        Functions\when('wp_die')->justReturn(true);

        require_once dirname(__DIR__) . '/wp-academic-post-enhanced.php';

        Monkey\tearDown();
    }

    protected function setUp(): void {
        parent::setUp();
        Monkey\setUp();

        $_POST = [];
    }

    protected function tearDown(): void {
        Monkey\tearDown();
        parent::tearDown();
    }

    public function test_wp_academic_post_enhanced_toggle_feature_permissions() {
        Functions\expect('current_user_can')
            ->once()
            ->with('manage_options')
            ->andReturn(false);

        Functions\expect('__')
            ->once()
            ->andReturnFirstArg();

        // Ensure we prevent wp_die from continuing execution, we can throw an exception
        Functions\expect('wp_die')
            ->once()
            ->with('You do not have sufficient permissions to access this page.')
            ->andThrow(new \Exception('wp_die called'));

        try {
            wp_academic_post_enhanced_toggle_feature();
            $this->fail('Expected wp_die to be called');
        } catch (\Exception $e) {
            $this->assertEquals('wp_die called', $e->getMessage());
        }
    }

    public function test_wp_academic_post_enhanced_toggle_feature_nonce_failure() {
        Functions\expect('current_user_can')
            ->once()
            ->with('manage_options')
            ->andReturn(true);

        $_POST['feature_key'] = 'test_feature';
        $_POST['enabled_option'] = 'test_option';
        $_POST['current_status'] = '0';
        // Mocked wp_verify_nonce expects this key to be set even if it's invalid
        $_POST['_wpnonce_wp_academic_post_enhanced_toggle_feature'] = 'invalid_nonce';

        Functions\expect('sanitize_key')->andReturnFirstArg();

        Functions\expect('wp_verify_nonce')
            ->once()
            ->with('invalid_nonce', 'wp_academic_post_enhanced_toggle_feature_test_feature')
            ->andReturn(false);

        Functions\expect('__')
            ->once()
            ->andReturnFirstArg();

        Functions\expect('wp_die')
            ->once()
            ->with('Nonce verification failed.')
            ->andThrow(new \Exception('wp_die called'));

        try {
            wp_academic_post_enhanced_toggle_feature();
            $this->fail('Expected wp_die to be called');
        } catch (\Exception $e) {
            $this->assertEquals('wp_die called', $e->getMessage());
        }
    }

    public function test_wp_academic_post_enhanced_toggle_feature_success() {
        Functions\expect('current_user_can')
            ->once()
            ->with('manage_options')
            ->andReturn(true);

        $_POST['feature_key'] = 'citation';
        $_POST['enabled_option'] = 'wpa_citation_enabled';
        $_POST['current_status'] = '0';
        $_POST['_wpnonce_wp_academic_post_enhanced_toggle_feature'] = 'valid_nonce';

        Functions\expect('sanitize_key')->andReturnFirstArg();

        Functions\expect('wp_verify_nonce')
            ->once()
            ->with('valid_nonce', 'wp_academic_post_enhanced_toggle_feature_citation')
            ->andReturn(true);

        Functions\expect('update_option')
            ->once()
            ->with('wpa_citation_enabled', true);

        Functions\expect('get_option')
            ->once()
            ->with('wpa_citation_settings', [])
            ->andReturn(['some' => 'setting']);

        Functions\expect('update_option')
            ->once()
            ->with('wpa_citation_settings', ['some' => 'setting', 'enabled' => true]);

        Functions\expect('admin_url')
            ->once()
            ->with('admin.php?page=wp-academic-post-enhanced&msg=settings-saved')
            ->andReturn('http://example.com/admin');

        Functions\expect('wp_safe_redirect')
            ->once()
            ->with('http://example.com/admin')
            ->andThrow(new \Exception('ExitPrevented'));

        try {
            wp_academic_post_enhanced_toggle_feature();
            $this->fail('Expected ExitPrevented exception');
        } catch (\Exception $e) {
            $this->assertEquals('ExitPrevented', $e->getMessage());
        }
    }
}
