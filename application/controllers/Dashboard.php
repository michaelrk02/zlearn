<?php
defined('BASEPATH') OR exit;

class Dashboard extends CI_Controller {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $this->auth->check();

        $this->load->view('header', ['title' => 'Dashboard']);
        $this->load->view('dashboard/index');
        $this->load->view('footer');
    }

}

