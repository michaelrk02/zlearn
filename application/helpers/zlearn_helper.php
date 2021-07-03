<?php
defined('BASEPATH') OR exit;

function zl_session_get($key) {
    //$key = ZL_SESS_PREFIX.$key;
    if (isset($_SESSION[$key])) {
        return $_SESSION[$key];
    }
    return NULL;
}

function zl_session_set($key, $value) {
    //$key = ZL_SESS_PREFIX.$key;
    if (isset($value)) {
        $_SESSION[$key] = $value;
    } else {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }
}

function zl_is_logged_in() {
    return zl_session_get('user_id') !== NULL;
}

function zl_success($message) {
    zl_session_set('status', ['severity' => 'success', 'message' => $message]);
}

function zl_warning($message) {
    zl_session_set('status', ['severity' => 'warning', 'message' => $message]);
}

function zl_error($message) {
    zl_session_set('status', ['severity' => 'error', 'message' => $message]);
}

function zl_status($keep = FALSE) {
    $status = zl_session_get('status');
    $message = '<div></div>';
    if (isset($status)) {
        $class = $status['severity'] === 'success' ? 'alert-success' : 'alert-danger';
        $message = '<div class="alert '.$class.' alert-dismissible fade show" role="alert">'.$status['message'].'<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    }
    if (!$keep) {
        zl_session_set('status', NULL);
    }
    return $message;
}

