<?php
namespace com;

/**
 * @copyright (c) 2011-13, Magesh Ravi
 */
abstract class BaseController {
    
    /** @var Template */
    protected $template;
    
    /** @var Log */
    protected $log;

    /**
     * 
     * @param Registry $registry to get the controller and action values
     */
    function __construct(Registry &$registry) {
        $this->template = new Template($registry);
        $this->log = Log::getInstance();
    }
    
    public function setTemplateViewFile($controller, $action) {
        $this->template->setViewFile($controller, $action);
    }

    abstract function init();
    abstract function indexAction();
}

?>