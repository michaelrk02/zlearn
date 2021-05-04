<?php
defined('BASEPATH') OR exit;

class Auth {

    protected $ci;

    public $user;

    public function __construct() {
        $this->ci =& get_instance();

        $this->ci->load->library('session');
    }

    public function check() {
        if (zl_session_get('user_id') === NULL) {
            redirect('authentication/login');
        }
        if (zl_session_get('incomplete') !== NULL) {
            redirect('authentication/complete');
        }

        $this->ci->load->model('users_model', 'users');

        $this->user = $this->ci->users->get($this->id());
        if ($this->user === NULL) {
            $this->unauthorize();
            zl_error('User not found. Logged out automatically');
            redirect('authentication/login');
        }
    }

    public function authorize($user_id) {
        $this->ci->load->model('users_model', 'users');

        $name = 'Unknown';
        $user = $this->ci->users->get($user_id, 'name');
        if (isset($user)) {
            $name = $user['name'];
        }

        zl_session_set('user_id', $user_id);
        zl_session_set('name', $name);
    }

    public function unauthorize() {
        zl_session_set('user_id', NULL);
        zl_session_set('name', NULL);
        zl_session_set('sso', NULL);
        zl_session_set('incomplete', NULL);
        zl_session_set('tmp_name', NULL);
        zl_session_set('tmp_email', NULL);
    }

    public function id() {
        return zl_session_get('user_id');
    }

    public function sso($user_id) {
        return 'sso:'.md5($user_id);
    }

    public function sso_url($redirect = NULL, $param = 'token') {
        if (!isset($redirect)) {
            $redirect = uri_string();
        }
        $redirect = site_url($redirect);

        $timestamp = time();
        $signature = hash_hmac('sha256', ZL_SSO_APP_ID.':'.$timestamp, ZL_SSO_APP_SECRET);
        return ZL_SSO_URL.'?app_id='.urlencode(ZL_SSO_APP_ID).'&timestamp='.urlencode($timestamp).'&signature='.urlencode($signature).'&redirect='.urlencode($redirect).'&param='.urlencode($param);
    }

    public function create_token($data, $lifetime = 86400) {
        $token = [];
        $token[0] = $data;
        $token[0]['__expires'] = time() + $lifetime;
        $token[0] = json_encode($token[0]);
        $token[0] = base64_encode($token[0]);
        $token[1] = hash_hmac('sha256', $token[0], ZL_SECRET_KEY);
        $token = implode(':', $token);
        $token = base64_encode($token);
        return $token;
    }

    public function extract_token($token, $keys = []) {
        if (empty($token)) {
            return NULL;
        }

        $token = base64_decode($token, TRUE);
        if ($token === FALSE) {
            return NULL;
        }

        $token = explode(':', $token);
        if (count($token) != 2) {
            return NULL;
        }

        if ($token[1] !== hash_hmac('sha256', $token[0], ZL_SECRET_KEY)) {
            return NULL;
        }

        $token[0] = base64_decode($token[0], TRUE);
        if ($token[0] === FALSE) {
            return NULL;
        }

        $token[0] = json_decode($token[0], TRUE);
        if ($token[0] === NULL) {
            return NULL;
        }

        $keys[] = '__expires';
        foreach ($keys as $key) {
            if (!isset($token[0][$key])) {
                return NULL;
            }
        }

        if (time() > $token[0]['__expires']) {
            return NULL;
        }

        return $token[0];
    }

}

