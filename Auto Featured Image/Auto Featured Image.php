<?php
/**
 * Plugin Name: Auto Featured Image
 * Plugin URI: https://example.com/
 * Description: Automatically sets the first image in a post as the featured image without adding to media library.
 * Version: 1.6
 * Author: B1PL0B
 * Author URI: https://example.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class Auto_Featured_Image {
    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('admin_menu', array($this, 'add_settings_page'));
        add_action('admin_menu', array($this, 'add_process_all_button'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('save_post', array($this, 'set_featured_image'), 10, 3);
        add_action('admin_notices', array($this, 'display_admin_notices'));
        add_action('add_meta_boxes', array($this, 'add_url_metabox'));
    }

    public function add_settings_page() {
        add_options_page(
            'Auto Featured Image Settings',
            'Auto Featured Image',
            'manage_options',
            'auto-featured-image',
            array($this, 'render_settings_page')
        );
    }

    public function add_process_all_button() {
        add_submenu_page(
            'options-general.php',
            'Process All Posts',
            'Process All Posts',
            'manage_options',
            'afi-process-all',
            array($this, 'process_all_posts_page')
        );
    }

    public function register_settings() {
        register_setting('auto_featured_image_settings', 'afi_post_types');
    }

    public function render_settings_page() {
        // ... (keep the existing render_settings_page method)
    }

    public function set_featured_image($post_id, $post, $update) {
        $post_types = get_option('afi_post_types', array('post'));
        
        if (!in_array($post->post_type, $post_types)) {
            return;
        }

        if (has_post_thumbnail($post_id)) {
            return;
        }

        $this->process_post($post_id);
    }

    public function process_post($post_id) {
        $post = get_post($post_id);
        $post_content = $post->post_content;
        $first_image = $this->get_first_image($post_content);

        if ($first_image) {
            $this->set_featured_image_by_url($post_id, $first_image);
            update_post_meta($post_id, '_afi_processed', true);
            return true;
        } else {
            update_post_meta($post_id, '_afi_no_image', true);
            return false;
        }
    }

    private function get_first_image($content) {
        if (preg_match('/<img.+src=[\'"](?P<src>.+?)[\'"].*>/i', $content, $match)) {
            return $match['src'];
        }
        return false;
    }

    public function set_featured_image_by_url($post_id, $image_url) {
        $image_id = $this->get_image_id($image_url);
        
        if ($image_id) {
            set_post_thumbnail($post_id, $image_id);
            return true;
        } else {
            // If the image is not in the media library, store the URL as post meta
            update_post_meta($post_id, '_thumbnail_ext_url', $image_url);
            add_filter('post_thumbnail_html', array($this, 'external_thumbnail_html'), 10, 5);
            return true;
        }
        
        return false;
    }

    private function get_image_id($image_url) {
        global $wpdb;
        $attachment = $wpdb->get_col($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE guid='%s';", $image_url ));
        return $attachment ? $attachment[0] : null;
    }

    public function external_thumbnail_html($html, $post_id, $post_thumbnail_id, $size, $attr) {
        $external_url = get_post_meta($post_id, '_thumbnail_ext_url', true);
        if ($external_url) {
            $attr = wp_parse_args($attr);
            $attr['src'] = $external_url;
            $attr['class'] = isset($attr['class']) ? $attr['class'] . ' external-thumbnail' : 'external-thumbnail';
            $html = rtrim("<img");
            foreach ($attr as $name => $value) {
                $html .= " $name=" . '"' . $value . '"';
            }
            $html .= ' />';
        }
        return $html;
    }

    public function display_admin_notices() {
        // ... (keep the existing display_admin_notices method)
    }

    public function process_all_posts_page() {
        // ... (keep the existing process_all_posts_page method)
    }

    private function process_all_posts() {
        // ... (keep the existing process_all_posts method)
    }

    public function add_url_metabox() {
        $post_types = get_option('afi_post_types', array('post'));
        foreach ($post_types as $post_type) {
            add_meta_box(
                'afi_url_metabox',
                'Set Featured Image by URL',
                array($this, 'render_url_metabox'),
                $post_type,
                'side',
                'low'
            );
        }
    }

    public function render_url_metabox($post) {
        wp_nonce_field('afi_set_image_by_url', 'afi_set_image_by_url_nonce');
        $current_url = get_post_meta($post->ID, '_thumbnail_ext_url', true);
        ?>
        <p>
            <label for="afi_image_url">Image URL:</label>
            <input type="text" id="afi_image_url" name="afi_image_url" value="<?php echo esc_attr($current_url); ?>" style="width: 100%;">
        </p>
        <p>
            <input type="submit" name="afi_set_image_by_url" class="button button-primary" value="Set as Featured Image">
        </p>
        <?php
    }

    public function save_url_metabox($post_id) {
        if (!isset($_POST['afi_set_image_by_url_nonce']) || !wp_verify_nonce($_POST['afi_set_image_by_url_nonce'], 'afi_set_image_by_url')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        if (isset($_POST['afi_set_image_by_url']) && !empty($_POST['afi_image_url'])) {
            $image_url = esc_url_raw($_POST['afi_image_url']);
            $this->set_featured_image_by_url($post_id, $image_url);
        }
    }
}

// Initialize the plugin
function auto_featured_image_init() {
    $afi = Auto_Featured_Image::get_instance();
    add_action('save_post', array($afi, 'save_url_metabox'));
}
add_action('plugins_loaded', 'auto_featured_image_init');
