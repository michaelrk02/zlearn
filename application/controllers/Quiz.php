<?php
defined('BASEPATH') OR exit;

class Quiz extends CI_Controller {

    protected $id;
    protected $quiz;
    protected $course;
    protected $user_id;
    protected $role;

    public function __construct() {
        parent::__construct();

        $this->auth->check();

        $this->load->model('course_model', 'courses');
        $this->load->model('quiz_model', 'quizzes');

        $this->user_id = zl_session_get('user_id');
    }

    public function index() {
        redirect('course/list');
    }

    public function view() {
        $this->init_quiz('course_id, title, description, num_questions, essay, mc_score_correct, mc_score_incorrect, mc_score_empty, show_grades, show_leaderboard, locked');

        $attempt = NULL;

        if ($this->role === 'participant') {
            $response = $this->quizzes->get_response($this->id, $this->user_id);
            if (isset($response)) {
                $attempt = [];
                $attempt['answered'] = 0;
                for ($i = 1; $i <= $this->quiz['num_questions']; $i++) {
                    if (!empty($response['data'][$i])) {
                        $attempt['answered']++;
                    }
                }
            }
        }

        $this->load->view('header', ['title' => $this->quiz['title'].' - Quiz ('.$this->course['title'].')']);
        $this->load->view('quiz/view', ['id' => $this->id, 'role' => $this->role, 'attempt' => $attempt, 'course' => $this->course, 'quiz' => $this->quiz]);
        $this->load->view('footer');
    }

    public function attempt() {
        $this->init_quiz('course_id, title, num_questions, questions_link, essay, mc_num_choices, locked');
        $this->ensure_role('participant');

        if (empty($this->quiz['locked'])) {
            if ($this->quizzes->init_response($this->id, $this->user_id, !empty($this->quiz['essay']), $this->quiz['num_questions'])) {
                $token = [];
                $token[0] = ['user_id' => $this->user_id, 'expired' => time() + 86400];
                $token[0] = base64_encode(serialize($token[0]));
                $token[1] = hash_hmac('sha256', $token[0], ZL_SECRET_KEY);
                $token = implode(':', $token);
                $token = base64_encode($token);

                $this->load->view('header', ['title' => htmlspecialchars($this->quiz['title']).' - Quiz Attempt']);
                $this->load->view('quiz/attempt', ['id' => $this->id, 'quiz' => $this->quiz, 'course' => $this->course, 'token' => $token]);
                $this->load->view('footer');
            } else {
                zl_error('Unable to initialize responses');
                redirect(site_url('quiz/view').'?id='.urlencode($this->id));
            }
        } else {
            zl_error('The quiz is locked at this time');
            redirect(site_url('quiz/view').'?id='.urlencode($this->id));
        }
    }

