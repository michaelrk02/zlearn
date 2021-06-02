<?php
defined('BASEPATH') OR exit;

class Material extends CI_Controller {

    protected $id;
    protected $material;
    protected $course;
    protected $user_id;
    protected $role;

    public function __construct() {
        parent::__construct();

        $this->auth->check();

        $this->load->model('course_model', 'courses');
        $this->load->model('material_model', 'materials');

        $this->user_id = zl_session_get('user_id');
    }

    public function index() {
        redirect('course/listing');
    }

    public function view() {
        $this->init_material('course_id, title, subtitle, contents, timestamp');

        $this->load->view('header', ['title' => $this->material['title'].' ('.$this->course['title'].')']);
        $this->load->view('material/view', ['id' => $this->id, 'role' => $this->role, 'material' => $this->material, 'course' => $this->course]);
        $this->load->view('footer');
    }

    public function add() {
        $course_id = $this->input->get('course_id');

        if (!empty($course_id)) {
            $course = $this->courses->get($course_id, 'title');

            if (isset($course)) {
                if ($this->courses->get_role($course_id, $this->user_id) === 'instructor') {
                    $this->material = [];
                    $this->material['title'] = '';
                    $this->material['subtitle'] = '';
                    $this->material['contents'] = '';

                    if (!empty($this->input->post('submit'))) {
                        $this->load->library('form_validation');

                        $this->init_material_manager($this->form_validation);

                        if ($this->form_validation->run()) {
                            $this->material['course_id'] = $course_id;
                            $this->material['title'] = $this->input->post('title');
                            $this->material['subtitle'] = $this->input->post('subtitle');
                            $this->material['contents'] = $this->input->post('contents');
                            $this->material['timestamp'] = time();

                            if (($id = $this->materials->add($this->material)) !== NULL) {
                                zl_success('Material <b>'.htmlspecialchars($this->material['title']).'</b> added successfully to <b>'.$course['title'].'</b> course');
                                redirect(site_url('material/view').'?id='.urlencode($id));
                            } else {
                                zl_error('Failed to create material');
                            }
                        } else {
                            zl_error($this->form_validation->error_string());
                        }

                        $this->material['title'] = set_value('title');
                        $this->material['subtitle'] = set_value('subtitle');
                        $this->material['contents'] = set_value('contents');
                    }

                    $this->material['course_id'] = $course_id;

                    $this->load->view('header', ['title' => 'Add Material']);
                    $this->load->view('material/manager', ['action' => 'add', 'course' => $course, 'material' => $this->material]);
                    $this->load->view('footer');
                } else {
                    zl_error('You must be an instructor to perform this action');
                    redirect(site_url('course/view').'?id='.urlencode($course_id));
                }
            } else {
                zl_error('Invalid course ID');
                redirect('course/listing');
            }
        } else {
            zl_error('Invalid operation');
            redirect('course/listing');
        }
    }

    public function edit() {
        $this->init_material('course_id, title, subtitle, contents');
        $this->ensure_role('instructor');

        if (!empty($this->input->post('submit'))) {
            $this->load->library('form_validation');

            $this->init_material_manager($this->form_validation);

            if ($this->form_validation->run()) {
                $this->material['title'] = $this->input->post('title');
                $this->material['subtitle'] = $this->input->post('subtitle');
                $this->material['contents'] = $this->input->post('contents');

                if ($this->materials->set($this->id, $this->material)) {
                    zl_success('Material updated successfully');
                    redirect(site_url('material/view').'?id='.urlencode($this->id));
                } else {
                    zl_error('Failed to update the material');
                }
            } else {
                zl_error($this->form_validation->error_string());
            }

            $this->material['title'] = set_value('title');
            $this->material['subtitle'] = set_value('subtitle');
            $this->material['contents'] = set_value('contents');
        }

        $this->load->view('header', ['title' => 'Edit Material: '.htmlspecialchars($this->material['title'])]);
        $this->load->view('material/manager', ['action' => 'edit', 'id' => $this->id, 'course' => $this->course, 'material' => $this->material]);
        $this->load->view('footer');
    }

    public function remove() {
        $this->init_material('title');
    }

    protected function init_material($columns = '*') {
        $this->id = $this->input->get('id');

        $this->material = $this->materials->get($this->id, $columns);
        if ($this->material === NULL) {
            zl_error('Material not found');
            redirect('course/listing');
        }

        // must include `course_id` column or error otherwise
        $this->course = $this->courses->get($this->material['course_id'], 'title');
        if ($this->course === NULL) {
            zl_error('Invalid course');
            redirect('course/listing');
        }

        $this->role = $this->courses->get_role($this->material['course_id'], $this->user_id);
        if (!isset($this->role)) {
            zl_error('You have no access to view this material');
            redirect('course/listing');
        }
    }

    protected function init_material_manager(&$form_validation) {
        $form_validation->set_rules('title', 'Title', 'required|max_length[100]');
        $form_validation->set_rules('subtitle', 'Subtitle', 'max_length[250]');
        $form_validation->set_rules('contents', 'Contents', 'required');
    }

    protected function ensure_role($role) {
        if ($this->role !== $role) {
            zl_error('You must be a(n) '.$role.' to perform this action');
            redirect(site_url('course/view').'?id='.urlencode($this->material['course_id']));
        }
    }

}

