<?php

class IndexController extends \com\BaseController {

    public function index() {
        
        $this->log->debug("Inside " . __METHOD__ . "()...");

        // SET TITLE
        $this->template->title = SITE_TITLE;

        // INCLUDE CSS
        $this->template->css = (array) 'global';

        // OPTIONAL: SET THE LAYOUT
        $this->template->setLayout('default');
        
        // RENDER VIEW FILE
        $this->template->show();

        $this->log->debug("Peak memory usage in " . __METHOD__ . " = " . (memory_get_peak_usage(TRUE) / 1024) . " KB");
    }

}

?>