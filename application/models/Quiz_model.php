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

    /*** Quiz responses ***/

    public function init_response($id, $user_id, $essay, $num_questions) {
        if ($this->db->select('user_id')->from('quiz_responses')->where(['quiz_id' => $id, 'user_id' => $user_id])->count_all_results() == 0) {
            $response = [];
            $response['quiz_id'] = $id;
            $response['user_id'] = $user_id;

            $response['data'] = [];
            for ($i = 1; $i <= $num_questions; $i++) {
                $response['data'][$i] = $essay ? '' : 0;
            }
            $response['data'] = serialize($response['data']);

            $response['score'] = 0;
            $response['comment'] = '';

            return $this->db->insert('quiz_responses', $response);
        }
        return TRUE;
    }

    public function get_response($id, $user_id, $question_no = NULL) {
        $data = $this->db->select('data')->from('quiz_responses')->where(['quiz_id' => $id, 'user_id' => $user_id])->get()->row_array(0);
        if (isset($data)) {
            $data = unserialize($data['data']);
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

    public function put_response($id, $user_id, $question_no, $value) {
        $data = $this->get_response($id, $user_id);
        if (isset($data)) {
            $data = $data['data'];
            $data[$question_no] = $value;
            $data = serialize($data);
            return $this->db->where(['quiz_id' => $id, 'user_id' => $user_id])->update('quiz_responses', ['data' => $data]);
        }
        return FALSE;
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

