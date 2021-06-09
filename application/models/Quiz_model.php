<?php
defined('BASEPATH') OR exit;

class Quiz_model extends CI_Model {

    public function __construct() {
        $this->load->database();
    }

    /*** Quiz management ***/

    public function add($data) {
        $id = '';
        do {
            $id = $data['course_id'].random_string('alnum', 8);
        } while ($this->get($id, 'quiz_id') !== NULL);

        $data['quiz_id'] = $id;

        return $this->db->insert('quizzes', $data) ? $id : FALSE;
    }

    public function get($id, $columns = '*') {
        return $this->db->select($columns)->from('quizzes')->where('quiz_id', $id)->get()->row_array(0);
    }

    public function set($id, $data) {
        $this->db->where('quiz_id', $id);
        return $this->db->update('quizzes', $data);
    }

    public function remove($id) {
        $this->db->where('quiz_id', $id)->delete('quiz_responses');

        return $this->db->where('quiz_id', $id)->delete('quizzes') !== FALSE;
    }

    public function rehash($id) {
        $quiz = $this->get($id, 'duration, num_questions, questions_hash, essay, mc_num_choices');
        $args = [];
        $args[] = (int)$quiz['duration'];
        $args[] = (int)$quiz['num_questions'];
        $args[] = $quiz['questions_hash'];
        $args[] = (int)$quiz['essay'];
        if (empty($quiz['essay'])) {
            $args[] = (int)$quiz['mc_num_choices'];
        }
        $args = serialize($args);
        $hash = md5($args);

        return $this->set($id, ['hash' => $hash]);
    }

    /*** Quiz responses ***/

    public function init_response($id, $user_id, $essay, $num_questions, $timestamp) {
        if ($this->db->select('user_id')->from('quiz_responses')->where(['quiz_id' => $id, 'user_id' => $user_id])->count_all_results() == 0) {
            $response = [];
            $response['quiz_id'] = $id;
            $response['user_id'] = $user_id;
            $response['timestamp'] = $timestamp;
            $response['data'] = '';
            $response['score'] = 0;
            $response['comment'] = '';

            if ($this->db->insert('quiz_responses', $response)) {
                $response['data'] = $this->init_response_data($essay, $num_questions);
                return $this->set_response_info($id, $user_id, $response);
            }
            return FALSE;
        }
        return TRUE;
    }

    public function init_response_data($essay, $num_questions) {
        $data = [];
        for ($i = 1; $i <= $num_questions; $i++) {
            $data[$i] = [$essay ? '' : 0, 0];
        }
        $data = serialize($data);
        return $data;
    }

    public function update_response_data($id, $essay, $num_questions) {
        $response = [];
        $response['data'] = $this->init_response_data($essay, $num_questions);
        return $this->db->where('quiz_id', $id)->update('quiz_responses', $response);
    }

    public function get_response_info($id, $user_id, $columns = '*') {
        return $this->db->select($columns)->from('quiz_responses')->where(['quiz_id' => $id, 'user_id' => $user_id])->get()->row_array(0);
    }

    public function set_response_info($id, $user_id, $data) {
        return $this->db->where(['quiz_id' => $id, 'user_id' => $user_id])->update('quiz_responses', $data);
    }

    public function get_response($id, $user_id, $question_no = NULL) {
        $data = $this->db->select('data')->from('quiz_responses')->where(['quiz_id' => $id, 'user_id' => $user_id])->get()->row_array(0);
        return $this->unserialize_response_data($data['data'], $question_no);
    }

    public function put_response($id, $user_id, $question_no, $value = NULL, $grade = NULL) {
        $data = $this->get_response($id, $user_id);
        if (isset($data)) {
            $data = $data['data'];
            if (isset($value)) {
                $data[$question_no][0] = $value;
            }
            if (isset($grade)) {
                $data[$question_no][1] = $grade;
            }
            $data = serialize($data);
            return $this->db->where(['quiz_id' => $id, 'user_id' => $user_id])->update('quiz_responses', ['data' => $data]);
        }
        return FALSE;
    }

    public function unserialize_response_data($data, $question_no) {
        if (isset($data)) {
            $data = unserialize($data);
            if (isset($question_no)) {
                if (isset($data[$question_no])) {
                    return ['data' => $data[$question_no]];
                } else {
                    return ['data' => NULL];
                }
            } else {
                return ['data' => $data];
            }
        }
        return NULL;
    }

    /*** Utilities ***/

    public function mc_transform($raw_answers) {
        $answers = [];
        $len = strlen($raw_answers);
        for ($i = 0; $i < $len; $i++) {
            $answers[$i] = (int)$raw_answers[$i];
        }
        return serialize($answers);
    }

}

