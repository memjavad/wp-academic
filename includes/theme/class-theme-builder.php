<?php
/**
 * Homepage Builder Engine
 *
 * @package WP Academic Post Enhanced
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPA_Theme_Builder {

    private static $instance;
    private $h1_output = false;

    public static function get_instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        add_action( 'wp_ajax_wpa_builder_load_news', [ $this, 'ajax_load_news' ] );
        add_action( 'wp_ajax_nopriv_wpa_builder_load_news', [ $this, 'ajax_load_news' ] );
    }

    public function get_blocks() {
        // Fetch Categories for News Block
        $cats = get_categories( ['hide_empty' => false] );
        $cat_options = [ '-1' => __( 'All Categories', 'wp-academic-post-enhanced' ) ];
        foreach ( $cats as $c ) {
            $cat_options[ $c->term_id ] = $c->name;
        }

        $common_fields = [
            '_width' => [ 'type' => 'select', 'label' => 'Block Width', 'options' => ['100' => 'Full Width (100%)', '50' => 'Half Width (50%)'], 'default' => '100' ]
        ];

        $blocks = [
            'hero' => [
                'label' => __( 'Hero Section', 'wp-academic-post-enhanced' ),
                'icon'  => 'dashicons-cover-image',
                'fields' => [
                    'title'    => [ 'type' => 'text', 'label' => 'Headline', 'default' => 'عرب سايكلوجي: المنصة الأكاديمية الأولى لعلم النفس' ],
                    'subtitle' => [ 'type' => 'textarea', 'label' => 'Subheadline', 'default' => 'اكتشف أحدث الأبحاث، المقالات، والدورات التدريبية المعتمدة في علوم علم النفس والتربية.' ],
                    'bg_image' => [ 'type' => 'image', 'label' => 'Background Image URL' ],
                    'align'    => [ 'type' => 'select', 'label' => 'Alignment', 'options' => ['center' => 'Center', 'left' => 'Left', 'right' => 'Right'] ],
                    'cta_text' => [ 'type' => 'text', 'label' => 'CTA Button Text', 'default' => 'ابدأ التعلم الآن' ],
                    'cta_url'  => [ 'type' => 'text', 'label' => 'CTA Button URL', 'default' => '#' ],
                ]
            ],
            'slider' => [
                'label' => __( 'Hero Slider', 'wp-academic-post-enhanced' ),
                'icon'  => 'dashicons-images-alt2',
                'fields' => [
                    'title'  => [ 'type' => 'text', 'label' => 'Section Title (Optional)' ],
                    'source' => [ 'type' => 'select', 'label' => 'Content Source', 'options' => [ 'posts' => 'Recent Posts', 'news' => 'Field News', 'courses' => 'Courses', 'category' => 'Specific Category' ], 'default' => 'posts' ],
                    'cat'    => [ 'type' => 'select', 'label' => 'Category (if selected above)', 'options' => $cat_options ],
                    'count'  => [ 'type' => 'number', 'label' => 'Number of Slides', 'default' => 5 ],
                    'style'  => [ 'type' => 'select', 'label' => 'Design Style', 'options' => [
                        'classic' => 'Classic Slide',
                        'fade' => 'Modern Fade',
                        'hero-overlay' => 'Hero Overlay',
                        'split' => 'Split Screen',
                        'card-carousel' => 'Card Carousel',
                        'fullscreen' => 'Full Screen',
                        'magazine' => 'Magazine Grid',
                        'minimal' => 'Minimal Focus',
                        'clip' => 'Clip Path',
                        'gradient' => 'Gradient Mesh',
                        'focus' => 'Zoom Focus'
                    ]],
                    'height' => [ 'type' => 'number', 'label' => 'Height (px)', 'default' => 600 ],
                ]
            ],
            'stats' => [
                'label' => __( 'Key Statistics', 'wp-academic-post-enhanced' ),
                'icon'  => 'dashicons-chart-bar',
                'fields' => [
                    'stat_1_num' => [ 'type' => 'text', 'label' => 'Stat 1 Number', 'default' => '50+' ],
                    'stat_1_lbl' => [ 'type' => 'text', 'label' => 'Stat 1 Label', 'default' => 'Papers' ],
                    'stat_2_num' => [ 'type' => 'text', 'label' => 'Stat 2 Number', 'default' => '10k' ],
                    'stat_2_lbl' => [ 'type' => 'text', 'label' => 'Stat 2 Label', 'default' => 'Students' ],
                    'stat_3_num' => [ 'type' => 'text', 'label' => 'Stat 3 Number', 'default' => '12' ],
                    'stat_3_lbl' => [ 'type' => 'text', 'label' => 'Stat 3 Label', 'default' => 'Courses' ],
                    'stat_4_num' => [ 'type' => 'text', 'label' => 'Stat 4 Number', 'default' => '100%' ],
                    'stat_4_lbl' => [ 'type' => 'text', 'label' => 'Stat 4 Label', 'default' => 'Satisfaction' ],
                ]
            ],
            'news' => [
                'label' => __( 'News Grid', 'wp-academic-post-enhanced' ),
                'icon'  => 'dashicons-megaphone',
                'fields' => [
                    'title' => [ 'type' => 'text', 'label' => 'Section Title', 'default' => 'أحدث أبحاث ومقالات علم النفس' ],
                    'count' => [ 'type' => 'number', 'label' => 'Post Count', 'default' => 3 ],
                    'cat'   => [ 'type' => 'select', 'label' => 'Filter Category', 'options' => $cat_options ],
                    'style' => [ 'type' => 'select', 'label' => 'Display Style', 'options' => [
                        'grid' => 'Grid (Default)',
                        'list' => 'List View',
                        'compact' => 'Compact Grid',
                        'carousel' => 'Horizontal Carousel',
                        'masonry' => 'Masonry',
                        'highlight' => 'First Highlighted',
                        'magazine' => 'Magazine Layout',
                        'timeline' => 'Timeline',
                        'overlay' => 'Image Overlay',
                        'cards' => 'Clean Cards'
                    ], 'default' => 'grid' ],
                    'paginate' => [ 'type' => 'select', 'label' => 'Show Pagination', 'options' => [ '0' => 'None', '1' => 'Load More (AJAX)', '2' => 'Standard Paging' ], 'default' => '2' ],
                ]
            ],
            'courses' => [
                'label' => __( 'Course Grid', 'wp-academic-post-enhanced' ),
                'icon'  => 'dashicons-welcome-learn-more',
                'fields' => [
                    'title' => [ 'type' => 'text', 'label' => 'Section Title', 'default' => 'دورات علم النفس التدريبية' ],
                    'count' => [ 'type' => 'number', 'label' => 'Course Count', 'default' => 3 ],
                    'style' => [ 'type' => 'select', 'label' => 'Display Style', 'options' => [
                        'grid' => 'Grid (Default)',
                        'list' => 'List View',
                        'compact' => 'Compact Grid',
                        'carousel' => 'Horizontal Carousel',
                        'masonry' => 'Masonry',
                        'highlight' => 'First Highlighted',
                        'cards' => 'Clean Cards'
                    ], 'default' => 'grid' ],
                ]
            ],
            'glossary' => [
                'label' => __( 'Glossary Grid', 'wp-academic-post-enhanced' ),
                'icon'  => 'dashicons-book',
                'fields' => [
                    'title'   => [ 'type' => 'text', 'label' => 'Section Title', 'default' => 'قاموس مصطلحات علم النفس' ],
                    'count'   => [ 'type' => 'number', 'label' => 'Term Count', 'default' => 6 ],
                    'style'   => [ 'type' => 'select', 'label' => 'Display Style', 'options' => [
                        'images'  => 'Visual Cards (Images)',
                        'badges'  => 'Badges/Pills',
                        'list'    => 'Simple List'
                    ], 'default' => 'images' ],
                    'orderby' => [ 'type' => 'select', 'label' => 'Order By', 'options' => [
                        'rand'      => 'Random (Refresh)',
                        'rand_day'  => 'Random (Daily)',
                        'date'      => 'Newest First',
                        'title'     => 'Alphabetical'
                    ], 'default' => 'rand' ],
                    'columns' => [ 'type' => 'number', 'label' => 'Columns', 'default' => 3 ],
                ]
            ],
            'about' => [
                'label' => __( 'Content / About', 'wp-academic-post-enhanced' ),
                'icon'  => 'dashicons-text-page',
                'fields' => [
                    'title'   => [ 'type' => 'text', 'label' => 'Title' ],
                    'content' => [ 'type' => 'textarea', 'label' => 'Content' ],
                    'image'   => [ 'type' => 'image', 'label' => 'Side Image URL' ],
                    'layout'  => [ 'type' => 'select', 'label' => 'Image Position', 'options' => ['right' => 'Right', 'left' => 'Left'] ],
                ]
            ],
            'partners' => [
                'label' => __( 'Partners Strip', 'wp-academic-post-enhanced' ),
                'icon'  => 'dashicons-groups',
                'fields' => [
                    'title' => [ 'type' => 'text', 'label' => 'Title', 'default' => 'Trusted By' ],
                    'logos' => [ 'type' => 'textarea', 'label' => 'Logo URLs (One per line)' ],
                ]
            ],
            'team' => [
                'label' => __( 'Team / Faculty', 'wp-academic-post-enhanced' ),
                'icon'  => 'dashicons-groups',
                'fields' => [
                    'title' => [ 'type' => 'text', 'label' => 'Section Title', 'default' => 'Meet Our Faculty' ],
                    'member_1_name' => [ 'type' => 'text', 'label' => 'Member 1 Name' ],
                    'member_1_role' => [ 'type' => 'text', 'label' => 'Member 1 Role' ],
                    'member_1_img'  => [ 'type' => 'image', 'label' => 'Member 1 Image' ],
                    'member_2_name' => [ 'type' => 'text', 'label' => 'Member 2 Name' ],
                    'member_2_role' => [ 'type' => 'text', 'label' => 'Member 2 Role' ],
                    'member_2_img'  => [ 'type' => 'image', 'label' => 'Member 2 Image' ],
                    'member_3_name' => [ 'type' => 'text', 'label' => 'Member 3 Name' ],
                    'member_3_role' => [ 'type' => 'text', 'label' => 'Member 3 Role' ],
                    'member_3_img'  => [ 'type' => 'image', 'label' => 'Member 3 Image' ],
                    'member_4_name' => [ 'type' => 'text', 'label' => 'Member 4 Name' ],
                    'member_4_role' => [ 'type' => 'text', 'label' => 'Member 4 Role' ],
                    'member_4_img'  => [ 'type' => 'image', 'label' => 'Member 4 Image' ],
                ]
            ],
            'testimonials' => [
                'label' => __( 'Testimonials', 'wp-academic-post-enhanced' ),
                'icon'  => 'dashicons-format-quote',
                'fields' => [
                    'title' => [ 'type' => 'text', 'label' => 'Section Title', 'default' => 'Student Success' ],
                    'quote_1' => [ 'type' => 'textarea', 'label' => 'Quote 1' ],
                    'author_1' => [ 'type' => 'text', 'label' => 'Author 1' ],
                    'quote_2' => [ 'type' => 'textarea', 'label' => 'Quote 2' ],
                    'author_2' => [ 'type' => 'text', 'label' => 'Author 2' ],
                    'quote_3' => [ 'type' => 'textarea', 'label' => 'Quote 3' ],
                    'author_3' => [ 'type' => 'text', 'label' => 'Author 3' ],
                ]
            ],
            'newsletter' => [
                'label' => __( 'Newsletter', 'wp-academic-post-enhanced' ),
                'icon'  => 'dashicons-email',
                'fields' => [
                    'title'     => [ 'type' => 'text', 'label' => 'Title' ],
                    'desc'      => [ 'type' => 'textarea', 'label' => 'Description' ],
                    'shortcode' => [ 'type' => 'text', 'label' => 'Form Shortcode' ],
                ]
            ],
            'faq' => [
                'label' => __( 'FAQ / Accordion', 'wp-academic-post-enhanced' ),
                'icon'  => 'dashicons-list-view',
                'fields' => [
                    'title' => [ 'type' => 'text', 'label' => 'Section Title', 'default' => 'Frequently Asked Questions' ],
                    'q1'    => [ 'type' => 'text', 'label' => 'Question 1' ],
                    'a1'    => [ 'type' => 'textarea', 'label' => 'Answer 1' ],
                    'q2'    => [ 'type' => 'text', 'label' => 'Question 2' ],
                    'a2'    => [ 'type' => 'textarea', 'label' => 'Answer 2' ],
                    'q3'    => [ 'type' => 'text', 'label' => 'Question 3' ],
                    'a3'    => [ 'type' => 'textarea', 'label' => 'Answer 3' ],
                    'q4'    => [ 'type' => 'text', 'label' => 'Question 4' ],
                    'a4'    => [ 'type' => 'textarea', 'label' => 'Answer 4' ],
                ]
            ],
            'html' => [
                'label' => __( 'Custom HTML/Shortcode', 'wp-academic-post-enhanced' ),
                'icon'  => 'dashicons-editor-code',
                'fields' => [
                    'content' => [ 'type' => 'textarea', 'label' => 'HTML or Shortcode' ],
                ]
            ]
        ];

        // Merge Common Fields
        foreach ( $blocks as $type => &$def ) {
            $def['fields'] = array_merge( $common_fields, $def['fields'] );
        }

        return $blocks;
    }

    public function render_builder_ui() {
        $layout = get_option( 'wpa_homepage_layout', [] );
        
        // Fix: Ensure layout is array if stored as JSON string
        if ( is_string( $layout ) ) {
            $decoded = json_decode( $layout, true );
            $layout = is_array( $decoded ) ? $decoded : [];
        } elseif ( ! is_array( $layout ) ) {
            $layout = [];
        }
        
        // Auto-Populate Demo Data if Empty
        if ( empty( $layout ) ) {
            $layout = [
                [ 'id' => 'hero_demo', 'type' => 'hero', 'data' => [ 
                    'title' => 'Advancing Knowledge', 
                    'subtitle' => 'Join our community of researchers and learners.', 
                    'cta_text' => 'Get Started', 
                    'cta_url' => '#',
                    'align' => 'center'
                ]],
                [ 'id' => 'stats_demo', 'type' => 'stats', 'data' => [
                    'stat_1_num' => '50+', 'stat_1_lbl' => 'Papers',
                    'stat_2_num' => '10k', 'stat_2_lbl' => 'Students',
                    'stat_3_num' => '12', 'stat_3_lbl' => 'Courses',
                    'stat_4_num' => '100%', 'stat_4_lbl' => 'Impact'
                ]],
                [ 'id' => 'news_demo', 'type' => 'news', 'data' => [ 'title' => 'Latest Research', 'count' => 3 ] ],
                [ 'id' => 'team_demo', 'type' => 'team', 'data' => [
                    'title' => 'Meet Our Faculty',
                    'member_1_name' => 'Dr. Sarah Smith', 'member_1_role' => 'Dean of Science',
                    'member_2_name' => 'Prof. John Doe', 'member_2_role' => 'Lead Researcher',
                    'member_3_name' => 'Dr. Emily White', 'member_3_role' => 'Senior Lecturer'
                ]],
                [ 'id' => 'testimonials_demo', 'type' => 'testimonials', 'data' => [
                    'title' => 'Student Success',
                    'quote_1' => 'This program changed my life. The research opportunities are unmatched.', 'author_1' => 'Jane A.',
                    'quote_2' => 'Incredible faculty and support system.', 'author_2' => 'Mark B.'
                ]],
                [ 'id' => 'newsletter_demo', 'type' => 'newsletter', 'data' => [ 
                    'title' => 'Stay Updated', 
                    'desc' => 'Subscribe to our newsletter for the latest updates.',
                    'shortcode' => '' 
                ]]
            ];
        }
        
        $blocks = $this->get_blocks();
        ?>
        <div id="wpa-builder-app">
            <div class="wpa-builder-sidebar">
                <h3><?php esc_html_e( 'Available Blocks', 'wp-academic-post-enhanced' ); ?></h3>
                <div class="wpa-block-library">
                    <?php foreach ( $blocks as $type => $def ) : ?>
                        <div class="wpa-lib-item" data-type="<?php echo esc_attr( $type ); ?>">
                            <span class="dashicons <?php echo esc_attr( $def['icon'] ); ?>"></span>
                            <span class="wpa-lib-label"><?php echo esc_html( $def['label'] ); ?></span>
                            <button type="button" class="button wpa-add-block-btn"><?php esc_html_e( 'Add', 'wp-academic-post-enhanced' ); ?></button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="wpa-builder-canvas">
                <div class="wpa-canvas-header">
                    <h3><?php esc_html_e( 'Homepage Layout', 'wp-academic-post-enhanced' ); ?></h3>
                    <div class="wpa-canvas-actions">
                        <button type="button" class="button wpa-expand-all"><?php esc_html_e( 'Expand All', 'wp-academic-post-enhanced' ); ?></button>
                        <button type="button" class="button wpa-collapse-all"><?php esc_html_e( 'Collapse All', 'wp-academic-post-enhanced' ); ?></button>
                        <button type="button" class="button wpa-clear-layout" style="color: #b32d2e; border-color: #b32d2e;"><?php esc_html_e( 'Clear Layout', 'wp-academic-post-enhanced' ); ?></button>
                    </div>
                </div>
                <p class="description"><?php esc_html_e( 'Drag and drop to reorder. Click arrow to edit.', 'wp-academic-post-enhanced' ); ?></p>
                
                <div id="wpa-layout-container">
                    <!-- Blocks injected via JS -->
                </div>
                
                <input type="hidden" name="wpa_homepage_layout" id="wpa_homepage_layout_input" value="<?php echo esc_attr( json_encode( $layout ) ); ?>">
            </div>
        </div>

        <!-- Templates for JS -->
        <script>
        var wpaBlockDefs = <?php echo json_encode( $blocks ); ?>;
        var wpaInitialLayout = <?php echo json_encode( $layout ); ?>;
        </script>
        <?php
    }

    public function render_layout() {
        $layout = get_option( 'wpa_homepage_layout', [] );
        if ( is_string( $layout ) ) {
            $decoded = json_decode( $layout, true );
            $layout = is_array( $decoded ) ? $decoded : [];
        } elseif ( ! is_array( $layout ) ) {
            $layout = [];
        }
        
        // Auto-Populate Complete Demo Layout if Empty
        if ( empty( $layout ) ) {
            $layout = [
                [ 'id' => 'hero_demo', 'type' => 'hero', 'data' => [ 
                    'title' => 'Advancing Knowledge for a Better Future', 
                    'subtitle' => 'Join our world-class community of researchers, educators, and students in shaping the next generation of innovation.', 
                    'cta_text' => 'Explore Programs', 
                    'cta_url' => '#',
                    'align' => 'center',
                    'bg_image' => 'https://images.unsplash.com/photo-1541339907198-e08756dedf3f?ixlib=rb-1.2.1&auto=format&fit=crop&w=1920&q=80' // Academic Library
                ]],
                [ 'id' => 'stats_demo', 'type' => 'stats', 'data' => [
                    'stat_1_num' => '150+', 'stat_1_lbl' => 'Published Papers',
                    'stat_2_num' => '12k', 'stat_2_lbl' => 'Active Students',
                    'stat_3_num' => '45', 'stat_3_lbl' => 'Research Labs',
                    'stat_4_num' => '98%', 'stat_4_lbl' => 'Graduate Success'
                ]],
                [ 'id' => 'partners_demo', 'type' => 'partners', 'data' => [
                    'title' => 'Trusted By Leading Institutions',
                    'logos' => "https://upload.wikimedia.org/wikipedia/commons/2/2f/Google_2015_logo.svg\nhttps://upload.wikimedia.org/wikipedia/commons/9/91/Octicons-mark-github.svg\nhttps://upload.wikimedia.org/wikipedia/commons/0/05/Facebook_Logo_%282019%29.png\nhttps://upload.wikimedia.org/wikipedia/commons/e/e9/Linkedin_icon.svg"
                ]],
                [ 'id' => 'about_demo', 'type' => 'about', 'data' => [
                    'title' => 'Our Mission & Vision',
                    'content' => 'We are dedicated to fostering an environment of academic excellence and integrity. Our institution believes in the power of research to solve real-world problems. Through collaborative efforts and cutting-edge facilities, we empower our students to become leaders in their fields.',
                    'image' => 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80', // Classroom
                    'layout' => 'right'
                ]],
                [ 'id' => 'team_demo', 'type' => 'team', 'data' => [
                    'title' => 'Meet Our Distinguished Faculty',
                    'member_1_name' => 'Dr. Eleanor Vance', 'member_1_role' => 'Dean of Sciences', 'member_1_img' => 'https://randomuser.me/api/portraits/women/44.jpg',
                    'member_2_name' => 'Prof. Arthur Black', 'member_2_role' => 'Head of Physics', 'member_2_img' => 'https://randomuser.me/api/portraits/men/32.jpg',
                    'member_3_name' => 'Dr. Sarah Connor', 'member_3_role' => 'Senior Lecturer', 'member_3_img' => 'https://randomuser.me/api/portraits/women/68.jpg',
                    'member_4_name' => 'James Miller', 'member_4_role' => 'Research Fellow', 'member_4_img' => 'https://randomuser.me/api/portraits/men/86.jpg'
                ]],
                [ 'id' => 'testimonials_demo', 'type' => 'testimonials', 'data' => [
                    'title' => 'Student Voices',
                    'quote_1' => 'The curriculum is challenging yet incredibly rewarding. I found my passion for research here.', 'author_1' => 'Alice Johnson, Class of 2024',
                    'quote_2' => 'Support from faculty has been instrumental in my career development.', 'author_2' => 'David Lee, PhD Candidate',
                    'quote_3' => 'A vibrant community that truly values diversity and innovation.', 'author_3' => 'Maria Garcia, Alumni'
                ]],
                [ 'id' => 'news_demo', 'type' => 'news', 'data' => [ 'title' => 'Latest Research News', 'count' => 3 ] ],
                [ 'id' => 'courses_demo', 'type' => 'courses', 'data' => [ 'title' => 'Featured Courses', 'count' => 3 ] ],
                [ 'id' => 'newsletter_demo', 'type' => 'newsletter', 'data' => [ 
                    'title' => 'Stay Connected', 
                    'desc' => 'Subscribe to our newsletter to receive the latest research updates and course announcements.',
                    'shortcode' => '' 
                ]]
            ];
        }

        if ( empty( $layout ) || ! is_array( $layout ) ) return;

        $in_row = false;

        foreach ( $layout as $index => $block ) {
            $type = $block['type'];
            $data = isset( $block['data'] ) ? $block['data'] : [];
            $width = isset( $data['_width'] ) ? $data['_width'] : '100';
            $method = 'render_' . $type;
            
            if ( method_exists( $this, $method ) ) {
                
                if ( $width == '50' ) {
                    if ( ! $in_row ) {
                        echo '<div class="wpa-layout-row wpa-container">';
                        $in_row = true;
                    }
                    echo '<div class="wpa-layout-col">';
                } else {
                    if ( $in_row ) {
                        echo '</div>'; // close row
                        $in_row = false;
                    }
                }

                // Animation delay based on index
                $delay_class = 'wpa-delay-' . ( ($index % 3) * 100 );
                if ( $index === 0 ) $delay_class = ''; // No delay for first item (Hero) 
                
                $this->$method( $data, $delay_class );

                if ( $width == '50' ) {
                    echo '</div>'; // close col
                }
            }
        }

        if ( $in_row ) {
            echo '</div>'; // close last row if open
        }
        
        $this->render_scripts();
    }
    
    private function render_scripts() {
        ?>
        <script>
        var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
        var wpa_nonce = '<?php echo wp_create_nonce('wpa_theme_nonce'); ?>';
        document.addEventListener("DOMContentLoaded", function() {
            // Scroll Down
            const scrollBtns = document.querySelectorAll('.wpa-scroll-down');
            if (scrollBtns.length > 0) {
                scrollBtns.forEach(btn => {
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        const target = document.querySelector('#wpa-scroll-target');
                        if (target) target.scrollIntoView({ behavior: 'smooth' });
                    });
                });
            }
            
            // Count Up
            const stats = document.querySelectorAll('.wpa-stat-number');
            if (stats.length > 0 && 'IntersectionObserver' in window) {
                const observer = new IntersectionObserver((entries, observer) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const el = entry.target;
                            const target = parseInt(el.getAttribute('data-count'));
                            const suffix = el.getAttribute('data-suffix');
                            if (!isNaN(target)) {
                                let start = 0;
                                const duration = 2000;
                                const step = 20;
                                const increment = target / (duration / step);
                                const timer = setInterval(() => {
                                    start += increment;
                                    if (start >= target) {
                                        el.textContent = target + suffix;
                                        clearInterval(timer);
                                    } else {
                                        el.textContent = Math.floor(start) + suffix;
                                    }
                                }, step);
                            }
                            observer.unobserve(el);
                        }
                    });
                }, { threshold: 0.5 });
                stats.forEach(stat => observer.observe(stat));
            }

            // AJAX Pagination & Load More
            document.body.addEventListener('click', function(e) {
                const target = e.target.closest('.wpa-ajax-pagination-btn');
                if (!target) return;
                
                e.preventDefault();
                
                const section = target.closest('.wpa-section-news');
                const container = section.querySelector('.wpa-news-container');
                const paginationContainer = section.querySelector('.wpa-pagination-container');
                
                const paged = parseInt(target.getAttribute('data-paged'));
                const count = section.getAttribute('data-count');
                const cat = section.getAttribute('data-cat');
                const style = section.getAttribute('data-style');
                const paginateType = section.getAttribute('data-paginate');

                // Visual Feedback
                if (paginateType === '1') { // Load More
                    target.textContent = 'Loading...';
                    target.disabled = true;
                } else { // Standard Paging
                    container.style.opacity = '0.5';
                }

                const formData = new FormData();
                formData.append('action', 'wpa_builder_load_news');
                formData.append('nonce', wpa_nonce);
                formData.append('paged', paged);
                formData.append('count', count);
                formData.append('cat', cat);
                formData.append('style', style);
                formData.append('paginate', paginateType);

                fetch(ajaxurl || '/wp-admin/admin-ajax.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        if (paginateType === '1') {
                            // Append for Load More
                            container.insertAdjacentHTML('beforeend', data.data.html);
                            // Update or Remove Button
                            if (data.data.next_page) {
                                target.setAttribute('data-paged', data.data.next_page);
                                target.textContent = 'Load More';
                                target.disabled = false;
                            } else {
                                target.parentElement.remove();
                            }
                        } else {
                            // Replace for Standard Pagination
                            container.innerHTML = data.data.html;
                            container.style.opacity = '1';
                            // Update Pagination HTML
                            if (paginationContainer) {
                                paginationContainer.innerHTML = data.data.pagination;
                            }
                            // Scroll to top of section
                            section.scrollIntoView({ behavior: 'smooth' });
                        }
                    }
                })
                .catch(err => {
                    console.error('Pagination Error:', err);
                    if (paginateType === '1') {
                        target.textContent = 'Error';
                    } else {
                        container.style.opacity = '1';
                    }
                });
            });
        });
        </script>
        <?php
    }

    public function ajax_load_news() {
        check_ajax_referer( 'wpa_theme_nonce', 'nonce' );

        $paged = isset($_POST['paged']) ? intval($_POST['paged']) : 1;
        $count = isset($_POST['count']) ? intval($_POST['count']) : 3;
        $cat = isset($_POST['cat']) ? sanitize_text_field( $_POST['cat'] ) : '-1';
        $style = isset($_POST['style']) ? sanitize_key( $_POST['style'] ) : 'grid';
        $paginate_type = isset($_POST['paginate']) ? sanitize_text_field( $_POST['paginate'] ) : '2';

        $args = [ 
            'post_type' => 'wpa_news', 
            'posts_per_page' => $count, 
            'post_status' => 'publish',
            'paged' => $paged
        ];
        if ( $cat !== '-1' ) {
            $args['tax_query'] = [
                [
                    'taxonomy' => 'category',
                    'field'    => 'term_id',
                    'terms'    => $cat,
                ],
            ];
        }

        $q = new WP_Query( $args );
        $html = '';
        $pagination_html = '';
        
        if ( $q->have_posts() ) {
            ob_start();
            while ( $q->have_posts() ) {
                $q->the_post();
                $this->render_news_item($style);
            }
            $html = ob_get_clean();

            // Generate Pagination HTML if needed
            if ( $paginate_type === '2' && $q->max_num_pages > 1 ) {
                $pagination_html = $this->get_ajax_pagination_html($paged, $q->max_num_pages);
            }
        }
        
        wp_send_json_success([
            'html' => $html,
            'pagination' => $pagination_html,
            'next_page' => ($paged < $q->max_num_pages) ? $paged + 1 : false
        ]);
    }

    private function get_ajax_pagination_html($current, $total) {
        if ($total <= 1) return '';
        
        $html = '<div class="wpa-pagination"><ul>';
        
        // Prev
        if ($current > 1) {
            $html .= '<li><a href="#" class="wpa-ajax-pagination-btn prev" data-paged="' . ($current - 1) . '">&larr;</a></li>';
        }

        // Pages (Simplified Logic for now, can be expanded)
        // Show all if <= 5, otherwise show range
        $start = max(1, $current - 2);
        $end = min($total, $current + 2);
        
        if ($start > 1) $html .= '<li><span class="dots">...</span></li>';

        for ($i = $start; $i <= $end; $i++) {
            if ($i === $current) {
                $html .= '<li><span class="current">' . $i . '</span></li>';
            } else {
                $html .= '<li><a href="#" class="wpa-ajax-pagination-btn" data-paged="' . $i . '">' . $i . '</a></li>';
            }
        }

        if ($end < $total) $html .= '<li><span class="dots">...</span></li>';

        // Next
        if ($current < $total) {
            $html .= '<li><a href="#" class="wpa-ajax-pagination-btn next" data-paged="' . ($current + 1) . '">&rarr;</a></li>';
        }
        
        $html .= '</ul></div>';
        return $html;
    }

    private function render_news_item($style) {
        ?>
        <article class="wpa-card wpa-news-card">
            <div class="wpa-card-img">
                <a href="<?php the_permalink(); ?>">
                    <?php has_post_thumbnail() ? the_post_thumbnail('medium_large', ['alt' => get_the_title()]) : echo_placeholder_icon('format-image'); ?>
                </a>
            </div>
            <div class="wpa-card-body">
                <h3 class="wpa-card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                <div class="wpa-card-tags" style="margin-top: 12px; display: flex; gap: 8px; flex-wrap: wrap;">
                    <?php 
                    $categories = get_the_category();
                    if ( ! empty( $categories ) ) {
                        echo '<span class="wpa-tag wpa-tag-primary">' . esc_html( $categories[0]->name ) . '</span>';
                    }
                    echo '<span class="wpa-tag wpa-tag-outline">' . get_the_date() . '</span>';
                    echo '<span class="wpa-tag wpa-tag-outline">' . get_the_author() . '</span>';
                    ?>
                </div>
            </div>
        </article>
        <?php
    }

    // --- Render Methods ---

    private function render_slider( $data, $delay ) {
        $source = ! empty($data['source']) ? $data['source'] : 'posts';
        $count = ! empty($data['count']) ? intval($data['count']) : 5;
        $style = ! empty($data['style']) ? $data['style'] : 'classic';
        $height = ! empty($data['height']) ? intval($data['height']) : 600;
        
        $args = [ 'posts_per_page' => $count, 'post_status' => 'publish' ];
        
        if ( $source === 'news' ) {
            $args['post_type'] = 'wpa_news';
        } elseif ( $source === 'courses' ) {
            $args['post_type'] = 'wpa_course';
        } elseif ( $source === 'category' ) {
            $args['post_type'] = 'post';
            if ( ! empty($data['cat']) && $data['cat'] !== '-1' ) {
                $args['cat'] = $data['cat'];
            }
        } else {
            $args['post_type'] = 'post';
        }
        
        // ⚡ Bolt: Prevent expensive SQL_CALC_FOUND_ROWS query since pagination is not needed.
        $args['no_found_rows'] = true;

        $q = new WP_Query( $args );
        if ( ! $q->have_posts() ) return; 
        
        echo '<!-- WPA Slider Debug: Found ' . $q->post_count . ' posts. Style: ' . esc_html($style) . ' -->';
        
        ?>
        <section class="wpa-slider-section wpa-fade-up <?php echo $delay; ?>" style="position:relative; overflow:hidden; padding: 60px 0;">
            <div class="wpa-container">
                <?php if ( ! empty($data['title']) ) : 
                    $tag = ! $this->h1_output ? 'h1' : 'h2';
                    if ( ! $this->h1_output ) $this->h1_output = true;
                ?>
                    <<?php echo $tag; ?> class="wpa-section-title"><?php echo esc_html( $data['title'] ); ?></<?php echo $tag; ?>>
                <?php endif; ?>
                
                <div class="wpa-slider-container style-<?php echo esc_attr($style); ?>" data-style="<?php echo esc_attr($style); ?>" style="height:<?php echo $height; ?>px; border-radius: 20px; box-shadow: var(--wpa-shadow-hover);">
                    <div class="wpa-slider-track">
                    <?php 
                    $slide_index = 0;
                    while ( $q->have_posts() ) : $q->the_post(); 
                        $thumb_id = get_post_thumbnail_id( get_the_ID() );
                        $active_class = ( $slide_index === 0 ) ? ' active' : '';
                    ?>
                        <div class="wpa-slide<?php echo $active_class; ?>">
                            <div class="wpa-slide-bg">
                                <?php 
                                if ( $thumb_id ) {
                                    echo wp_get_attachment_image( $thumb_id, 'full', false, ['class' => 'wpa-slide-img', 'loading' => 'eager', 'style' => 'width:100%; height:100%; object-fit:cover; position:absolute; top:0; left:0;'] );
                                } else {
                                    echo_placeholder_icon('format-image');
                                }
                                ?>
                            </div>
                            <div class="wpa-slide-overlay"></div>
                            <div class="wpa-slide-content">
                                <div class="wpa-container">
                                    <h2 class="wpa-slide-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                                    <div class="wpa-slide-excerpt"><?php echo wp_trim_words( get_the_excerpt(), 20 ); ?></div>
                                    <a href="<?php the_permalink(); ?>" class="wpa-btn wpa-btn-primary wpa-slide-btn"><?php esc_html_e( 'Read More', 'wp-academic-post-enhanced' ); ?></a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; wp_reset_postdata(); ?>
                </div>
                
                <div class="wpa-slider-nav">
                    <button class="wpa-slider-prev"><?php echo WPA_Icons::get('arrow-left-alt2'); ?></button>
                    <button class="wpa-slider-next"><?php echo WPA_Icons::get('arrow-right-alt2'); ?></button>
                </div>
                <div class="wpa-slider-dots"></div>
                
                <a href="#wpa-scroll-target" class="wpa-scroll-down wpa-slider-scroll-down" aria-label="Scroll Down">
                    <?php echo WPA_Icons::get('arrow-down-alt2'); ?>
                </a>
            </div>
        </section>
        <div id="wpa-scroll-target"></div>
        <?php
    }

    private function render_hero( $data, $delay ) {
        $bg = ! empty($data['bg_image']) ? esc_url($data['bg_image']) : '';
        $style = $bg ? "background-image: linear-gradient(rgba(15, 23, 42, 0.8), rgba(15, 23, 42, 0.8)), url('{$bg}'); background-size: cover; background-position: center;" : "background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);";
        
        $align = ! empty($data['align']) ? $data['align'] : 'center';
        $style .= " text-align: {$align};";
        if ( $align === 'left' ) $style .= " align-items: flex-start;";
        elseif ( $align === 'right' ) $style .= " align-items: flex-end;";
        else $style .= " align-items: center;";
        ?>
        <section class="wpa-hero wpa-fade-up <?php echo $delay; ?>" style="<?php echo $style; ?>">
            <div class="wpa-container">
                <?php 
                $tag = ! $this->h1_output ? 'h1' : 'h2';
                if ( ! $this->h1_output ) $this->h1_output = true;
                ?>
                <<?php echo $tag; ?>><?php echo esc_html( $data['title'] ); ?></<?php echo $tag; ?>>
                <?php if ( ! empty($data['subtitle']) ) echo '<p>' . wp_kses_post( $data['subtitle'] ) . '</p>'; ?>
                <?php if ( ! empty($data['cta_text']) && ! empty($data['cta_url']) ) : ?>
                    <div style="margin-top: 30px;">
                        <a href="<?php echo esc_url( $data['cta_url'] ); ?>" class="wpa-hero-cta"><?php echo esc_html( $data['cta_text'] ); ?></a>
                    </div>
                <?php endif; ?>
                
                <a href="#wpa-scroll-target" class="wpa-scroll-down" aria-label="Scroll Down">
                    <?php echo WPA_Icons::get('arrow-down-alt2'); ?>
                </a>
            </div>
        </section>
        <div id="wpa-scroll-target"></div>
        <?php
    }

    private function render_stats( $data, $delay ) {
        ?>
        <div class="wpa-stats-bar wpa-fade-up <?php echo $delay; ?>">
            <div class="wpa-container">
                <div class="wpa-stats-grid">
                    <?php for( $i=1; $i<=4; $i++ ) : 
                        if ( ! empty( $data["stat_{$i}_num"] ) ) : 
                            $num = $data["stat_{$i}_num"];
                            $raw = filter_var($num, FILTER_SANITIZE_NUMBER_INT);
                            $suffix = str_replace($raw, '', $num);
                        ?>
                        <div class="wpa-stat-item">
                            <span class="wpa-stat-number" data-count="<?php echo esc_attr($raw); ?>" data-suffix="<?php echo esc_attr($suffix); ?>"><?php echo esc_html( $num ); ?></span>
                            <span class="wpa-stat-label"><?php echo esc_html( $data["stat_{$i}_lbl"] ); ?></span>
                        </div>
                    <?php endif; endfor; ?>
                </div>
            </div>
        </div>
        <?php
    }

    private function render_news( $data, $delay ) {
        $count = ! empty($data['count']) ? intval($data['count']) : 3;
        $style = ! empty($data['style']) ? $data['style'] : 'grid';
        $paginate = isset($data['paginate']) ? $data['paginate'] : '2'; // Default to 2 if not set
        $cat = ! empty($data['cat']) ? $data['cat'] : '-1';
        
        // Initial Paged
        $paged = 1;

        $args = [ 
            'post_type' => 'wpa_news', 
            'posts_per_page' => $count, 
            'post_status' => 'publish',
            'paged' => $paged
        ];
        if ( $cat !== '-1' ) {
            $args['tax_query'] = [
                [
                    'taxonomy' => 'category',
                    'field'    => 'term_id',
                    'terms'    => $cat,
                ],
            ];
        }
        
        $q = new WP_Query( $args );
        if ( ! $q->have_posts() ) return;

        $total_posts = $q->found_posts;
        $block_id = 'wpa-news-' . uniqid();
        ?>
        <section id="<?php echo esc_attr($block_id); ?>" class="wpa-section-news wpa-fade-up <?php echo $delay; ?>" 
                 data-count="<?php echo esc_attr($count); ?>" 
                 data-style="<?php echo esc_attr($style); ?>" 
                 data-cat="<?php echo esc_attr($cat); ?>"
                 data-paginate="<?php echo esc_attr($paginate); ?>">
            <div class="wpa-container">
                <div class="wpa-section-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 60px; flex-wrap: wrap; gap: 20px;">
                    <?php 
                    $tag = ! $this->h1_output ? 'h1' : 'h2';
                    if ( ! $this->h1_output ) $this->h1_output = true;
                    ?>
                    <<?php echo $tag; ?> class="wpa-section-title" style="margin-bottom:0; text-align: left; width: auto; display: inline-block;"><?php echo esc_html( $data['title'] ); ?></<?php echo $tag; ?>>
                    <span class="wpa-news-total-badge" style="background: var(--wpa-bg-light); padding: 8px 16px; border-radius: 30px; font-weight: 700; color: var(--wpa-accent); font-size: 0.9rem; border: 1px solid var(--wpa-border-color);">
                        <?php echo esc_html( $total_posts ); ?> <?php esc_html_e( 'Total News', 'wp-academic-post-enhanced' ); ?>
                    </span>
                </div>

                <div class="wpa-grid news-style-<?php echo esc_attr($style); ?> wpa-news-container">
                    <?php while ( $q->have_posts() ) : $q->the_post(); 
                        $this->render_news_item($style);
                    endwhile; ?>
                </div>

                <div class="wpa-pagination-container">
                    <?php 
                    if ( $paginate === '1' && $q->max_num_pages > 1 ) {
                        echo '<div class="wpa-load-more-wrap" style="margin-top: 40px; text-align: center;">
                            <button class="wpa-btn wpa-btn-primary wpa-ajax-pagination-btn" data-paged="2">' . esc_html__( 'Load More', 'wp-academic-post-enhanced' ) . '</button>
                        </div>';
                    }
                    
                    if ( $paginate === '2' && $q->max_num_pages > 1 ) {
                        echo $this->get_ajax_pagination_html(1, $q->max_num_pages);
                    } 
                    ?>
                </div>
                <?php wp_reset_postdata(); ?>
            </div>
        </section>
        <?php
    }

    private function render_courses( $data, $delay ) {
        $count = ! empty($data['count']) ? $data['count'] : 3;
        $style = ! empty($data['style']) ? $data['style'] : 'grid';
        // ⚡ Bolt: Prevent expensive SQL_CALC_FOUND_ROWS query since pagination is not needed.
        $q = new WP_Query([ 'post_type' => 'wpa_course', 'posts_per_page' => $count, 'post_status' => 'publish', 'no_found_rows' => true ]);
        if ( ! $q->have_posts() ) return;
        ?>
        <section class="wpa-section-courses wpa-fade-up <?php echo $delay; ?>">
            <div class="wpa-container">
                <?php 
                $tag = ! $this->h1_output ? 'h1' : 'h2';
                if ( ! $this->h1_output ) $this->h1_output = true;
                ?>
                <<?php echo $tag; ?> class="wpa-section-title"><?php echo esc_html( $data['title'] ); ?></<?php echo $tag; ?>>
                <div class="wpa-grid course-style-<?php echo esc_attr($style); ?>">
                    <?php while ( $q->have_posts() ) : $q->the_post(); ?>
                        <article class="wpa-card">
                                    <?php if ( has_post_thumbnail() ) : 
                                            the_post_thumbnail('medium_large', ['alt' => get_the_title()]); 
                                        else : ?>
                                            <div class="wpa-post-card-fallback" style="background:var(--wpa-border-color); display:flex; flex-direction:column; align-items:center; justify-content:center; height:200px; color:var(--wpa-text-main); opacity:0.6;">
                                                <span class="dashicons dashicons-welcome-learn-more" style="font-size:40px; width:40px; height:40px; margin-bottom:10px;"></span>
                                                <small><?php _e('Academic Course', 'wp-academic-post-enhanced'); ?></small>
                                            </div>
                                        <?php endif; ?>
                            <div class="wpa-card-body">
                                <h3 class="wpa-card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                <a href="<?php the_permalink(); ?>" class="wpa-btn wpa-btn-outline wpa-course-btn"><?php echo WPA_Theme_Labels::get('label_view_course'); ?></a>
                            </div>
                        </article>
                    <?php endwhile; wp_reset_postdata(); ?>
                </div>
            </div>
        </section>
        <?php
    }

    private function render_glossary( $data, $delay ) {
        $count   = ! empty($data['count']) ? intval($data['count']) : 6;
        $style   = ! empty($data['style']) ? $data['style'] : 'images';
        $columns = ! empty($data['columns']) ? intval($data['columns']) : 3;
        $orderby = ! empty($data['orderby']) ? $data['orderby'] : 'rand';
        
        $args = [ 
            'post_type'      => 'wpa_glossary', 
            'posts_per_page' => $count, 
            'post_status'    => 'publish',
        ];

        // Ordering Logic
        if ( $orderby === 'rand_day' ) {
            // Daily Random: Cache IDs for 24 hours
            $cache_key = 'wpa_glossary_daily_' . date('Ymd');
            $post_ids = get_transient( $cache_key );
            
            if ( false === $post_ids ) {
                $all_ids = get_posts([
                    'post_type' => 'wpa_glossary',
                    'posts_per_page' => -1,
                    'fields' => 'ids',
                ]);
                if ( ! empty( $all_ids ) ) {
                    shuffle( $all_ids );
                    $post_ids = array_slice( $all_ids, 0, $count );
                    set_transient( $cache_key, $post_ids, 24 * HOUR_IN_SECONDS );
                }
            }
            
            if ( ! empty( $post_ids ) ) {
                $args['post__in'] = $post_ids;
                $args['orderby']  = 'post__in';
            }
        } elseif ( $orderby === 'title' ) {
            $args['orderby'] = 'title';
            $args['order']   = 'ASC';
        } elseif ( $orderby === 'date' ) {
            $args['orderby'] = 'date';
            $args['order']   = 'DESC';
        } else {
            $args['orderby'] = 'rand';
        }
        
        // ⚡ Bolt: Prevent expensive SQL_CALC_FOUND_ROWS query since pagination is not needed.
        $args['no_found_rows'] = true;

        $q = new WP_Query( $args );
        
        if ( ! $q->have_posts() ) return;
        ?>
        <section class="wpa-section-glossary wpa-fade-up <?php echo $delay; ?>">
            <div class="wpa-container">
                <?php 
                $tag = ! $this->h1_output ? 'h1' : 'h2';
                if ( ! $this->h1_output ) $this->h1_output = true;
                ?>
                <<?php echo $tag; ?> class="wpa-section-title"><?php echo esc_html( $data['title'] ); ?></<?php echo $tag; ?>>
                
                <?php if ( $style === 'images' ) : ?>
                    <div class="wpa-grid" style="grid-template-columns: repeat(<?php echo $columns; ?>, 1fr); gap: 20px;">
                        <?php while ( $q->have_posts() ) : $q->the_post(); 
                            $img_url = get_the_post_thumbnail_url( get_the_ID(), 'medium' );
                            $has_img = ! empty( $img_url );
                        ?>
                            <a href="<?php the_permalink(); ?>" class="wpa-glossary-visual-card" style="border: 1px solid #eee; border-radius: 8px; overflow: hidden; display: block; text-decoration: none; transition: transform 0.2s;">
                                <?php if ( $has_img ) : ?>
                                    <div class="wpa-card-image" style="height: 140px; background-image: url(<?php echo esc_url( $img_url ); ?>); background-size: cover; background-position: center;"></div>
                                <?php endif; ?>
                                <div class="wpa-card-content" style="padding: 15px;">
                                    <h4 style="margin: 0 0 5px; color: var(--wpa-accent); font-size: 1.1rem;"><?php the_title(); ?></h4>
                                    <p style="margin: 0; font-size: 0.9rem; color: #666;"><?php echo wp_trim_words( get_the_excerpt(), 10 ); ?></p>
                                </div>
                            </a>
                        <?php endwhile; ?>
                    </div>
                <?php else : ?>
                    <div class="wpa-glossary-badges" style="display: flex; flex-wrap: wrap; gap: 10px; justify-content: center;">
                        <?php while ( $q->have_posts() ) : $q->the_post(); ?>
                            <a href="<?php the_permalink(); ?>" class="wpa-btn wpa-btn-outline wpa-btn-sm"><?php the_title(); ?></a>
                        <?php endwhile; ?>
                    </div>
                <?php endif; ?>
                
                <?php wp_reset_postdata(); ?>
            </div>
        </section>
        <?php
    }

    private function render_about( $data, $delay ) {
        $img = ! empty($data['image']) ? $data['image'] : '';
        $layout = ! empty($data['layout']) ? $data['layout'] : 'right';
        $dir = $layout === 'left' ? 'row-reverse' : 'row';
        ?>
        <section class="wpa-section-about wpa-fade-up <?php echo $delay; ?>" style="flex-direction: <?php echo $dir; ?>;">
            <div class="wpa-container" style="display:flex; gap:60px; align-items:center; flex-direction: inherit;">
                <div class="wpa-about-content" style="flex:1;">
                    <?php 
                    $tag = ! $this->h1_output ? 'h1' : 'h2';
                    if ( ! $this->h1_output ) $this->h1_output = true;
                    ?>
                    <<?php echo $tag; ?> class="wpa-section-title" style="text-align:left; margin-bottom:20px;"><?php echo esc_html( $data['title'] ); ?></<?php echo $tag; ?>>
                    <?php echo wpautop( wp_kses_post( $data['content'] ) ); ?>
                </div>
                <?php if ( $img ) : ?>
                    <div class="wpa-about-image" style="flex:1;">
                        <img src="<?php echo esc_url( $img ); ?>" alt="About" style="width:100%; border-radius:16px; box-shadow:0 20px 40px -10px rgba(0,0,0,0.1);">
                    </div>
                <?php endif; ?>
            </div>
        </section>
        <?php
    }

    private function render_partners( $data, $delay ) {
        $logos = array_filter( array_map( 'trim', explode( "\n", $data['logos'] ) ) );
        if ( empty( $logos ) ) return;
        ?>
        <div class="wpa-partners-strip wpa-fade-up <?php echo $delay; ?>">
            <div class="wpa-container">
                <h3 class="wpa-partners-title"><?php echo esc_html( $data['title'] ); ?></h3>
                <div class="wpa-partners-grid">
                    <?php foreach ( $logos as $logo ) : ?>
                        <img src="<?php echo esc_url( $logo ); ?>" alt="<?php echo esc_attr($data['title']); ?> - Partner">
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php
    }

    private function render_newsletter( $data, $delay ) {
        ?>
        <section class="wpa-section-newsletter wpa-fade-up <?php echo $delay; ?>">
            <div class="wpa-container">
                <h2><?php echo esc_html( $data['title'] ); ?></h2>
                <p><?php echo esc_html( $data['desc'] ); ?></p>
                <div class="wpa-newsletter-form">
                    <?php echo do_shortcode( $data['shortcode'] ); ?>
                </div>
            </div>
        </section>
        <?php
    }

    private function render_html( $data, $delay ) {
        ?>
        <section class="wpa-section-html wpa-fade-up <?php echo $delay; ?>">
            <div class="wpa-container">
                <?php echo do_shortcode( $data['content'] ); ?>
            </div>
        </section>
        <?php
    }

    private function render_team( $data, $delay ) {
        ?>
        <section class="wpa-section-team wpa-fade-up <?php echo $delay; ?>">
            <div class="wpa-container">
                <h2 class="wpa-section-title"><?php echo esc_html( $data['title'] ); ?></h2>
                <div class="wpa-grid">
                    <?php for( $i=1; $i<=4; $i++ ) : 
                        if ( ! empty( $data["member_{$i}_name"] ) ) : 
                            $img = ! empty($data["member_{$i}_img"]) ? $data["member_{$i}_img"] : '';
                    ?>
                        <div class="wpa-card wpa-team-card">
                            <div class="wpa-card-img" style="height:250px;">
                                <?php if($img) echo '<img src="' . esc_url($img) . '" alt="' . esc_attr($data["member_{$i}_name"]) . '">'; 
                                      else echo_placeholder_icon('admin-users'); ?>
                            </div>
                            <div class="wpa-card-body" style="text-align:center;">
                                <h3 class="wpa-card-title" style="margin-bottom:5px;"><?php echo esc_html( $data["member_{$i}_name"] ); ?></h3>
                                <span class="wpa-card-meta" style="color:var(--wpa-accent);"><?php echo esc_html( $data["member_{$i}_role"] ); ?></span>
                            </div>
                        </div>
                    <?php endif; endfor; ?>
                </div>
            </div>
        </section>
        <?php
    }

    private function render_testimonials( $data, $delay ) {
        ?>
        <section class="wpa-section-testimonials wpa-fade-up <?php echo $delay; ?>">
            <div class="wpa-container">
                <h2 class="wpa-section-title"><?php echo esc_html( $data['title'] ); ?></h2>
                <div class="wpa-grid">
                    <?php for( $i=1; $i<=3; $i++ ) : 
                        if ( ! empty( $data["quote_{$i}"] ) ) : ?>
                        <div class="wpa-card wpa-testimonial-card" style="padding:30px;">
                            <div class="wpa-testimonial-icon" style="opacity:0.3; color:var(--wpa-accent); width:40px; margin-bottom:15px;"><?php echo WPA_Icons::get('format-quote'); ?></div>
                            <p class="wpa-testimonial-quote" style="font-style:italic; font-size:1.1rem; color: var(--wpa-text-muted); margin-bottom:20px;">
                                "<?php echo esc_html( $data["quote_{$i}"] ); ?>"
                            </p>
                            <h4 class="wpa-testimonial-author" style="margin:0; font-weight:700; color: var(--wpa-text-main);">
                                &mdash; <?php echo esc_html( $data["author_{$i}"] ); ?>
                            </h4>
                        </div>
                    <?php endif; endfor; ?>
                </div>
            </div>
        </section>
        <?php
    }

    private function render_faq( $data, $delay ) {
        ?>
        <section class="wpa-section-faq wpa-fade-up <?php echo $delay; ?>" style="padding: 50px 0;">
            <div class="wpa-container" style="max-width: 800px;">
                <h2 class="wpa-section-title"><?php echo esc_html( $data['title'] ); ?></h2>
                <div class="wpa-faq-accordion">
                    <?php for( $i=1; $i<=4; $i++ ) : 
                        if ( ! empty( $data["q{$i}"] ) ) : ?>
                        <div class="wpa-faq-item" style="margin-bottom: 15px; border: 1px solid var(--wpa-border-color); border-radius: 12px; overflow: hidden;">
                            <button class="wpa-faq-question" aria-expanded="false" aria-controls="wpa-faq-answer-<?php echo esc_attr( $i ); ?>" style="width: 100%; padding: 20px 25px; background: var(--wpa-bg-white); border: none; text-align: inherit; font-weight: 700; font-size: 1.1rem; cursor: pointer; display: flex; justify-content: space-between; align-items: center; transition: background 0.3s; color: var(--wpa-text-main);">
                                <?php echo esc_html( $data["q{$i}"] ); ?>
                                <span class="wpa-faq-icon" style="width:20px; transition: transform 0.3s;"><?php echo WPA_Icons::get('arrow-down-alt2'); ?></span>
                            </button>
                            <div id="wpa-faq-answer-<?php echo esc_attr( $i ); ?>" class="wpa-faq-answer" style="max-height: 0; overflow: hidden; transition: max-height: 0.4s cubic-bezier(0.4, 0, 0.2, 1); background: var(--wpa-bg-light);">
                                <div style="padding: 20px 25px; border-top: 1px solid var(--wpa-border-color); line-height: 1.6; color: var(--wpa-text-muted);">
                                    <?php echo wpautop( wp_kses_post( $data["a{$i}"] ) ); ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; endfor; ?>
                </div>
            </div>
        </section>
        <?php
    }
}

// Helper
function echo_placeholder_icon($icon) {
    echo '<div style="width:100%;height:100%;background:var(--wpa-bg-light);display:flex;align-items:center;justify-content:center;color:#cbd5e1;"><div style="width:40px;height:40px;">' . WPA_Icons::get($icon) . '</div></div>';
}
