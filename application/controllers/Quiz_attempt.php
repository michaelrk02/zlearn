<?php
defined('BASEPATH') OR exit;

class Quiz_attempt extends CI_Controller {

    protected $user_id;
    protected $quiz_id;
    protected $quiz;
    protected $role;

    protected $request = [];
    protected $response = [];

    public function __construct() {
        parent::__construct();

        $this->load->model('quiz_model', 'quizzes');

        $this->quiz_id = $this->input->get('quiz_id');
        if (empty($this->quiz_id)) {
            $this->output->set_status_header(400);
            exit;
        }

        $this->quiz = $this->quizzes->get($this->quiz_id, 'course_id, locked');
        if (!isset($this->quiz)) {
            $this->output->set_status_header(404, 'Quiz not found');
            exit;
        }

        if (!empty($this->quiz['locked'])) {
            $this->output->set_status_header(403, 'This quiz has been locked by the instructor. Please refresh the page');
            exit;
        }

        $this->user_id = $this->authorize_attempt();
        if (!isset($this->user_id)) {
            $this->output->set_status_header(403, 'Invalid authorization token. Try refreshing the page');
            exit;
        }

        $this->load->model('course_model', 'courses');

        $this->role = $this->courses->get_role($this->quiz['course_id'], $this->user_id);
        if ($this->role !== 'participant') {
            $this->output->set_status_header(403, 'Unsupported role');
            exit;
        }

        if ($this->input->get_request_header('Content-Type') === 'application/json') {
            $this->request = json_decode($this->input->raw_input_stream, TRUE);
        }
    }

    public function get_response() {
        $question_no = isset($this->request['question_no']) ? $this->request['question_no'] : NULL;

        $response = $this->quizzes->get_response($this->quiz_id, $this->user_id, $question_no);
        if (isset($response)) {
            $this->output->set_status_header(200);
            $this->response = $response;
        } else {
            $this->output->set_status_header(404);
        }
        $this->respond();
    }

    public function put_response() {
        $question_no = $this->request['question_no'];
        $data = $this->request['data'];

        if (!empty($question_no) && isset($data)) {
            if ($this->quizzes->put_response($this->quiz_id, $this->user_id, $question_no, $data)) {
                $this->output->set_status_header(200);
            } else {
                $this->output->set_status_header(500);
            }
        } else {
            $this->output->set_status_header(400);
        }
        $this->respond();
    }

    protected function authorize_attempt() {
        $token = $this->input->get_request_header('X-ZLEARN-Attempt-Token');
        if (!empty($token)) {
            $token = base64_decode($token);
            $token = explode(':', $token);
            if (hash_hmac('sha256', $token[0], ZL_SECRET_KEY) === $token[1]) {
                $token[0] = base64_decode($token[0]);
                $token[0] = unserialize($token[0]);
                if (time() < $token[0]['expired']) {
                    return $token[0]['user_id'];
                }
            }
        }
        return NULL;
    }

    protected function respond() {
        $this->output->set_content_type('application/json');
        $this->output->set_output(json_encode($this->response));
    }

}

