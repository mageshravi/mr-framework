<?php
namespace com;

/**
 * @copyright (c) 2011-13, Magesh Ravi
 */
abstract class BaseController {
    /** @var Request */
    protected $request;
    
    /** @var Template */
    protected $template;
    
    /**
     * 
     * @param Registry $registry to get the controller and action values
     */
    function __construct(Registry &$registry) {
        $this->request = new Request();
        $this->template = new Template($registry);
        
        // for web requests process params
        if($registry->getRequestType() !== 'internal')
            $this->request->processParams();
    }
    
    public function setTemplateViewFile($controller, $action) {
        $this->template->setViewFile($controller, $action);
    }

    abstract function init();
    abstract function indexAction();
}