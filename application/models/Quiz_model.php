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
        } while ($this->get_quiz($id, 'quiz_id') !== NULL);

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

    /*** Quiz responses ***/

}

