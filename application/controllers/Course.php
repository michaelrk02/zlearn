<?php
defined('BASEPATH') OR exit;

class Course extends CI_Controller {

    protected $id;
    protected $course;
    protected $user_id;
    protected $role;

    public function __construct() {
        parent::__construct();

        $this->auth->check();

        $this->load->model('course_model', 'courses');

        $this->user_id = zl_session_get('user_id');
        $this->id = $this->input->get('id');
    }

    public function index() {
        redirect('course/list');
    }

    public function list() {
        $filter = $this->input->get('filter');
        $filter = isset($filter) ? $filter : '';

        $display = $this->input->get('display');
        $display = isset($display) && is_numeric($display) ? max(1, (int)$display) : 25;

        $max_page = max(1, ceil($this->courses->count_member_courses($this->user_id, $filter) / $display));

        $page = $this->input->get('page');
        $page = isset($page) && is_numeric($page) ? max(1, min($max_page, (int)$page)) : 1;

        $courses = $this->courses->list_member_courses($this->user_id, ZL_DB_PREFIX.'courses.course_id AS course_id, title, metadata, instructor', $filter, ($page - 1) * $display, $display);

        $this->load->view('header', ['title' => 'My Courses']);
        $this->load->view('course/list', [
            'courses' => $courses,
            'allow_course_management' => !empty($this->auth->user['allow_course_management']),
            'filter' => $filter,
            'page' => $page,
            'max_page' => $max_page,
            'display' => $display
        ]);
        $this->load->view('footer');
    }

    public function view() {
        $this->init_course('title, metadata, description, allow_leave');

        $tab = $this->input->get('tab');
        $tab = isset($tab) ? $tab : 'materials';

        $data = [];
        if ($tab === 'materials') {
            $filter = $this->input->get('filter');
            $filter = isset($filter) ? $filter : '';

            $order = $this->input->get('order');
            if (($order !== 'timestamp') && ($order !== 'title')) {
                $order = 'timestamp';
            }

            $display = $this->input->get('display');
            $display = isset($display) && is_numeric($display) ? max(1, (int)$display) : 25;

            $max_page = max(1, ceil($this->courses->count_materials($this->id, $filter) / $display));

            $page = $this->input->get('page');
            $page = isset($page) && is_numeric($page) ? max(1, min($max_page, (int)$page)) : 1;

            $data['id'] = $this->id;
            $data['role'] = $this->role;
            $data['materials'] = $this->courses->list_materials($this->id, 'material_id, title, subtitle, timestamp', $filter, $order, ($page - 1) * $display, $display);
            $data['filter'] = $filter;
            $data['order'] = $order;
            $data['page'] = $page;
            $data['max_page'] = $max_page;
            $data['display'] = $display;
        } elseif ($tab === 'quizzes') {
            $filter = $this->input->get('filter');
            $filter = isset($filter) ? $filter : '';

            $display = $this->input->get('display');
            $display = isset($display) && is_numeric($display) ? max(1, (int)$display) : 25;

            $max_page = max(1, ceil($this->courses->count_quizzes($this->id, $filter) / $display));

            $page = $this->input->get('page');
            $page = isset($page) && is_numeric($page) ? max(1, min($max_page, (int)$page)) : 1;

            $data['id'] = $this->id;
            $data['role'] = $this->role;
            $data['quizzes'] = $this->courses->list_quizzes($this->id, 'quiz_id, title, num_questions, essay, locked', $filter, ($page - 1) * $display, $display);
            $data['filter'] = $filter;
            $data['page'] = $page;
            $data['max_page'] = $max_page;
            $data['display'] = $display;
        } elseif ($tab === 'members') {
            $data['id'] = $this->id;
            $data['members'] = $this->courses->list_course_members($this->id, ZL_DB_PREFIX.'course_members.user_id AS user_id, name, instructor');
            $data['allow_course_management'] = $this->auth->user['allow_course_management'];
        }

        $this->load->view('header', ['title' => $this->course['title']]);
        $this->load->view('course/view', [
            'id' => $this->id,
            'course' => $this->course,
            'allow_course_management' => $this->auth->user['allow_course_management'],
            'role' => $this->role,
            'tab' => $tab
        ]);
        $this->load->view('course/view/'.$tab, $data);
        $this->load->view('footer');
    }

