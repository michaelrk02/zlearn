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

    /*** Quiz-user relationship ***/

    public function get_access($quiz_id, $user_id) {
        $quiz = $this->get_quiz($quiz_id, 'course_id');
        if (isset($quiz)) {
            $this->load->model('course_model');

            return $this->course_model->get_role($quiz['course_id'], $user_id);
        }
        return NULL;
    }

    public function count($user_id = NULL, $course_id = NULL, $filter = '') {
        $this->db->select('quiz_id')->from('quizzes');
        if (isset($course_id)) {
            $this->db->where('course_id', $course_id);
        }
        if (isset($user_id)) {
            $this->db->group_start();
            $this->db->where('locked', 0);
            $this->db->or_group_start();
            $this->db->where('locked', 1);
            $this->db->where('NOT EXISTS(SELECT user_id FROM '.$this->db->dbprefix('quiz_responses').' WHERE user_id = '.$this->db->escape($user_id).')', NULL, FALSE);
            $this->db->group_end();
            $this->db->group_end();
        }
        $this->db->like('title', $filter);
        return $this->db->count_all_results();
    }

    public function list($user_id = NULL, $columns = '*', $course_id = NULL, $filter = '', $offset = 0, $limit = NULL) {
        $this->db->select($columns)->from('quizzes');
        if (isset($course_id)) {
            $this->db->where('course_id', $course_id);
        }
        if (isset($user_id)) {
            $this->db->group_start();
            $this->db->where('locked', 0);
            $this->db->or_group_start();
            $this->db->where('locked', 1);
            $this->db->where('NOT EXISTS(SELECT user_id FROM '.$this->db->dbprefix('quiz_responses').' WHERE user_id = '.$this->db->escape($user_id).')', NULL, FALSE);
            $this->db->group_end();
            $this->db->group_end();
        }
        $this->db->like('title', $filter);
        $this->db->order_by('title');
        $this->db->offset($offset);
        if (isset($limit)) {
            $this->db->limit($limit);
        }
        return $this->db->get()->result_array();
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

