<?php
defined('BASEPATH') OR exit;

class Settings extends CI_Controller {

    public function __construct() {
        parent::__construct();

        $this->load->library('auth');

        $this->load->model('config_model', 'cfg');
        $this->load->model('users_model', 'users');
    }

    public function index() {
        $this->auth->check();

        $allow = !(zl_session_get('sso') && ZL_SSO_FIXED);

        $user = $this->users->get(zl_session_get('user_id'), 'name, email');

        if (!empty($this->input->post('submit'))) {
            if ($allow) {
                $this->load->library('form_validation');

                $user['name'] = $this->input->post('name');
                $user['email'] = $this->input->post('email');

                if (!empty($this->input->post('password'))) {
                    $this->form_validation->set_rules('password', 'Password', 'min_length[8]|max_length[72]');
                    $this->form_validation->set_rules('password_confirm', 'Password confirmation', 'matches[password]');
                }

                $this->form_validation->set_rules('name', 'Full name', 'required|max_length[100]');
                $this->form_validation->set_rules('email', 'E-mail address', 'required|valid_email|max_length[254]');

                if ($this->form_validation->run()) {
                    if (!empty($this->input->post('password'))) {
                        $user['password'] = password_hash($this->input->post('password'), PASSWORD_BCRYPT, ['cost' => 5]);
                    }

                    if ($this->users->set(zl_session_get('user_id'), $user)) {
                        $this->auth->authorize(zl_session_get('user_id'));
                        zl_success('Successfully updated account settings');
                    } else {
                        zl_error('Failed to update account settings. Please try again');
                    }
                } else {
                    zl_error($this->form_validation->error_string());
                }
            } else {
                zl_error('You are not allowed to change your account settings!');
            }
        }

        $this->load->view('header', ['title' => 'Account Settings']);
        $this->load->view('settings/index', ['user' => $user, 'allow' => $allow]);
        $this->load->view('footer');
    }

}