    public function enroll() {
        if (!empty($this->input->post('submit'))) {
            $course_id = $this->input->post('course_id');
            $password = $this->input->post('password');

            $course = $this->courses->get($course_id, 'password, title');
            if (isset($course) && password_verify($password, $course['password'])) {
                if ($this->courses->get_role($course_id, $this->user_id) === NULL) {
                    if ($this->courses->add_member($course_id, $this->user_id, FALSE)) {
                        zl_success('Successfully registered for <b>'.$course['title'].'</b> course!');
                        redirect(site_url('course/view').'?id='.urlencode($course_id));
                    } else {
                        zl_error('Cannot enroll yourself to this course');
                    }
                } else {
                    zl_error('You are already in that course');
                }
            } else {
                zl_error('Invalid course ID or password');
            }
        }

        $this->load->view('header', ['title' => 'Enroll a Course']);
        $this->load->view('course/enroll');
        $this->load->view('footer');
    }

    public function leave() {
        $this->ensure_management(FALSE);

        $this->init_course('title, allow_leave');

        if ($this->courses->get_role($this->id, $this->user_id) !== NULL) {
            if (!empty($this->course['allow_leave'])) {
                if ($this->courses->remove_member($this->id, $this->user_id)) {
                    zl_success('Successfully left from <b>'.htmlspecialchars($this->course['title']).'</b>');
                    redirect('course/list');
                } else {
                    zl_error('Failed to leave this course');
                    redirect(site_url('course/view').'?id='.urlencode($this->id));
                }
            } else {
                zl_error('You cannot leave from this course');
                redirect(site_url('course/view').'?id='.urlencode($this->id));
            }
        } else {
            zl_error('You are not a member of this course');
            redirect('course/list');
        }
    }

    public function create() {
        $this->ensure_management();

        $this->course = [];
        $this->course['title'] = '';
        $this->course['metadata'] = '';
        $this->course['description'] = '';
        $this->course['allow_leave'] = 0;

        if (!empty($this->input->post('submit'))) {
            $this->load->library('form_validation');

            $this->init_course_manager($this->form_validation);

            if ($this->form_validation->run()) {
                $this->course['title'] = $this->input->post('title');
                $this->course['password'] = password_hash($this->input->post('password'), PASSWORD_BCRYPT, ['cost' => 5]);
                $this->course['metadata'] = $this->input->post('metadata');
                $this->course['description'] = $this->input->post('description');
                $this->course['allow_leave'] = (int)!empty($this->input->post('allow_leave'));

                if (($id = $this->courses->add($this->course)) !== FALSE) {
                    if ($this->courses->add_member($id, $this->user_id, TRUE)) {
                        zl_success('Successfully created course: <b>'.$this->course['title'].'</b>. Click on <b>Members</b> tab to add members');
                        redirect(site_url('course/view').'?id='.urlencode($id));
                    } else {
                        $this->courses->remove($id);
                        zl_error('Unexpected error when creating course (failed to add you as an instructor)');
                    }
                } else {
                    zl_error('Failed to create course');
                }
            } else {
                zl_error($this->form_validation->error_string());
            }

            $this->course['title'] = set_value('title');
            $this->course['metadata'] = set_value('metadata');
            $this->course['description'] = set_value('description');
            $this->course['allow_leave'] = (int)!empty(set_value('allow_leave'));
        }

        $this->load->view('header', ['title' => 'Course Creator']);
        $this->load->view('course/manager', ['action' => 'create', 'course' => $this->course]);
        $this->load->view('footer');
    }

