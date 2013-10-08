<?php
namespace com;

/**
 * @copyright (c) 2011-13, Magesh Ravi
 */
class Router {

    private $path;

    private $args = array();

    public $file;

    public $controller;

    public $action;

    /**
    *
    * @set controller directory path
    *
    * @param string $path
    *
    * @return void
    *
    */
    function setPath($path) {
        // CHECK IF PATH IS DIRECTORY
        if (is_dir($path) == false) {
            throw new Exception ('Invalid controller path: `' . $path . '`');
        }

        // SET PATH
        $this->path = $path;
    }

    /**
     *
     * @param Registry $registry to store the controller and action values
     */
    public function loader(Registry &$registry) {
        
        $internalRequest = FALSE;

        if($registry->getRequestType() === 'internal')
            $internalRequest = TRUE;
        
        if($internalRequest)
            $this->resolveFilePath();
        else
            $this->getControllerAndAction(); // for web requests
        
        // is controller file readable?
        if (is_readable($this->file) == false) {
            $this->file = $this->path.'/Error404.php';
            $this->controller = 'Error404';
        }

        // include the controller file
        include $this->file;

        // new instance of controller
        $class = $this->controller . 'Controller';
        Log::debug("Controller class: ".$class);
        $fullyQualifiedClassName = APP_NAMESPACE.'\\controller\\'.$class;
        $controller = new $fullyQualifiedClassName($registry);

        $action = $this->action.'Action';
        
        // what if the action is not callable?
        if (is_callable(array($controller, $action)) === false) {
            $action = 'indexAction';
            $this->action = 'index';
        }
        
        // store in registry, for use in template
        $registry->controller = $this->controller;
        $registry->action = $this->action;

        // init, then run
        $controller->init();
        $controller->$action();
    }

    private function getControllerAndAction() {
        // GET ROUTE FROM URL
        $route = (empty($_GET['rt'])) ? '' : $_GET['rt'];

        if (empty($route)) {
            $route = 'Index';
        } else {
            // GET PARTS OF ROUTE
            $parts = explode('/', $route);

            if(isset($parts[0])) {
                // CONTROLLER
                $this->controller = ucfirst($parts[0]);
            }

            if(isset( $parts[1])) {
                // ACTION
                $this->action = str_replace('-','',$parts[1]);  /* Ignore hyphens in url */
            }

            $partsCount = count($parts);

            for($i=2; $i < $partsCount; $i=$i+2) {
                // GET PARAMETERS
                if(array_key_exists($i+1, $parts))
                    $_GET[$parts[$i]] = $parts[($i+1)];
                else
                    $_GET[$parts[$i]] = "";

                Log::debug("GET Parameter : {$parts[$i]} = {$_GET[$parts[$i]]}");
            }
        }
        Log::debug("Route: ".$route);

        $this->resolveFilePath();
    }

    private function resolveFilePath() {
        if (empty($this->controller)) {
            $this->controller = 'Index';
        }

        if (empty($this->action)) {
            $this->action = 'index';
        }
        
        Log::debug("Controller: ".$this->controller);
        Log::debug("Action: ".$this->action);

        $this->file = $this->path .'/'. $this->controller . 'Controller.php';
    }
}

?>