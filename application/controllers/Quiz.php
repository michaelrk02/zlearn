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
        redirect('course/listing');
    }

    public function view() {
        $this->init_quiz('course_id, title, description, duration, num_questions, essay, mc_score_correct, mc_score_incorrect, mc_score_empty, show_grades, show_leaderboard, locked');

        $attempt = NULL;

        if ($this->role === 'participant') {
            $info = $this->quizzes->get_response_info($this->id, $this->user_id, 'timestamp, score');
            if (isset($info)) {
                $attempt = [];
                $attempt['timestamp'] = $info['timestamp'];
                $attempt['score'] = $info['score'];
            }
        }

        $this->load->view('header', ['title' => $this->quiz['title'].' - Quiz ('.$this->course['title'].')']);
        $this->load->view('quiz/view', ['id' => $this->id, 'role' => $this->role, 'attempt' => $attempt, 'course' => $this->course, 'quiz' => $this->quiz]);
        $this->load->view('footer');
    }

    public function attempt() {
        $this->init_quiz('course_id, title, duration, num_questions, essay, mc_num_choices, locked, hash');
        $this->ensure_role('participant');

        $grant = FALSE;
        $info = $this->quizzes->get_response_info($this->id, $this->user_id, 'timestamp');
        if (isset($info)) {
            if ((($this->quiz['duration'] == 0) && empty($this->quiz['locked'])) || (($this->quiz['duration'] > 0) && (time() <= $info['timestamp'] + $this->quiz['duration'] * 60))) {
                $grant = TRUE;
            }
        } else {
            if (empty($this->quiz['locked'])) {
                $grant = TRUE;
            }
        }

        if ($grant) {
            $timestamp = isset($info) ? $info['timestamp'] : time();
            if ($this->quizzes->init_response($this->id, $this->user_id, !empty($this->quiz['essay']), $this->quiz['num_questions'], $timestamp)) {
                $token = $this->auth->create_token(['user_id' => $this->user_id, 'hash' => $this->quiz['hash']], 86400);

                $pdf_token = $this->auth->create_token(['quiz_id' => $this->id], 10);
                $pdf_url = site_url('quiz/pdf').'?token='.urlencode($pdf_token);

                $this->load->view('header', ['title' => htmlspecialchars($this->quiz['title']).' - Quiz Attempt']);
                $this->load->view('quiz/attempt', ['id' => $this->id, 'quiz' => $this->quiz, 'course' => $this->course, 'token' => $token, 'timestamp' => $timestamp, 'pdf_url' => $pdf_url]);
                $this->load->view('footer');
            } else {
                zl_error('Unable to initialize the quiz response. Contact admin if you think this is a mistake');
                redirect(site_url('quiz/view').'?id='.urlencode($this->id));
            }
        } else {
            zl_error('Access to this quiz has been denied');
            redirect(site_url('quiz/view').'?id='.urlencode($this->id));
        }
    }

    public function grade() {
        $this->init_quiz('course_id, title, num_questions, essay, mc_score_correct, mc_score_incorrect, mc_score_empty, mc_answers');
        $this->ensure_role('instructor');

        $this->quiz['mc_answers'] = unserialize($this->quiz['mc_answers']);

        if (!empty($this->quiz['essay'])) {
            $user_id = $this->input->get('user_id');
            $question_no = $this->input->get('question_no');

            if (is_numeric($question_no)) {
                $question_no = min(max($question_no, 1), $this->quiz['num_questions']);

                $response = $this->quizzes->get_response($this->id, $user_id, $question_no);
                if (isset($response)) {
                    $this->load->model('users_model', 'users');

                    if (!empty($this->input->post('submit'))) {
                        $this->load->library('form_validation');

                        $this->form_validation->set_rules('points', 'Points', 'integer');

                        if ($this->form_validation->run()) {
                            $points = $this->input->post('points');

                            if ($this->quizzes->put_response($this->id, $user_id, $question_no, NULL, $points)) {
                                zl_success('Successfully graded with <b>'.$points.' points</b>');
                                redirect(site_url('quiz/grade').'?id='.urlencode($this->id).'&user_id='.urlencode($user_id).'&question_no='.$question_no);
                            } else {
                                zl_error('Unable to grade. Please try again later');
                            }
                        } else {
                            zl_error($this->form_validation->error_string());
                        }
                    }

                    $user = $this->users->get($user_id, 'name');

                    $this->load->view('header', ['title' => 'Quiz Grading']);
                    $this->load->view('quiz/grade', [
                        'course' => $this->course,
                        'quiz' => $this->quiz,
                        'id' => $this->id,
                        'user' => $user,
                        'user_id' => $user_id,
                        'question_no' => $question_no,
                        'response' => $response
                    ]);
                    $this->load->view('footer');
                } else {
                    zl_error('Cannot find user response');
                    redirect(site_url('quiz/view').'?id='.urlencode($this->id));
                }
            } else {
                zl_error('Invalid question number');
                redirect(site_url('quiz/view').'?id='.urlencode($this->id));
            }
        } else {
            $user_id = $this->input->get('user_id');

            if ($this->autograde($user_id)) {
                $this->load->model('users_model', 'users');

                $user = $this->users->get($user_id, 'name');

                zl_success('Successfully autograded <b>'.htmlspecialchars($user['name']).'</b>');
                redirect(site_url('quiz/grades').'?id='.urlencode($this->id).'#UID-'.md5($user_id));
            } else {
                zl_error('Failed to grade automatically due to some error');
                redirect(site_url('quiz/view').'?id='.urlencode($this->id));
            }
        }
    }

    public function autograde_all() {
        $this->init_quiz('course_id, num_questions, essay, mc_score_correct, mc_score_incorrect, mc_score_empty, mc_answers');
        $this->ensure_role('instructor');

        $this->quiz['mc_answers'] = unserialize($this->quiz['mc_answers']);

        if (empty($this->quiz['essay'])) {
            $responses = $this->quizzes->list_responses($this->id, ZL_DB_PREFIX.'quiz_responses.user_id AS user_id');
            $processed = 0;
            foreach ($responses as $response) {
                if ($this->autograde($response['user_id'])) {
                    $processed++;
                }
            }

            if ($processed == count($responses)) {
                zl_success('Successfully graded all users automatically');
            } else {
                zl_warning('Graded '.$processed.' users out of '.count($responses).' users');
            }
            redirect(site_url('quiz/grades').'?id='.urlencode($this->id));
        } else {
            zl_error('Not a multiple choice type of quiz. Unable to perform autograding');
            redirect(site_url('quiz/view').'?id='.urlencode($this->id));
        }
    }

    protected function autograde($user_id) {
        $response = $this->quizzes->get_response($this->id, $user_id);
        if (isset($response)) {
            for ($i = 1; $i <= $this->quiz['num_questions']; $i++) {
                $flag = 'correct';

                if ($this->quiz['mc_answers'][$i] == 0) {
                    $flag = 'correct';
                } else {
                    if ($response['data'][$i][0] != 0) {
                        if ($response['data'][$i][0] == $this->quiz['mc_answers'][$i]) {
                            $flag = 'correct';
                        } else {
                            $flag = 'incorrect';
                        }
                    } else {
                        $flag = 'empty';
                    }
                }

                $response['data'][$i][1] = $this->quiz['mc_score_'.$flag];
            }

            $response = serialize($response['data']);

            return $this->quizzes->set_response_info($this->id, $user_id, ['data' => $response]);
        }
        return FALSE;
    }

    public function save_response() {
        $this->init_quiz('course_id, title, num_questions, essay');
        $this->ensure_role('instructor');

        if (!empty($this->quiz['essay'])) {
            $this->load->model('users_model', 'users');

            $user_id = $this->input->get('user_id');
            $question_no = $this->input->get('question_no');

            $user = $this->users->get($user_id, 'name');
            if (isset($user)) {
                if (is_numeric($question_no)) {
                    $question_no = min(max($question_no, 1), $this->quiz['num_questions']);

                    $response = $this->quizzes->get_response($this->id, $user_id, $question_no);
                    if (isset($response)) {
                        $unique_id = md5($this->id.'/'.$user_id.'/'.$question_no);
                        $filename = url_title($this->quiz['title']).'_'.url_title($user['name']).'_'.$question_no.'_'.$unique_id;

                        $this->output->set_status_header(200);
                        $this->output->set_content_type('text/plain');
                        $this->output->set_header('Content-Disposition: attachment; filename="'.$filename.'.txt"');
                        $this->output->set_output($response['data'][0]);
                    } else {
                        zl_error('Cannot find user response');
                        redirect(site_url('quiz/view').'?id='.urlencode($this->id));
                    }
                } else {
                    zl_error('Invalid question number');
                    redirect(site_url('quiz/view').'?id='.urlencode($this->id));
                }
            } else {
                zl_error('Invalid user ID');
                redirect(site_url('quiz/view').'?id='.urlencode($this->id));
            }
        } else {
            zl_error('Not an essay type of quiz. Unable to save user response');
            redirect(site_url('quiz/view').'?id='.urlencode($this->id));
        }
    }

    public function grade_calculate() {
        $this->init_quiz('course_id, num_questions, essay');
        $this->ensure_role('instructor');

        $this->load->model('users_model', 'users');

        $user_id = $this->input->get('user_id');

        if (!empty($user_id)) {
            $user = $this->users->get($user_id, 'name');
            if (isset($user)) {
                if ($this->quizzes->calculate_grade($this->id, $user_id)) {
                    zl_success('Successfully calculated grade of <b>'.htmlspecialchars($user['name']).'</b> (ID: '.htmlspecialchars($user_id).')');
                    redirect(site_url('quiz/grades').'?id='.urlencode($this->id).'#UID-'.md5($user_id));
                } else {
                    zl_error('Unable to calculate grade of user: '.htmlspecialchars($user['name']).' (ID: '.$user_id.')');
                    redirect(site_url('quiz/grades').'?id='.urlencode($this->id));
                }
            } else {
                zl_error('Invalid user ID: '.htmlspecialchars($user_id));
                redirect(site_url('quiz/grades').'?id='.urlencode($this->id));
            }
        } else {
            $this->load->database();
            $responses = $this->quizzes->list_responses($this->id, $this->db->dbprefix('quiz_responses').'.user_id AS user_id');
            $count = count($responses);
            $success = 0;
            foreach ($responses as $response) {
                if ($this->quizzes->calculate_grade($this->id, $response['user_id'])) {
                    $success++;
                }
            }
            if ($count == $success) {
                zl_success('Successfully calculated all grades');
            } else {
                zl_warning('Not all grades calculated (only '.$success.' out of '.$count.' respondents)');
            }
            redirect(site_url('quiz/grades').'?id='.urlencode($this->id));
        }
    }

    public function grades() {
        $this->init_quiz('course_id, title, essay, show_leaderboard');

        $this->load->database();

        $grades = [];
        if (!empty($this->quiz['show_leaderboard']) || ($this->role === 'instructor')) {
            $grades = $this->quizzes->list_responses($this->id, $this->db->dbprefix('quiz_responses').'.user_id AS user_id, name, score', $this->role === 'participant');
        }

        $this->load->view('header', ['title' => $this->quiz['title'].' - Responses']);
        $this->load->view('quiz/grades', ['id' => $this->id, 'user_id' => $this->user_id, 'role' => $this->role, 'quiz' => $this->quiz, 'course' => $this->course, 'grades' => $grades]);
        $this->load->view('footer');
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
                    $this->quiz['duration'] = 0;
                    $this->quiz['num_questions'] = 0;
                    $this->quiz['questions_hash'] = '';
                    $this->quiz['essay'] = 0;
                    $this->quiz['mc_num_choices'] = 2;
                    $this->quiz['show_grades'] = 0;
                    $this->quiz['show_leaderboard'] = 0;
                    $this->quiz['hash'] = '';

                    if (!empty($this->input->post('submit'))) {
                        $this->load->library('form_validation');

                        $this->init_quiz_manager($this->form_validation);

                        if ($this->form_validation->run()) {
                            $this->quiz['course_id'] = $course_id;
                            $this->quiz['title'] = $this->input->post('title');
                            $this->quiz['description'] = $this->input->post('description');
                            $this->quiz['duration'] = $this->input->post('duration');
                            $this->quiz['num_questions'] = $this->input->post('num_questions');
                            $this->quiz['questions_hash'] = '';
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
                            $this->quiz['locked'] = !empty($this->input->post('locked'));

                            if ((($this->id = $this->quizzes->add($this->quiz)) !== NULL) && $this->quizzes->rehash($this->id)) {
                                if (($pdf_hash = $this->upload_pdf_file()) !== FALSE) {
                                    if ($this->quizzes->set($this->id, ['questions_hash' => $pdf_hash]) && $this->quizzes->rehash($this->id)) {
                                        zl_success('Quiz <b>'.htmlspecialchars($this->quiz['title']).'</b> added successfully to <b>'.$course['title'].'</b> course');
                                        redirect(site_url('quiz/view').'?id='.urlencode($this->id));
                                    } else {
                                        zl_error('Failed updating the PDF file. Please try again later');
                                    }
                                } else {
                                    zl_error('Failed uploading the PDF file. Please try again later');
                                }
                            } else {
                                zl_error('Failed to create quiz');
                            }
                        } else {
                            zl_error($this->form_validation->error_string());
                        }

                        $this->quiz['title'] = set_value('title');
                        $this->quiz['description'] = set_value('description');
                        $this->quiz['duration'] = set_value('duration');
                        $this->quiz['num_questions'] = set_value('num_questions');
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
                redirect('course/listing');
            }
        } else {
            zl_error('Invalid operation');
            redirect('course/listing');
        }
    }

    public function edit() {
        $this->init_quiz('course_id, title, description, duration, num_questions, questions_hash, essay, mc_num_choices, mc_answers, show_grades, show_leaderboard, locked');
        $this->ensure_role('instructor');

        if (!empty($this->input->post('submit'))) {
            $this->load->library('form_validation');

            $this->init_quiz_manager($this->form_validation);

            if ($this->form_validation->run()) {
                $num_questions = $this->quiz['num_questions'];
                $essay = !empty($this->quiz['essay']);
                $mc_num_choices = $this->quiz['mc_num_choices'];

                $this->quiz['title'] = $this->input->post('title');
                $this->quiz['description'] = $this->input->post('description');
                $this->quiz['num_questions'] = $this->input->post('num_questions');
                $this->quiz['duration'] = $this->input->post('duration');
                $this->quiz['essay'] = !empty($this->input->post('essay'));
                $this->quiz['mc_num_choices'] = $this->input->post('mc_num_choices');
                $this->quiz['show_grades'] = !empty($this->input->post('show_grades'));
                $this->quiz['show_leaderboard'] = !empty($this->input->post('show_leaderboard'));
                $this->quiz['locked'] = !empty($this->input->post('locked'));

                $reset_answers = ($this->quiz['num_questions'] != $num_questions) || (!empty($this->quiz['essay']) === !$essay) || (empty($this->quiz['essay']) && ($this->quiz['mc_num_choices'] != $mc_num_choices)) ? $this->quizzes->update_response_data($this->id, !empty($this->quiz['essay']), $this->quiz['num_questions']) : TRUE;

                if ($reset_answers) {
                    $this->quiz['mc_answers'] = unserialize($this->quiz['mc_answers']);
                    for ($i = 1; $i <= $this->quiz['num_questions']; $i++) {
                        $this->quiz['mc_answers'][$i] = 0;
                    }
                    $this->quiz['mc_answers'] = serialize($this->quiz['mc_answers']);
                }

                if ($this->quizzes->set($this->id, $this->quiz) && $this->quizzes->rehash($this->id)) {
                    if (($pdf_hash = $this->upload_pdf_file()) !== FALSE) {
                        if ($this->quizzes->set($this->id, ['questions_hash' => $pdf_hash]) && $this->quizzes->rehash($this->id)) {
                            if ($reset_answers) {
                                zl_success('Quiz updated successfully');
                                redirect(site_url('quiz/view').'?id='.urlencode($this->id));
                            } else {
                                zl_error('Failed updating user responses. Please try again later');
                            }
                        } else {
                            zl_error('Failed updating the PDF file. Please try again later');
                        }
                    } else {
                        zl_error('Failed uploading the PDF file. Please try again later');
                    }
                } else {
                    zl_error('Failed updating the quiz');
                }
            } else {
                zl_error($this->form_validation->error_string());
            }

            $this->quiz['title'] = set_value('title');
            $this->quiz['description'] = set_value('description');
            $this->quiz['duration'] = set_value('duration');
            $this->quiz['num_questions'] = set_value('num_questions');
            $this->quiz['essay'] = !empty(set_value('essay'));
            $this->quiz['mc_num_choices'] = set_value('mc_num_choices');
            $this->quiz['show_grades'] = !empty(set_value('show_grades'));
            $this->quiz['show_leaderboard'] = !empty(set_value('show_leaderboard'));
            $this->quiz['locked'] = !empty(set_value('locked'));
        }

        $this->load->view('header', ['title' => 'Edit Quiz']);
        $this->load->view('quiz/manager', ['action' => 'edit', 'id' => $this->id, 'course' => $this->course, 'quiz' => $this->quiz]);
        $this->load->view('footer');
    }

    public function configure() {
        $this->init_quiz('course_id, title, num_questions, essay, mc_num_choices, mc_score_correct, mc_score_incorrect, mc_score_empty, mc_answers');
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
            }
        }

        $this->load->view('header', ['title' => 'Configure Quiz']);
        $this->load->view('quiz/configure', ['id' => $this->id, 'course' => $this->course, 'quiz' => $this->quiz]);
        $this->load->view('footer');
    }

    public function delete() {
        $this->init_quiz('course_id, title');
        $this->ensure_role('instructor');

        if ($this->quizzes->remove($this->id)) {
            if ($this->storage->remove('quiz/'.$this->id)) {
                zl_success('Successfully removed quiz: '.$this->quiz['title']);
                redirect(site_url('course/view').'?id='.urlencode($this->quiz['course_id']));
            } else {
                zl_error('Failed to remove quiz files');
            }
        } else {
            zl_error('Failed to remove quiz: '.$this->quiz['title']);
        }
        redirect(site_url('quiz/view').'?id='.urlencode($this->id));
    }

    public function viewpdf() {
        $this->init_quiz('course_id');
        $this->ensure_role('instructor');

        $pdf_token = $this->auth->create_token(['quiz_id' => $this->id], 10);
        $pdf_url = site_url('quiz/pdf').'?token='.urlencode($pdf_token);

        redirect(site_url('plugins/pdf_viewer').'?src='.urlencode($pdf_url));
    }

    public function pdf() {
        $token = $this->input->get('token');
        $token = $this->auth->extract_token($token, ['quiz_id']);
        if (isset($token)) {
            $resource = $this->storage->get('quiz/'.$token['quiz_id']);
            if (isset($resource) && !empty($resource['metadata']['type'])) {
                $this->output->set_status_header(200);
                $this->output->set_content_type($resource['metadata']['type']);
                $this->output->set_output($resource['contents']);
            } else {
                $this->output->set_status_header(404);
            }
        } else {
            $this->output->set_status_header(403);
        }
    }

    protected function init_quiz($columns = '*') {
        $this->id = $this->input->get('id');

        $this->quiz = $this->quizzes->get($this->id, $columns);
        if ($this->quiz === NULL) {
            zl_error('Quiz not found');
            redirect('course/listing');
        }

        // must include `course_id` column or error otherwise
        $this->course = $this->courses->get($this->quiz['course_id'], 'title');
        if ($this->course === NULL) {
            zl_error('Invalid course');
            redirect('course/listing');
        }

        $this->role = $this->courses->get_role($this->quiz['course_id'], $this->user_id);
        if (!isset($this->role)) {
            zl_error('You have no access to view this quiz');
            redirect('course/listing');
        }
    }

    protected function init_quiz_manager(&$form_validation) {
        $form_validation->set_rules('title', 'Title', 'required|max_length[100]');
        $form_validation->set_rules('description', 'Description', 'required|max_length[1000]');
        $form_validation->set_rules('duration', 'Duration', 'required|is_natural');
        $form_validation->set_rules('num_questions', 'Number of questions', 'required|is_natural_no_zero');
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

    protected function upload_pdf_file() {
        $blob_path = $this->storage->blob_path('quiz/'.$this->id);

        if ($_FILES['questions_pdf']['size'] > 0) {
            if ($_FILES['questions_pdf']['size'] <= 10485760) {
                $contents = file_get_contents($_FILES['questions_pdf']['tmp_name']);
                $metadata = ['type' => mime_content_type($_FILES['questions_pdf']['tmp_name'])];
                if (($metadata['type'] === 'application/pdf') && $this->storage->put('quiz/'.$this->id, $contents, $metadata)) {
                    return is_readable($blob_path) ? hash_file('md5', $blob_path) : FALSE;
                }
            }
            return FALSE;
        }
        return is_readable($blob_path) ? hash_file('md5', $blob_path) : FALSE;
    }

}

