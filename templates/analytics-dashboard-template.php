<div class="quiz-principal-dashboard-wrapper">
    <h1><?php echo esc_html__("Quiz Analytics Dashboard for Principals", 'quiz-analytics'); ?></h1>

    <div class="dashboard-filters">
        <label for="quiz-select"><?php esc_html_e("Select Quiz:", 'quiz-analytics'); ?></label>
        <select id="quiz-select">
            <option value=""><?php esc_html_e("All Quizzes", 'quiz-analytics'); ?></option>
            <?php
            $data_processor = new QA_Quiz_Data_Processor();
            $quizzes = $data_processor->QADP_get_all_quizzes();
            if ( $quizzes ) {
                foreach ( $quizzes as $quiz ) {
                    echo '<option value="' . esc_attr( $quiz['id'] ) . '">' . esc_html( $quiz['title'] ) . '</option>';
                }
            }
            ?>
        </select>

        <button id="apply-filters"><?php esc_html_e("Apply Filters", 'quiz-analytics'); ?></button>
    </div>

    <div class="dashboard-overview">
        <h2><?php esc_html_e("Overall Performance", 'quiz-analytics'); ?></h2>
        <p><?php esc_html_e("Total Quizzes Taken:", 'quiz-analytics'); ?> <span id="total-quizzes-taken"></span></p>
        <p><?php esc_html_e("Average Score:", 'quiz-analytics'); ?> <span id="average-overall-score"></span>%</p>
        <p><?php esc_html_e("Total Students:", 'quiz-analytics'); ?> <span id="total-unique-students"></span></p>
    </div>

    <div class="dashboard-charts">
        <h2><?php esc_html_e("Average Score Per Quiz", 'quiz-analytics'); ?></h2>
        <canvas id="quizAverageChart"></canvas>
    </div>

    <div class="dashboard-charts" id="individual-quiz-performance-chart-container" style="display: none;">
        <h2 id="individual-quiz-performance-chart-title"></h2>
        <canvas id="individualStudentPerformanceChart"></canvas>
    </div>

    <div class="dashboard-tables">
        <h2><?php esc_html_e("Student Performance Summary", 'quiz-analytics'); ?></h2>
        <table id="student-performance-table" class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e("Student Name", 'quiz-analytics'); ?></th>
                    <th><?php esc_html_e("Total Attempts", 'quiz-analytics'); ?></th>
                    <th><?php esc_html_e("Average Score", 'quiz-analytics'); ?></th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>