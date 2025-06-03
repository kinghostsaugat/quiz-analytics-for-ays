<?php

class QA_Quiz_shortcodes {
    public function __construct() {
        add_shortcode('analytics_dashboard', array($this, 'QAS_analytics_dashboard_shortcode'));
    }

    public function QAS_analytics_dashboard_shortcode() {

        wp_enqueue_style('quiz-analytics-style', QAP_URL . 'public/css/quiz-analytics.css', array(), filemtime(QAP_PATH . 'public/css/quiz-analytics.css'));
        wp_enqueue_script('quiz-chart-data', QAP_URL . 'public/js/chart-data.js', array('jquery'), filemtime(QAP_PATH . 'public/js/chart-data.js'), true);
        wp_localize_script('quiz-chart-data', 'quizAnalyticsData', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('quiz_analytics_nonce')
        ));

        $cached = get_transient('qa_analytics_dashboard');
        if (false !== $cached) {
            return $cached;
        }

        ob_start();
        $template_path = QAP_PATH . 'templates/analytics-dashboard-template.php';
        if ( file_exists( $template_path ) ) {
            include $template_path;
        } else {
            echo '<p>Dashboard template not found.</p>';
        }
        $output = ob_get_clean();

        set_transient('qa_analytics_dashboard', $output, 5 * MINUTE_IN_SECONDS);
        return $output;
    }
}