<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|  example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|  http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|  $route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|  $route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|  $route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:  my-controller/index  -> my_controller/index
|    my-controller/my-method  -> my_controller/my_method
*/

// AJAX Calls (always using params)
$route['ajax/cancel']                      = 'Vzg_controller/cancel';
$route['ajax/chart']                       = 'Vzg_controller/chart';
$route['ajax/clear']                       = 'Vzg_controller/clear';
$route['ajax/command']                     = 'Vzg_controller/command';
$route['ajax/config']                      = 'Vzg_controller/config';
$route['ajax/changepw']                    = 'Vzg_controller/changepw';
$route['ajax/checkpw']                     = 'Vzg_controller/checkpw';
$route['ajax/cockpit']                     = 'Vzg_controller/cockpit';
$route['ajax/exportlink']                  = 'Vzg_controller/exportlink';
$route['ajax/facet']                       = 'Vzg_controller/facet';
$route['ajax/fullview/(:any)/(:any)']      = 'Vzg_controller/fullview/$1/$2';
$route['ajax/getwords']                    = 'Vzg_controller/getwords';
$route['ajax/ilorder']                     = 'Vzg_controller/ilorder';
$route['ajax/ilorderview/(:any)/(:any)']   = 'Vzg_controller/ilorderview/$1/$2';
$route['ajax/imgurl']                      = 'Vzg_controller/imgurl';
$route['ajax/language']                    = 'Vzg_controller/language';
$route['ajax/layout']                      = 'Vzg_controller/layout';
$route['ajax/libraryspecial']              = 'Vzg_controller/libraryspecial';
$route['ajax/linkresolver']                = 'Vzg_controller/linkresolver';
$route['ajax/login']                       = 'Vzg_controller/login';
$route['ajax/log']                         = 'Vzg_controller/log';
$route['ajax/logout']                      = 'Vzg_controller/logout';
$route['ajax/mailorderto']                 = 'Vzg_controller/mailorderto';
$route['ajax/mailorderview/(:any)/(:any)'] = 'Vzg_controller/mailorderview/$1/$2';
$route['ajax/mailto']                      = 'Vzg_controller/mailto';
$route['ajax/renew']                       = 'Vzg_controller/renew';
$route['ajax/request']                     = 'Vzg_controller/request';
$route['ajax/search/(:any)']               = 'Vzg_controller/search/$1';
$route['ajax/searchsimularpubs']           = 'Vzg_controller/searchsimularpubs';
$route['ajax/searchrelatedpubs']           = 'Vzg_controller/searchrelatedpubs';
$route['ajax/settingsload']                = 'Vzg_controller/settingsload';
$route['ajax/settingsstore']               = 'Vzg_controller/settingsstore';
$route['ajax/settingsdelete']              = 'Vzg_controller/settingsdelete';
$route['ajax/settingsview']                = 'Vzg_controller/settingsview';
$route['ajax/passwordview']                = 'Vzg_controller/passwordview';
$route['ajax/sessclear']                   = 'Vzg_controller/sessclear';
$route['ajax/settings/(:any)']             = 'Vzg_controller/settings/$1';
$route['ajax/specialview/(:any)/(:any)']   = 'Vzg_controller/specialview/$1/$2';
$route['ajax/statsclient']                 = 'Vzg_controller/statsclient';
$route['ajax/userview/(:any)']             = 'Vzg_controller/userview/$1';
$route['ajax/(:any)']                      = 'Vzg_controller/search/$1';
                                           
// Downloads                               
$route['exportfile/(:any)/(:any)']         = 'Vzg_controller/exportfile/$1/$2';
$route['exportfilelist/(:any)/(:any)']     = 'Vzg_controller/exportfilelist/$1/$2';

// NoJavaScript                               
$route['nojavascript']                     = 'Vzg_controller/nojavascript';

// Library Module
$route['librarymodule']                    = 'Vzg_controller/view/library';
                                           
// One Page Calls                          
$route['(:any)/(:any)']                    = 'Vzg_controller/directopen/$1/$2';
$route['(:any)']                           = 'Vzg_controller/directopen/$1';
$route['default_controller']               = 'Vzg_controller/view';
