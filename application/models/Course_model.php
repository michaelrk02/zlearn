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
        $this->db->where('course_id', $id)->delete('course_materials');

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

}