    public function create() {
        $course_id = $this->input->get('course_id');

        if (!empty($course_id)) {
            $course = $this->courses->get($course_id, 'course_id, title');

            if (isset($course)) {
                if ($this->courses->get_role($course_id, $this->user_id) === 'instructor') {
                    $this->quiz = [];
                    $this->quiz['title'] = '';
                    $this->quiz['description'] = '';
                    $this->quiz['num_questions'] = 0;
                    $this->quiz['questions_link'] = '';
                    $this->quiz['essay'] = 0;
                    $this->quiz['mc_num_choices'] = 2;
                    $this->quiz['show_grades'] = 0;
                    $this->quiz['show_leaderboard'] = 0;

                    if (!empty($this->input->post('submit'))) {
                        $this->load->library('form_validation');

                        $this->init_quiz_manager($this->form_validation);

                        if ($this->form_validation->run()) {
                            $this->quiz['course_id'] = $course_id;
                            $this->quiz['title'] = $this->input->post('title');
                            $this->quiz['description'] = $this->input->post('description');
                            $this->quiz['num_questions'] = $this->input->post('num_questions');
                            $this->quiz['questions_link'] = $this->input->post('questions_link');
                            $this->quiz['essay'] = (int)!empty($this->input->post('essay'));
                            $this->quiz['mc_num_choices'] = $this->input->post('mc_num_choices');
                            $this->quiz['mc_score_correct'] = 1.0;
                            $this->quiz['mc_score_incorrect'] = 0.0;
                            $this->quiz['mc_score_empty'] = 0.0;

                            $this->quiz['mc_answers'] = [];
                            for ($i = 1; $i <= $this->quiz['num_questions']; $i++) {
                                $this->quiz['mc_answers'][$i] = 0;
                            }
                            $this->quiz['mc_answers'] = serialize($this->quiz['mc_answers']);

                            $this->quiz['show_grades'] = (int)!empty($this->input->post('show_grades'));
                            $this->quiz['show_leaderboard'] = (int)!empty($this->input->post('show_leaderboard'));
                            $this->quiz['locked'] = 1;

                            if (($id = $this->quizzes->add($this->quiz)) !== NULL) {
                                zl_success('Quiz <b>'.htmlspecialchars($this->quiz['title']).'</b> added successfully to <b>'.$course['title'].'</b> course');
                                redirect(site_url('quiz/view').'?id='.urlencode($id));
                            } else {
                                zl_error('Failed to create quiz');
                            }
                        } else {
                            zl_error($this->form_validation->error_string());
                        }

                        $this->quiz['title'] = set_value('title');
                        $this->quiz['description'] = set_value('description');
                        $this->quiz['num_questions'] = set_value('num_questions');
                        $this->quiz['questions_link'] = set_value('questions_link');
                        $this->quiz['essay'] = !empty(set_value('essay'));
                        $this->quiz['mc_num_choices'] = set_value('mc_num_choices');
                        $this->quiz['show_grades'] = !empty(set_value('show_grades'));
                        $this->quiz['show_leaderboard'] = !empty(set_value('show_leaderboard'));
                    }

                    $this->quiz['course_id'] = $course_id;

                    $this->load->view('header', ['title' => 'Create Quiz']);
                    $this->load->view('quiz/manager', ['action' => 'create', 'course' => $course, 'quiz' => $this->quiz]);
                    $this->load->view('footer');
                } else {
                    zl_error('You must be an instructor to perform this action');
                    redirect(site_url('course/view').'?id='.urlencode($course_id));
                }
            } else {
                zl_error('Invalid course ID');
                redirect('course/list');
            }
        } else {
            zl_error('Invalid operation');
            redirect('course/list');
        }
    }

    public function edit() {
        $this->init_quiz('course_id, title, description, num_questions, questions_link, essay, mc_num_choices, mc_answers, show_grades, show_leaderboard');
        $this->ensure_role('instructor');

        if (!empty($this->input->post('submit'))) {
            $this->load->library('form_validation');

            $this->init_quiz_manager($this->form_validation);

            if ($this->form_validation->run()) {
                $this->quiz['title'] = $this->input->post('title');
                $this->quiz['description'] = $this->input->post('description');
                $this->quiz['num_questions'] = $this->input->post('num_questions');
                $this->quiz['questions_link'] = $this->input->post('questions_link');
                $this->quiz['essay'] = !empty($this->input->post('essay'));
                $this->quiz['mc_num_choices'] = $this->input->post('mc_num_choices');
                $this->quiz['show_grades'] = !empty($this->input->post('show_grades'));
                $this->quiz['show_leaderboard'] = !empty($this->input->post('show_leaderboard'));

                $this->quiz['mc_answers'] = unserialize($this->quiz['mc_answers']);
                for ($i = 1; $i <= $this->quiz['num_questions']; $i++) {
                    if ($this->quiz['mc_answers'][$i] > $this->quiz['mc_num_choices']) {
                        $this->quiz['mc_answers'][$i] = 0;
                    }
                }
                $this->quiz['mc_answers'] = serialize($this->quiz['mc_answers']);

                if ($this->quizzes->set($this->id, $this->quiz)) {
                    zl_success('Quiz updated successfully');
                    redirect(site_url('quiz/view').'?id='.urlencode($this->id));
                } else {
                    zl_error('Failed to update the quiz');
                }
            } else {
                zl_error($this->form_validation->error_string());
            }

            $this->quiz['title'] = set_value('title');
            $this->quiz['description'] = set_value('description');
            $this->quiz['num_questions'] = set_value('num_questions');
            $this->quiz['questions_link'] = set_value('questions_link');
            $this->quiz['essay'] = !empty(set_value('essay'));
            $this->quiz['mc_num_choices'] = set_value('mc_num_choices');
            $this->quiz['show_grades'] = !empty(set_value('show_grades'));
            $this->quiz['show_leaderboard'] = !empty(set_value('show_leaderboard'));
        }

        $this->load->view('header', ['title' => 'Edit Quiz']);
        $this->load->view('quiz/manager', ['action' => 'edit', 'id' => $this->id, 'course' => $this->course, 'quiz' => $this->quiz]);
        $this->load->view('footer');
    }

