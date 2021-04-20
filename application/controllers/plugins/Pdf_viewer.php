<?php
defined('BASEPATH') OR exit;

class Pdf_viewer extends CI_Controller {

    public function index() {
        $src = $this->input->get('src');

        if (!empty($src)) {
            redirect(base_url('public/pdfjs/web/viewer.html').'?file='.urlencode($src));
        } else {
            $this->output->set_status_header(400);
        }
    }

}

