<?php
defined('BASEPATH') or exit;

class Users_model extends CI_Model {

    public function __construct() {
        $this->load->database();
    }

    public function add($data) {
        return $this->db->insert('users', $data);
    }

    public function get($id, $columns = '*') {
        return $this->db->select($columns)->from('users')->where('user_id', $id)->get()->row_array(0);
    }

    public function set($id, $data) {
        $this->db->where('user_id', $id);
        return $this->db->update('users', $data);
    }

    public function remove($id) {
        $this->db->where('user_id', $id)->delete('quiz_responses');
        $this->db->where('user_id', $id)->delete('course_members');

        return $this->db->where('user_id', $id)->delete('users') !== FALSE;
    }

}

