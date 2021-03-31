<?php
defined('BASEPATH') OR exit;

class Material_model extends CI_Model {

    public function __construct() {
        $this->load->database();
    }

    public function add($data) {
        $id = '';
        do {
            $id = $data['course_id'].random_string('alnum', 8);
        } while ($this->get($id, 'material_id') !== NULL);

        $data['material_id'] = $id;

        return $this->db->insert('materials', $data) ? $id : FALSE;
    }

    public function get($id, $columns = '*') {
        return $this->db->select($columns)->from('materials')->where('material_id', $id)->get()->row_array(0);
    }

    public function set($id, $data) {
        $this->db->where('material_id', $id);
        $this->db->update('materials', $data);
        return $this->db->affected_rows() != 0;
    }

    public function remove($id) {
        $this->db->where('material_id', $id);
        return $this->db->delete('materials') !== NULL;
    }

}

