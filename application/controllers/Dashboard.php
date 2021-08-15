<?php
defined('BASEPATH') OR exit;

class Dashboard extends CI_Controller {

    public function __construct() {
        parent::__construct();

        $this->load->model('users_model', 'users');
        $this->load->model('course_model', 'courses');
    }

    public function index() {
        $this->auth->check();

        $this->load->database();

        $user = $this->users->get(zl_session_get('user_id'), 'name, email');
        $courses = $this->courses->list_member_courses(zl_session_get('user_id'), $this->db->dbprefix('course_members').'.course_id, title, metadata, instructor', '', 0, 3);

        $this->load->view('header', ['title' => 'Dashboard']);
        $this->load->view('dashboard/index', ['user' => $user, 'courses' => $courses]);
        $this->load->view('footer');
    }

}

