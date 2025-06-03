<?php
class QA_Quiz_Data_Processor {
    public function __construct()
    {
        add_action('wp_ajax_QADP_quiz_performance_data', array($this, 'QADP_quiz_performance_data'));
        add_action('wp_ajax_nopriv_QADP_quiz_performance_data', array($this, 'QADP_quiz_performance_data'));
    }

    public function QADP_get_raw_quiz_results($args = array()) {
        global $wpdb;

        $quiz_reports_table = $wpdb->prefix . 'aysquiz_reports';
        $quizzes_table = $wpdb->prefix . 'aysquiz_quizes';
        $users_table = $wpdb->users;

        $sql = "SELECT 
                    qr.user_id,
                    qr.quiz_id,
                    qr.score,
                    q.title,
                    u.display_name
                FROM {$quiz_reports_table} AS qr
                JOIN {$quizzes_table} AS q ON qr.quiz_id = q.id
                JOIN {$users_table} AS u ON qr.user_id = u.ID
                WHERE 1=1";
        $params = [];
        if(!empty($args['quizID'])) {
            $sql .= " AND qr.quiz_id = %d";
            $params[] = $args['quizID'];
        }

        $sql .= " ORDER BY qr.end_date DESC";
        $prepare = $wpdb->prepare($sql, $params);
        $results = $wpdb->get_results($prepare);

        return $results;
    }

    public function QADP_process_quiz_data( $results ) {
        $processed_data = array(
            'overall_performance' => array(
                'total_quizzes_taken' => 0,
                'average_score' => 0,
                'total_students' => 0,
            ),
            'student_performance' => array(),
            'quiz_averages' => array(),
        );

        $total_score = 0;
        $unique_students = array();
        $quiz_attempts_count = array();

        if(!empty($results)) {
            foreach($results as $result) {
                $quiz_titles[$result->quiz_id] = $result->title;
                $processed_data['overall_performance']['total_quizzes_taken']++;
                $total_score += $result->score;

                $unique_students[$result->user_id] = true;

                $quiz_id = $result->quiz_id;;

                if(!isset($quiz_scores_add[$quiz_id])) {
                    $quiz_scores_add[$quiz_id] = 0;
                    $quiz_attempts_count[$quiz_id] = 0;
                }

                $quiz_scores_add[$quiz_id] += (float) $result->score;
                $quiz_attempts_count[$quiz_id]++;

                $student_id = $result->user_id;
                if(!isset($processed_data['student_performance'][$student_id])) {
                    $processed_data['student_performance'][$student_id] = array(
                        'user_name' => $result->display_name,
                        'total_attempts' => 0,
                        'total_score_sum' => 0,
                        'average_score' => 0,
                    );
                }
                $processed_data['student_performance'][$student_id]['total_attempts']++;
                $processed_data['student_performance'][$student_id]['total_score_sum'] += (float) $result->score;
            }

            if($processed_data['overall_performance']['total_quizzes_taken'] > 0) {
                $processed_data['overall_performance']['average_score'] = $total_score/$processed_data['overall_performance']['total_quizzes_taken'];
            }
            $processed_data['overall_performance']['total_students'] = count($unique_students);

            if(!empty($quiz_scores_add)) {
                foreach($quiz_scores_add as $quiz_id=>$sum) {
                    $average = $sum/$quiz_attempts_count[$quiz_id];
                    
                    $found_title = $quiz_titles[$quiz_id] ?? 'Unknown Quiz';
                    $processed_data['quiz_averages'][$quiz_id] = array (
                        'quiz_title' => $found_title,
                        'average_score' => $average
                    );
                }
            }
            
            if(!empty($processed_data['student_performance'])) {
                foreach($processed_data['student_performance'] as $student_id => &$student_data) {
                    if($student_data['total_attempts'] > 0) {
                        $student_data['average_score'] = $student_data['total_score_sum']/$student_data['total_attempts'];
                    }
                }
            }
            unset($student_data);

            return $processed_data;
        }
    }

    public function QADP_quiz_performance_data() {
        check_ajax_referer('quiz_analytics_nonce', 'nonce');

        $filters = array();
        if(isset($_POST['quizID'])) {
            $filters['quizID'] = absint($_POST['quizID']); 
        }

        $raw_data = $this->QADP_get_raw_quiz_results($filters);
        $processed_data = $this->QADP_process_quiz_data( $raw_data );

        wp_send_json_success($processed_data);

    }

    public function QADP_get_all_quizzes() {
        global $wpdb;

        $quiz_reports_table = $wpdb->prefix . 'aysquiz_reports';
        $quizzes_table = $wpdb->prefix . 'aysquiz_quizes';
        $users_table = $wpdb->users;
        $quizzes_selctions = "SELECT
                                    q.id,
                                    q.title
                                FROM {$quizzes_table} AS q
                                JOIN {$quiz_reports_table} AS qr ON qr.quiz_id = q.id
                                JOIN {$users_table} AS u ON qr.user_id = u.ID
                                GROUP BY q.id, q.title 
                                ORDER BY q.id ASC";
        $quizzes_selctions_results = $wpdb->get_results($quizzes_selctions, ARRAY_A);

        return $quizzes_selctions_results;
    }
}