<?php

defined('BASEPATH') or exit('No direct script access allowed');

/* load the MX_Router class */
require APPPATH . 'third_party/MX/Router.php';

class App_Router extends MX_Router
{
    /**
    * Set the route mapping
    *
    * This function determines what should be served based on the URI request,
    * as well as any "routes" that have been set in the routing config file.
    *
    * @access   private
    * @return   void
    */
    public function _set_routing()
    {
        // Load the routes.php file. It would be great if we could
        // skip this for enable_query_strings = TRUE, but then
        // default_controller would be empty ...
        if (file_exists(APPPATH . 'config/routes.php')) {
            include(APPPATH . 'config/routes.php');
        }

        if (file_exists(APPPATH . 'config/' . ENVIRONMENT . '/routes.php')) {
            include(APPPATH . 'config/' . ENVIRONMENT . '/routes.php');
        }

        // Include routes every modules
        $modules_locations = config_item('modules_locations') ? config_item('modules_locations') : false;

        if (!$modules_locations) {
            $modules_locations = APPPATH . 'modules/';

            if (is_dir($modules_locations)) {
                $modules_locations = [$modules_locations => '../modules/'];
            } else {
                show_error('Modules directory not found');
            }
        }

        foreach ($modules_locations as $key => $value) {
            if ($handle = opendir($key)) {
                while (false !== ($entry = readdir($handle))) {
                    if ($entry != '.' && $entry != '..') {
                        if (is_dir($key . $entry)) {
                            $rfile = Modules::find('routes' . EXT, $entry, 'config/');

                            if ($rfile[0]) {
                                include($rfile[0] . $rfile[1]);
                            }
                        }
                    }
                }
                closedir($handle);
            }
        }

        // Validate & get reserved routes
        if (isset($route) && is_array($route)) {
            isset($route['default_controller']) && $this->default_controller     = $route['default_controller'];
            isset($route['translate_uri_dashes']) && $this->translate_uri_dashes = $route['translate_uri_dashes'];
            unset($route['default_controller'], $route['translate_uri_dashes']);
            $this->routes = $route;
        }

        // Are query strings enabled in the config file? Normally CI doesn't utilize query strings
        // since URI segments are more search-engine friendly, but they can optionally be used.
        // If this feature is enabled, we will gather the directory/class/method a little differently
        if ($this->enable_query_strings) {
            // If the directory is set at this time, it means an override exists, so skip the checks
            if (! isset($this->directory)) {
                $_d = $this->config->item('directory_trigger');
                $_d = isset($_GET[$_d]) ? trim($_GET[$_d], " \t\n\r\0\x0B/") : '';

                if ($_d !== '') {
                    $this->uri->filter_uri($_d);
                    $this->set_directory($_d);
                }
            }

            $_c = trim($this->config->item('controller_trigger'));
            if (! empty($_GET[$_c])) {
                $this->uri->filter_uri($_GET[$_c]);
                $this->set_class($_GET[$_c]);

                $_f = trim($this->config->item('function_trigger'));
                if (! empty($_GET[$_f])) {
                    $this->uri->filter_uri($_GET[$_f]);
                    $this->set_method($_GET[$_f]);
                }

                $this->uri->rsegments = [
                    1 => $this->class,
                    2 => $this->method,
                ];
            } else {
                $this->_set_default_controller();
            }

            // Routing rules don't apply to query strings and we don't need to detect
            // directories, so we're done here
            return;
        }

        // Is there anything to parse?
        if ($this->uri->uri_string !== '') {
            $this->_parse_routes();
        } else {
            $this->_set_default_controller();
        }
    }
}
