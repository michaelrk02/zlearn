<?php
defined('BASEPATH') OR exit;

class Course_model extends CI_Model {

    public function __construct() {
        $this->load->database();
    }

    /*** Course management ***/

    public function add($data) {
        $id = '';
        do {
            $id = random_string('alnum', 8);
        } while ($this->get($id, 'course_id') !== NULL);

        $data['course_id'] = $id;

        return $this->db->insert('courses', $data) ? $id : FALSE;
    }

    public function get($id, $columns = '*') {
        return $this->db->select($columns)->from('courses')->where('course_id', $id)->get()->row_array(0);
    }

    public function set($id, $data) {
        $this->db->where('course_id', $id);
        return $this->db->update('courses', $data);
    }

    public function remove($id) {
        $this->load->model('quiz_model', 'quizzes');

        $this->db->where('course_id', $id)->delete('course_members');
        $this->db->where('course_id', $id)->delete('materials');

        $quizzes = $this->db->select('quiz_id')->from('quizzes')->where('course_id', $id)->get()->result_array();
        foreach ($quizzes as $quiz) {
            $this->quizzes->remove($quiz['quiz_id']);
        }

        return $this->db->where('course_id', $id)->delete('courses') !== FALSE;
    }

    /*** Course-user relationship ***/

    public function add_member($course_id, $user_id, $instructor) {
        $relation = [];
        $relation['course_id'] = $course_id;
        $relation['user_id'] = $user_id;
        $relation['instructor'] = (int)!empty($instructor);

        return $this->db->insert('course_members', $relation);
    }

    public function remove_member($course_id, $user_id) {
        return $this->db->where(['course_id' => $course_id, 'user_id' => $user_id])->delete('course_members') !== FALSE;
    }

    public function list_course_members($course_id, $columns = '*', $offset = 0, $limit = NULL) {
        $this->db->select($columns, FALSE)->from('course_members')->where($this->db->dbprefix('course_members').'.course_id', $course_id);
        $this->db->join('users', $this->db->dbprefix('course_members').'.user_id = '.$this->db->dbprefix('users').'.user_id');
        $this->db->order_by('name');
        $this->db->offset($offset);
        if (isset($limit)) {
            return $this->db->limit($limit);
        }
        return $this->db->get()->result_array();
    }

    public function count_course_members($course_id) {
        return $this->db->select('course_id')->from('course_members')->where('course_id', $course_id)->count_all_results();
    }

    public function list_member_courses($user_id, $columns = '*', $filter = '', $offset = 0, $limit = NULL) {
        $this->db->select($columns, FALSE)->from('course_members')->where($this->db->dbprefix('course_members').'.user_id', $user_id)->like('title', $filter);
        $this->db->join('courses', $this->db->dbprefix('course_members').'.course_id = '.$this->db->dbprefix('courses').'.course_id');
        $this->db->order_by('title');
        $this->db->offset($offset);
        if (isset($limit)) {
            $this->db->limit($limit);
        }
        return $this->db->get()->result_array();
    }

    public function count_member_courses($user_id, $filter = '') {
        $this->db->select('user_id')->from('course_members')->where('user_id', $user_id);
        $this->db->join('courses', $this->db->dbprefix('course_members').'.course_id = '.$this->db->dbprefix('courses').'.course_id');
        $this->db->like('title', $filter);
        return $this->db->count_all_results();
    }

    public function get_role($course_id, $user_id) {
        $this->db->select('instructor')->from('course_members');
        $this->db->where(['course_id' => $course_id, 'user_id' => $user_id]);

        $relation = $this->db->get()->row_array(0);
        if (isset($relation)) {
            return !empty($relation['instructor']) ? 'instructor' : 'participant';
        }

        return NULL;
    }

    public function set_role($course_id, $user_id, $role) {
        if ($this->get_role($course_id, $user_id) === NULL) {
            return FALSE;
        }

        $this->db->where(['course_id' => $course_id, 'user_id' => $user_id]);
        return $this->db->update('course_members', ['instructor' => (int)($role === 'instructor')]);
    }

    /*** Course materials ***/

    public function count_materials($course_id, $filter = '') {
        return $this->db->select('material_id')->from('materials')->where('course_id', $course_id)->like('title', $filter)->count_all_results();
    }

    public function list_materials($course_id, $columns = '*', $filter = '', $order = NULL, $offset = 0, $limit = NULL) {
        $this->db->select($columns, FALSE)->from('materials')->where('course_id', $course_id)->like('title', $filter);

        $order = isset($order) ? $order : 'timestamp';
        if (($order === 'timestamp') || ($order === 'title')) {
            $direction = ($order === 'timestamp') ? 'DESC' : 'ASC';

            $this->db->order_by($order, $direction);
        }

        $this->db->offset($offset);
        if (isset($limit)) {
            $this->db->limit($limit);
        }

        return $this->db->get()->result_array();
    }

    /*** Course quizzes ***/

    public function count_quizzes($course_id, $filter = '') {
        return $this->db->select('quiz_id')->from('quizzes')->where('course_id', $course_id)->like('title', $filter)->count_all_results();
    }

    public function list_quizzes($course_id, $columns = '*', $filter = '', $offset = 0, $limit = NULL) {
        $this->db->select($columns)->from('quizzes')->where('course_id', $course_id)->like('title', $filter);
        $this->db->offset($offset);
        if (isset($limit)) {
            $this->db->limit($limit);
        }
        $this->db->order_by('locked, title', 'ASC');

        return $this->db->get()->result_array();
    }

    /*** Course grades ***/

    public function list_grades($id, $user_id = NULL, $sort_by_grade = FALSE) {
        if (isset($user_id)) {
            $this->db->select('course_id, '.$this->db->dbprefix('quizzes').'.quiz_id AS quiz_id, '.$this->db->dbprefix('quizzes').'.title AS quiz_title, score', FALSE);
        } else {
            $this->db->select('course_id, '.$this->db->dbprefix('quiz_responses').'.user_id AS user_id, name, SUM(score) AS score', FALSE);
        }
        $this->db->from('quiz_responses');
        $this->db->join('quizzes', $this->db->dbprefix('quiz_responses').'.quiz_id = '.$this->db->dbprefix('quizzes').'.quiz_id');
        if (isset($user_id)) {
            $this->db->where('user_id', $user_id);
            $this->db->where('course_id', $id);
            $this->db->where('show_grades', 1);
            $this->db->order_by('title', 'ASC');
        } else {
            $this->db->join('users', $this->db->dbprefix('quiz_responses').'.user_id = '.$this->db->dbprefix('users').'.user_id');
            $this->db->group_by('user_id');
            $this->db->having('course_id', $id);
            if ($sort_by_grade) {
                $this->db->order_by('score', 'DESC');
            } else {
                $this->db->order_by('name', 'ASC');
            }
        }
        return $this->db->get()->result_array();
    }

}

