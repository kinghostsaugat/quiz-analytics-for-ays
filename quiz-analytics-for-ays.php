<?php
/**
 * Plugin Name: Quiz Analytics For AYS
 * Description: Provides data analysis and visualization for AYS Quizzes.
 * Version: 1.0
 * Author: Saugat
 * text-domain: quiz-analytics
 */

 if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


define('QAP_PATH', plugin_dir_path(__FILE__));
define('QAP_URL', plugin_dir_url(__FILE__));

require_once QAP_PATH . 'includes/class-quiz-shortcodes.php';
require_once QAP_PATH . 'includes/class-quiz-data-processor.php';

class Quiz_Analytics {
    public function __construct()
    {
        add_action('plugins_loaded', array($this, 'QA_init'));
        register_activation_hook(__FILE__, array($this, 'QA_register_analytics_dashboard'));
        add_action('display_post_states', array($this, 'QA_add_analytics_dashboard_page_state'), 10, 2);
        add_action('template_redirect', array($this, 'QA_page_redirect'));
    }

    public function QA_init() {
        new QA_Quiz_Data_Processor();
        new QA_Quiz_shortcodes();
    }

    public static function QA_register_analytics_dashboard() {
        $page_slug = 'analytics-dashboard';
        $page_title = 'Analytics Dashboard';
        $page_content = '[analytics_dashboard]';

        if(null === get_page_by_path($page_slug)) {
            wp_insert_post(
                array(
                    'post_title' => $page_title,
                    'post_name' => $page_slug,
                    'post_status' => 'publish',
                    'post_type' => 'page',
                    'post_content' => $page_content,
                    'post_author' => 1
                )
            );
        }
    }

    public function QA_add_analytics_dashboard_page_state($post_states, $post) {
        if ($post->post_name === 'analytics-dashboard') {
            $post_states['analytics_dashboard'] = __('Analytics Page');
        }
        return $post_states;
    }

    public function QA_page_redirect() {
    if (is_page('analytics-dashboard')) {
        $user = wp_get_current_user();
        $roles = apply_filters('quiz_analytics_required_roles', ['administrator']);

        if (!is_array($roles)) {
            $roles = (array) $roles;
        }

        if(empty($roles)) {
            return;
        }

        if (!$user->exists() || empty(array_intersect($roles, $user->roles))) {
            wp_redirect(home_url());
            exit;
        }
    }
}

}

new Quiz_Analytics();