    public function edit() {
        $this->ensure_management();
        $this->init_course('title, metadata, description, allow_leave');
        $this->ensure_role('instructor');

        if (!empty($this->input->post('submit'))) {
            $this->load->library('form_validation');

            $this->init_course_manager($this->form_validation);

            if ($this->form_validation->run()) {
                $this->course['title'] = $this->input->post('title');
                if (!empty($this->input->post('password'))) {
                    $this->course['password'] = password_hash($this->input->post('password'), PASSWORD_BCRYPT, ['cost' => 5]);
                }
                $this->course['metadata'] = $this->input->post('metadata');
                $this->course['description'] = $this->input->post('description');
                $this->course['allow_leave'] = (int)!empty($this->input->post('allow_leave'));

                if ($this->courses->set($this->id, $this->course)) {
                    zl_success('Course updated successfully');
                    redirect(site_url('course/view').'?id='.urlencode($this->id));
                } else {
                    zl_error('Failed to update course');
                }

                $this->course['password'] = '';
            } else {
                zl_error($this->form_validation->error_string());
            }

            $this->course['title'] = set_value('title');
            $this->course['metadata'] = set_value('metadata');
            $this->course['description'] = set_value('description');
            $this->course['allow_leave'] = (int)!empty(set_value('allow_leave'));
        }

        $this->load->view('header', ['title' => 'Edit Course: '.htmlspecialchars($this->course['title'])]);
        $this->load->view('course/manager', ['action' => 'edit', 'id' => $this->id, 'course' => $this->course]);
        $this->load->view('footer');
    }

    public function delete() {
        $this->ensure_management();
        $this->init_course('title');
        $this->ensure_role('instructor');

        if ($this->courses->remove($this->id)) {
            zl_success('Successfully deleted course: <b>'.$this->course['title'].'</b> (ID: <code>'.$this->id.'</code>)');
            redirect('course/list');
        } else {
            zl_error('Failed to delete course');
        }
        redirect(site_url('course/view').'?id='.urlencode($this->id));
    }

    public function set_role() {
        $this->ensure_management();
        $this->init_course('title');
        $this->ensure_role('instructor');

        $user_id = $this->input->get('user_id');
        $role = $this->input->get('role');
        if (!empty($user_id) && !empty($role) && (($role === 'instructor') || ($role === 'participant'))) {
            if ($this->courses->set_role($this->id, $user_id, $role)) {
                $this->load->model('users_model', 'users');

                $user = $this->users->get($user_id, 'name');
                zl_success('Successfully set the role of <b>'.$user['name'].'</b> to '.$role.' for this course');
                redirect(site_url('course/view').'?id='.urlencode($this->id).'&tab=members');
            } else {
                zl_error('Cannot set the role of the specified user ID for this course');
            }
        } else {
            zl_error('Invalid operation');
            redirect(site_url('course/view').'?id='.urlencode($this->id));
        }
    }

    public function remove_member() {
        $this->ensure_management();
        $this->init_course('title');
        $this->ensure_role('instructor');
    }

    protected function init_course($columns = '*') {
        if ($this->id === NULL) {
            zl_error('Please choose a course first');
            redirect('course/list');
        }

        $this->load->model('course_model', 'courses');

        $this->course = $this->courses->get($this->id, $columns);
        if ($this->course === NULL) {
            zl_error('Course not found');
            redirect('course/list');
        }

        $this->role = $this->courses->get_role($this->id, $this->user_id);
        if (!isset($this->role)) {
            zl_error('You have no access to this course');
            redirect('course/list');
        }
    }

    protected function init_course_manager(&$form_validation) {
        $form_validation->set_rules('title', 'Title', 'required|max_length[100]');
        $form_validation->set_rules('metadata', 'Metadata', 'max_length[250]');

        if ($this->input->post('action') === 'create') {
            $form_validation->set_rules('password', 'Password', 'required|min_length[8]|max_length[72]');
            $form_validation->set_rules('password_confirm', 'Password confirmation', 'required|matches[password]');
        } else {
            if (!empty($this->input->post('password'))) {
                $form_validation->set_rules('password', 'Password', 'min_length[8]|max_length[72]');
                $form_validation->set_rules('password_confirm', 'Password confirmation', 'required|matches[password]');
            }
        }
    }

    protected function ensure_role($role) {
        if ($this->role !== $role) {
            zl_error('You must be a(n) '.$role.' to perform this action');
            redirect(site_url('course/view').'?id='.$this->id);
        }
    }

    protected function ensure_management($manager = TRUE) {
        if (empty($this->auth->user['allow_course_management']) === $manager) {
            zl_error('You must '.(!$manager ? 'not ' : '').'be a manager to perform this action');
            redirect(site_url('course/list'));
        }
    }

}

