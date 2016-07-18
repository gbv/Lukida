<?php

/**
 * A class to enable CodeIgniter to load PHP interfaces and abstract classes.
 *
 * @author Richard Großer <richard.grosser@thulb.uni-jena.de>
 */
class MY_Loader extends CI_Loader
{
    /**
    * List of paths to load interfaces from
    *
    * @var	array
    */
   protected $_ci_interface_paths = array(APPPATH, BASEPATH);
   
   /**
    * Loads a PHP interface from a file and makes it usable in the application 
    * 
    * @param    String  $interface  interface name to load
    * @return   ExtendedLoader  returns itself (fluent interface)
    */
   public function php_interface($interface = '')
   {
        if (empty($interface)) {
                return $this;
        } elseif (is_array($interface)) {
            foreach ($interface as $singleInterface) {
                $this->php_interface($singleInterface);
            }
            return $this;
        }

        $this->_ci_load_interface($interface);
        return $this;
   }
   
   /**
    * Loads a specified interface. Implementation is similar to _ci_load_library
    * but instantiation is ommited
    * 
    * @param    String  $interface  interface name to load
    * @return   void
    */
   protected function _ci_load_interface($interface)
   {
        // Get the interface name, and while we're at it trim any slashes.
        // The directory path can be included as part of the interface name,
        // but we don't want a leading slash
        $interface = str_replace('.php', '', trim($interface, '/'));

        // Was the path included with the interface name?
        // We look for a slash to determine this
        if (($last_slash = strrpos($interface, '/')) !== FALSE) {
            // Extract the path
            $subdir = substr($interface, 0, ++$last_slash);

            // Get the filename from the path
            $interface = substr($interface, $last_slash);
        } else {
            $subdir = '';
        }

        $interface = ucfirst($interface);
        
        foreach ($this->_ci_interface_paths as $path) {
            // BASEPATH has already been checked for
            if ($path === BASEPATH) {
                    continue;
            }

            $filepath = $path . 'interfaces/' . $subdir . $interface . '.php';

            // Safety: Was the interface already loaded by a previous call?
            if (interface_exists($interface, FALSE)) {
                log_message('debug', $interface . ' interface already loaded. Second attempt ignored.');
                return;
            } elseif ( ! file_exists($filepath)) {
                continue;
            }
            
            include_once($filepath);
        
            // One last attempt. Maybe the interface is in a subdirectory, but it wasn't specified?
            if ($subdir === '') {
                return $this->_ci_load_interface($interface . '/' . $interface);
            }
            
            // If we got this far we were unable to find the requested interface    .
            log_message('error', 'Unable to load the requested interface: ' . $interface);
            show_error('Unable to load the requested interface: ' . $interface);
        }
   }
   
   protected function _ci_autoloader() {
       parent::_ci_autoloader();
       
        if (defined('ENVIRONMENT') && file_exists(APPPATH . 'config/' . ENVIRONMENT . '/autoload.php')) {
          include(APPPATH . 'config/' . ENVIRONMENT . '/autoload.php');
        } else {
          include(APPPATH . 'config/autoload.php');
        }
        
        if (isset($autoload['interface'])) {
            foreach($autoload['interface'] as $interface) {
                $this->php_interface($interface);
            }
        }
    }
    
    /**
     * Abstract library Loader
     *
     * Loads a single abstract library.
     * Designed to be called from application controllers.
     *
     * @param	string	$abstractLibrary	Abstract library name
     * @return	object
     */
    public function abstract_library($abstractLibrary)
    {
        if (empty($abstractLibrary)) {
            return $this;
        } elseif (is_array($abstractLibrary)) {
            foreach ($abstractLibrary as $key => $value) {
                $this->abstract_library($value);
            }

            return $this;
        }

        $this->_ci_load_abstract_library($abstractLibrary);
        return $this;
    }
    
    /**
     * Internal Abstract Library Loader
     *
     * @used-by	MY_Loader::absract_library()
     *
     * @param	string	$class		Class name to load
     * @return	void
     */
    protected function _ci_load_abstract_library($class)
    {
        // Get the class name, and while we're at it trim any slashes.
        // The directory path can be included as part of the class name,
        // but we don't want a leading slash
        $class = str_replace('.php', '', trim($class, '/'));

        // Was the path included with the class name?
        // We look for a slash to determine this
        if (($last_slash = strrpos($class, '/')) !== false) {
            // Extract the path
            $subdir = substr($class, 0, ++$last_slash);

            // Get the filename from the path
            $class = substr($class, $last_slash);
        } else {
            $subdir = '';
        }

        $class = ucfirst($class);

        // Let's search for the requested library file and load it.
        foreach ($this->_ci_library_paths as $path) {
            $filepath = $path . 'libraries/' . $subdir . $class . '.php';

            // Safety: Was the class already loaded by a previous call?
            if (class_exists($class, FALSE)) {
                return;
            } elseif (!file_exists($filepath)) {
                continue;
            }

            include_once($filepath);
            return;
        }

        // One last attempt. Maybe the library is in a subdirectory, but it wasn't specified?
        if ($subdir === '') {
            return $this->_ci_load_abstract_library($class.'/'.$class);
        }

        // If we got this far we were unable to find the requested class.
        log_message('error', 'Unable to load the requested abstract class: ' . $class);
        show_error('Unable to load the requested abstract class: ' . $class);
    }
}