    public function configure() {
        $this->init_quiz('course_id, title, num_questions, essay, mc_num_choices, mc_score_correct, mc_score_incorrect, mc_score_empty, mc_answers, locked');
        $this->ensure_role('instructor');

        $this->quiz['mc_answers'] = unserialize($this->quiz['mc_answers']);
        for ($i = 1; $i <= $this->quiz['num_questions']; $i++) {
            if (!isset($this->quiz['mc_answers'][$i])) {
                $this->quiz['mc_answers'][$i] = 0;
            }
        }

        if (empty($this->quiz['essay'])) {
            if (!empty($this->input->post('submit'))) {
                $this->load->library('form_validation');

                $this->init_quiz_configuration($this->form_validation);

                if ($this->form_validation->run()) {
                    $this->quiz['mc_score_correct'] = $this->input->post('mc_score_correct');
                    $this->quiz['mc_score_incorrect'] = $this->input->post('mc_score_incorrect');
                    $this->quiz['mc_score_empty'] = $this->input->post('mc_score_empty');

                    $this->quiz['mc_answers'] = [];
                    $mc_answers = $this->input->post('mc_answers');
                    for ($i = 1; $i <= $this->quiz['num_questions']; $i++) {
                        $this->quiz['mc_answers'][$i] = min(max(0, isset($mc_answers[$i]) ? $mc_answers[$i] : 0), 10);
                    }
                    $this->quiz['mc_answers'] = serialize($this->quiz['mc_answers']);

                    $this->quiz['locked'] = !empty($this->input->post('locked'));

                    if ($this->quizzes->set($this->id, $this->quiz)) {
                        zl_success('Quiz has been successfully configured');
                        redirect(site_url('quiz/configure').'?id='.urlencode($this->id));
                    } else {
                        zl_error('Failed to configure quiz. Please try again later');
                    }
                } else {
                    zl_error($this->form_validation->error_string());
                }

                $this->quiz['mc_score_correct'] = set_value('mc_score_correct');
                $this->quiz['mc_score_incorrect'] = set_value('mc_score_incorrect');
                $this->quiz['mc_score_empty'] = set_value('mc_score_empty');
                $this->quiz['locked'] = !empty(set_value('locked'));
            }
        }

        $this->load->view('header', ['title' => 'Configure Quiz']);
        $this->load->view('quiz/configure', ['id' => $this->id, 'course' => $this->course, 'quiz' => $this->quiz]);
        $this->load->view('footer');
    }

    public function delete() {
    }

    protected function init_quiz($columns = '*') {
        $this->id = $this->input->get('id');

        $this->quiz = $this->quizzes->get($this->id, $columns);
        if ($this->quiz === NULL) {
            zl_error('Quiz not found');
            redirect('course/list');
        }

        // must include `course_id` column or error otherwise
        $this->course = $this->courses->get($this->quiz['course_id'], 'title');
        if ($this->course === NULL) {
            zl_error('Invalid course');
            redirect('course/list');
        }

        $this->role = $this->courses->get_role($this->quiz['course_id'], $this->user_id);
        if (!isset($this->role)) {
            zl_error('You have no access to view this quiz');
            redirect('course/list');
        }
    }

    protected function init_quiz_manager(&$form_validation) {
        $form_validation->set_rules('title', 'Title', 'required|max_length[100]');
        $form_validation->set_rules('description', 'Description', 'required|max_length[1000]');
        $form_validation->set_rules('num_questions', 'Number of questions', 'required|is_natural');
        $form_validation->set_rules('questions_link', 'Questions link', 'required|max_length[1000]');
        $form_validation->set_rules('mc_num_choices', 'Number of multiple choices', 'required|integer|greater_than_equal_to[2]|less_than_equal_to[10]');
    }

    protected function init_quiz_configuration(&$form_validation) {
        $form_validation->set_rules('mc_score_correct', 'Correct score', 'required|integer');
        $form_validation->set_rules('mc_score_incorrect', 'Incorrect score', 'required|integer');
        $form_validation->set_rules('mc_score_empty', 'Empty score', 'required|integer');
    }

    protected function ensure_role($role) {
        if ($this->role !== $role) {
            zl_error('You must be a(n) '.$role.' to perform this action');
            redirect(site_url('quiz/view').'?id='.urlencode($this->id));
        }
    }

}

