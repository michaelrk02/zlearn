<?php
defined('BASEPATH') OR exit;

class Authentication extends CI_Controller {

    public function __construct() {
        parent::__construct();
    }

    public function login() {
        $this->load->model('users_model', 'users');

        if (!empty($this->input->post('submit'))) {
            $user_id = $this->input->post('user_id');
            $password = $this->input->post('password');

            $user = $this->users->get($user_id, 'password, name');
            if (isset($user) && password_verify($password, $user['password'])) {
                $this->auth->authorize($user_id);
                zl_success('Login successful. Hello, <b>'.$user['name'].'</b>!');
                redirect('dashboard');
            } else {
                zl_error('Invalid user ID or password');
            }
        }

        $this->load->view('header', ['title' => 'Login']);
        $this->load->view('authentication/login');
        $this->load->view('footer');
    }

    public function logout() {
        $this->auth->unauthorize();
        zl_success('Logged out successfully');
        redirect('authentication/login');
    }

    public function register() {
        if (!empty($this->input->post('submit'))) {
            $this->load->model('config_model', 'cfg');

            if (!empty($this->cfg->get('enable_app_registration'))) {
                $this->load->library('form_validation');

                $this->form_validation->set_rules('user_id', 'User ID', 'required|max_length[50]|alpha_dash');
                $this->form_validation->set_rules('password', 'Password', 'required|min_length[8]|max_length[72]');
                $this->form_validation->set_rules('password_confirm', 'Password confirmation', 'required|matches[password]');
                $this->form_validation->set_rules('name', 'Full name', 'required|max_length[100]');
                $this->form_validation->set_rules('email', 'E-mail address', 'required|valid_email|max_length[254]');

                if ($this->form_validation->run()) {
                    $this->load->model('users_model', 'users');

                    $user = [];
                    $user['user_id'] = $this->input->post('user_id');

                    if ($this->users->get($user['user_id']) === NULL) {
                        $user['password'] = password_hash($this->input->post('password'), PASSWORD_BCRYPT, ['cost' => 5]);
                        $user['name'] = $this->input->post('name');
                        $user['email'] = $this->input->post('email');
                        $user['hash'] = md5($this->input->ip_address());
                        $user['allow_course_management'] = 0;

                        if ($this->users->add($user)) {
                            zl_success('You have been successfully registered! Use your credentials to login');
                            redirect('authentication/login');
                        } else {
                            zl_error('Unable to register at this moment. Please try again later');
                        }
                    } else {
                        zl_error('User ID <b>'.$user['user_id'].'</b> is already taken by someone else');
                    }
                } else {
                    zl_error($this->form_validation->error_string());
                }
            } else {
                zl_error('Normal registration is disabled at this time. Try logging in using <b>'.ZL_SSO_NAME.'</b> account');
            }
        }

        $this->load->view('header', ['title' => 'Register']);
        $this->load->view('authentication/register');
        $this->load->view('footer');
    }

    public function sso_login() {
        if (zl_is_logged_in() && empty(zl_session_get('incomplete'))) {
            redirect('dashboard');
        }

        if (!empty($this->input->get('token'))) {
            $token = base64_decode($this->input->get('token'));
            $token = explode(':', $token);
            if (hash_hmac('sha256', $token[0], ZL_SSO_APP_SECRET) === $token[1]) {
                $token[0] = base64_decode($token[0]);
                $token[0] = json_decode($token[0], TRUE);
                if (time() < $token[0]['expired']) {
                    $this->load->model('users_model', 'users');

                    $user_id = $this->auth->sso($token[0]['user_id']);
                    $user = $this->users->get($user_id, 'name,password');
                    if (isset($user)) {
                        $this->auth->authorize($user_id);
                        zl_session_set('sso', TRUE);
                        zl_success('SSO login successful. Hello, <b>'.$user['name'].'</b>!');
                        redirect('dashboard');
                    } else {
                        if (empty($token[0]['disallow'])) {
                            $this->load->model('config_model', 'cfg');

                            if (!empty($this->cfg->get('enable_sso_registration'))) {
                                $this->auth->authorize($user_id);
                                zl_session_set('sso', TRUE);
                                zl_session_set('incomplete', TRUE);
                                zl_session_set('tmp_name', isset($token[0]['name']) ? $token[0]['name'] : '');
                                zl_session_set('tmp_email', isset($token[0]['email']) ? $token[0]['email'] : '');

                                zl_success('SSO login successful. But please complete your identity first');

                                redirect(site_url('authentication/complete'));
                            } else {
                                zl_error('SSO registration is disabled at this time');
                                redirect('authentication/login');
                            }
                        } else {
                            zl_error('Your SSO user ID (<b>'.$token[0]['user_id'].'</b>) is not allowed to register for this application');
                            redirect('authentication/login');
                        }
                    }
                } else {
                    zl_error('SSO token is expired. This may due to slow network connection or high amount of traffic. Please try logging in again');
                    redirect('authentication/login');
                }
            } else {
                zl_error('Invalid SSO token');
                redirect('authentication/login');
            }
        } else {
            redirect($this->auth->sso_url());
        }
    }

    public function complete() {
        if (empty(zl_session_get('incomplete'))) {
            redirect('dashboard');
        }

        if (!empty($this->input->post('submit'))) {
            $this->load->library('form_validation');

            $this->form_validation->set_rules('name', 'Name', 'required|max_length[100]');
            $this->form_validation->set_rules('email', 'E-mail', 'required|valid_email|max_length[254]');

            if ($this->form_validation->run()) {
                $this->load->model('users_model', 'users');

                $user = [];
                $user['user_id'] = $this->auth->id();
                $user['password'] = ''; // leave password blank for SSO registrations
                $user['name'] = $this->input->post('name');
                $user['email'] = $this->input->post('email');
                $user['hash'] = md5($this->input->ip_address());
                $user['allow_course_management'] = 0;

                if ($this->users->add($user)) {
                    zl_session_set('incomplete', NULL);
                    zl_session_set('tmp_name', NULL);
                    zl_session_Set('tmp_email', NULL);
                    zl_session_set('name', $user['name']);
                    zl_success('Successfully completed user account setup');
                    redirect('dashboard');
                } else {
                    zl_error('Cannot update user identity. Please try again');
                }
            } else {
                zl_error($this->form_validation->error_string());
            }
        }

        $this->load->view('header', ['title' => 'Complete Registration']);
        $this->load->view('authentication/complete');
        $this->load->view('footer');
    }

}

