<?php
define("YAF_VERSION", "2.2.9");
define("YAF_ENVIRON", "product");
define("YAF_ERR_STARTUP_FAILED", 512);
define("YAF_ERR_ROUTE_FAILED", 513);
define("YAF_ERR_DISPATCH_FAILED", 514);
define("YAF_ERR_AUTOLOAD_FAILED", 520);
define("YAF_ERR_NOTFOUND_MODULE", 515);
define("YAF_ERR_NOTFOUND_CONTROLLER", 516);
define("YAF_ERR_NOTFOUND_ACTION", 517);
define("YAF_ERR_NOTFOUND_VIEW", 518);
define("YAF_ERR_CALL_FAILED", 519);
define("YAF_ERR_TYPE_ERROR", 521);
abstract class Yaf_Action_Abstract extends Yaf_Controller_Abstract {
protected $_controller;
/**
 * @param mixed $arg
 * @return mixed
 */
abstract public function execute($arg, $__args = null);
/**
 * @return Yaf_Controller_Abstract
 */
public function getController();
}
final class Yaf_Application {
protected $config;
protected $dispatcher;
protected static $_app;
protected $_modules;
protected $_running;
protected $_environ;
/**
 * @return Yaf_Application
 */
public static function app();
/**
 * @param Yaf_Bootstrap_Abstract $bootstrap
 * @return Yaf_Application
 */
public function bootstrap(Yaf_Bootstrap_Abstract $bootstrap = null);
/**
 * @return Yaf_Application
 */
public function clearLastError();
private function __clone();
/**
 * @param mixed $config
 * @param string $envrion
 */
function __construct($config, $envrion = null);
public function __destruct();
/**
 * @return string
 */
public function environ();
/**
 * @param callable $entry
 */
public function execute(callable $entry, $__args = null);
/**
 * @return string
 */
public function getAppDirectory();
/**
 * @return Yaf_Config_Abstract
 */
public function getConfig();
/**
 * @return Yaf_Dispatcher
 */
public function getDispatcher();
/**
 * @return string
 */
public function getLastErrorMsg();
/**
 * @return int
 */
public function getLastErrorNo();
/**
 * @return array
 */
public function getModules();
/**
 * @return Yaf_Response_Http
 */
public function run();
/**
 * @param string $directory
 * @return Yaf_Application
 */
public function setAppDirectory($directory);
private function __sleep();
private function __wakeup();
}
abstract class Yaf_Bootstrap_Abstract {
}
abstract class Yaf_Config_Abstract {
protected $_config;
protected $_readonly;
/**
 * @param string $name
 * @param mixed $value
 * @return mixed
 */
abstract public function get($name, $value);
/**
 * @return bool
 */
abstract public function readonly();
/**
 * @return Yaf_Config_Abstract
 */
abstract public function set();
/**
 * @return array
 */
abstract public function toArray();
}
class Yaf_Config_Ini extends Yaf_Config_Abstract implements Iterator , ArrayAccess , Countable {
/**
 * @param string $config_file
 * @param string $section
 */
function __construct ($config_file, $section = null);
public function count ();
public function current ();
/**
 * @param string $name
 */
public function __get ($name = null);
/**
 * @param string $name
 */
public function __isset ($name);
public function key ();
public function next ();
/**
 * @param string $name
 */
public function offsetExists ($name);
/**
 * @param string $name
 */
public function offsetGet ($name);
/**
 * @param string $name
 * @param string $value
 */
public function offsetSet ($name, $value);
/**
 * @param string $name
 */
public function offsetUnset ($name);
public function readonly ();
public function rewind ();
/**
 * @param string $name
 * @param mixed $value
 */
public function __set ($name, $value);
/**
 * @return array
 */
public function toArray ();
public function valid ();
public function get($name, $value);
public function set();
}
class Yaf_Config_Simple extends Yaf_Config_Abstract implements Iterator, ArrayAccess, Countable {
protected $_readonly;
/**
 * @param string $config_file
 * @param string $section
 */
function __construct($config_file, $section = null);
public function count();
public function current();
/**
 * @param string $name
 */
public function __get($name = null);
/**
 * @param string $name
 */
public function __isset($name);
public function key();
public function next();
/**
 * @param string $name
 */
public function offsetExists($name);
/**
 * @param string $name
 */
public function offsetGet($name);
/**
 * @param string $name
 * @param string $value
 */
public function offsetSet($name, $value);
/**
 * @param string $name
 */
public function offsetUnset($name);
public function readonly();
public function rewind();
/**
 * @param string $name
 * @param string $value
 */
public function __set($name, $value);
/**
 * @return array
 */
public function toArray();
public function valid();
public function get($name, $value);
public function set();
}
class Yaf_Controller_Abstract {
public $actions;
protected $_module;
protected $_name;
protected $_request;
protected $_response;
protected $_invoke_args;
protected $_view;
final private function __clone();
final private function __construct();
/**
 * @param string $tpl
 * @param array $parameters
 * @return bool
 */
protected function display($tpl, array $parameters = null);
/**
 * @param string $module
 * @param string|array $controller
 * @param string|array $action
 * @param array $paramters
 */
public function forward($module, $controller = null, $action = null, array $paramters = null);
/**
 * @param string $name
 * @return string
 */
public function getInvokeArg($name);
/**
 * @return array
 */
public function getInvokeArgs();
/**
 * @return string
 */
public function getModuleName();
/**
 * @return Yaf_Request_Http Yaf_Request_Abstract
 */
public function getRequest();
/**
 * @return Yaf_Response_Abstract
 */
public function getResponse();
/**
 * @return Yaf_View_Interface
 */
public function getView();
/**
 * @return string
 */
public function getViewpath();
public function init();
/**
 * @param array $options
 */
public function initView(array $options = null);
/**
 * @param string $url
 * @return bool
 */
public function redirect($url);
/**
 * @param string $tpl
 * @param array $parameters
 * @return string
 */
protected function render($tpl, array $parameters = null);
/**
 * @param string $view_directory
 */
public function setViewpath($view_directory);
}
final class Yaf_Dispatcher {
protected $_router;
protected $_view;
protected $_request;
protected $_plugins;
protected static $_instance;
protected $_auto_render;
protected $_return_response;
protected $_instantly_flush;
protected $_default_module;
protected $_default_controller;
protected $_default_action;
/**
 * @param bool $flag
 * @return Yaf_Dispatcher
 */
public function autoRender($flag = null);
/**
 * @param bool $flag
 * @return Yaf_Dispatcher
 */
public function catchException($flag = false);
private function __clone();
function __construct();
/**
 * @return Yaf_Dispatcher
 */
public function disableView();
/**
 * @param Yaf_Request_Abstract $request
 * @return Yaf_Response_Abstract
 */
public function dispatch(Yaf_Request_Abstract $request);
/**
 * @return Yaf_Dispatcher
 */
public function enableView();
/**
 * @param bool $flag
 * @return Yaf_Dispatcher
 */
public function flushInstantly($flag = null);
/**
 * @return Yaf_Application
 */
public function getApplication();
/**
 * @return Yaf_Dispatcher
 */
public static function getInstance();
/**
 * @return Yaf_Request_Abstract
 */
public function getRequest();
/**
 * @return Yaf_Router
 */
public function getRouter();
/**
 * @param string $templates_dir
 * @param array $options
 * @return Yaf_View_Interface
 */
public function initView($templates_dir, array $options = null);
/**
 * @param Yaf_Plugin_Abstract $plugin
 * @return Yaf_Dispatcher
 */
public function registerPlugin(Yaf_Plugin_Abstract $plugin);
/**
 * @param bool $flag
 * @return Yaf_Dispatcher
 */
public function returnResponse($flag);
/**
 * @param string $action
 * @return Yaf_Dispatcher
 */
public function setDefaultAction($action);
/**
 * @param string $controller
 * @return Yaf_Dispatcher
 */
public function setDefaultController($controller);
/**
 * @param string $module
 * @return Yaf_Dispatcher
 */
public function setDefaultModule($module);
/**
 * @param call $callback
 * @param int $error_types
 * @return Yaf_Dispatcher
 */
public function setErrorHandler(callable $callback, $error_types);
/**
 * @param Yaf_Request_Abstract $request
 * @return Yaf_Dispatcher
 */
public function setRequest(Yaf_Request_Abstract $request);
/**
 * @param Yaf_View_Interface $view
 * @return Yaf_Dispatcher
 */
public function setView(Yaf_View_Interface $view);
private function __sleep();
/**
 * @param bool $flag
 * @return Yaf_Dispatcher
 */
public function throwException($flag = false);
private function __wakeup();
}
class Yaf_Exception extends Exception {
protected $message;
protected $code;
protected $file;
protected $line;
function __construct();
public function getPrevious();
}
class Yaf_Exception_DispatchFailed extends Yaf_Exception {
}
class Yaf_Exception_LoadFailed extends Yaf_Exception {
}
class Yaf_Exception_LoadFailed_Action extends Yaf_Exception_LoadFailed {
}
class Yaf_Exception_LoadFailed_Controller extends Yaf_Exception_LoadFailed {
}
class Yaf_Exception_LoadFailed_Module extends Yaf_Exception_LoadFailed {
}
class Yaf_Exception_LoadFailed_View extends Yaf_Exception_LoadFailed {
}
class Yaf_Exception_RouterFailed extends Yaf_Exception {
}
class Yaf_Exception_StartupError extends Yaf_Exception {
}
class Yaf_Exception_TypeError extends Yaf_Exception {
}
class Yaf_Loader {
protected $_local_ns;
protected $_library;
protected $_global_library;
static $_instance;
/**
 * @param string $class_name
 * @return bool
 */
public function autoload($class_name);
/**
 * @return bool
 */
public function clearLocalNamespace();
private function __clone();
function __construct();
/**
 * @return Yaf_Loader
 */
public static function getInstance();
/**
 * @param bool $is_global
 * @return Yaf_Loader
 */
public function getLibraryPath($is_global = null);
/**
 * @return array
 */
public function getLocalNamespace();
/**
 * @return bool
 */
public static function import();
/**
 * @param string $class_name
 * @return bool
 */
public function isLocalName($class_name);
/**
 * @param mixed $prefix
 * @return Yaf_Loader
 */
public function registerLocalNamespace($prefix);
/**
 * @param string $directory
 * @param bool $is_global
 * @return Yaf_Loader
 */
public function setLibraryPath($directory, $is_global = null);
private function __sleep();
private function __wakeup();
}
class Yaf_Plugin_Abstract {
/**
 * @param Yaf_Request_Abstract $request
 * @param Yaf_Response_Abstract $response
 */
public function dispatchLoopShutdown(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response);
/**
 * @param Yaf_Request_Abstract $request
 * @param Yaf_Response_Abstract $response
 */
public function dispatchLoopStartup(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response);
/**
 * @param Yaf_Request_Abstract $request
 * @param Yaf_Response_Abstract $response
 */
public function postDispatch(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response);
/**
 * @param Yaf_Request_Abstract $request
 * @param Yaf_Response_Abstract $response
 */
public function preDispatch(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response);
/**
 * @param Yaf_Request_Abstract $request
 * @param Yaf_Response_Abstract $response
 */
public function preResponse(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response);
/**
 * @param Yaf_Request_Abstract $request
 * @param Yaf_Response_Abstract $response
 */
public function routerShutdown(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response);
/**
 * @param Yaf_Request_Abstract $request
 * @param Yaf_Response_Abstract $response
 */
public function routerStartup(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response);
}
class Yaf_Registry {
static $_instance;
protected $_entries;
private function __clone();
function __construct();
/**
 * @param string $name
 */
public static function del($name);
/**
 * @param string $name
 * @return mixed
 */
public static function get($name);
/**
 * @param string $name
 * @return bool
 */
public static function has($name);
/**
 * @param string $name
 * @param string $value
 * @return bool
 */
public static function set($name, $value);
}
class Yaf_Request_Abstract {
const SCHEME_HTTP = http;
const SCHEME_HTTPS = https;
public $module;
public $controller;
public $action;
public $method;
protected $params;
protected $language;
protected $_exception;
protected $_base_uri;
protected $uri;
protected $dispatched;
protected $routed;
public function getActionName();
public function getBaseUri();
public function getControllerName();
/**
 * @param string $name
 * @param string $default
 */
public function getEnv($name, $default = null);
public function getException();
public function getLanguage();
public function getMethod();
public function getModuleName();
/**
 * @param string $name
 * @param string $default
 */
public function getParam($name, $default = null);
public function getParams();
public function getRequestUri();
/**
 * @param string $name
 * @param string $default
 */
public function getServer($name, $default = null);
public function isCli();
public function isDispatched();
public function isGet();
public function isHead();
public function isOptions();
public function isPost();
public function isPut();
public function isRouted();
public function isXmlHttpRequest();
/**
 * @param string $action
 */
public function setActionName($action);
/**
 * @param string $uir
 * @return bool
 */
public function setBaseUri($uir);
/**
 * @param string $controller
 */
public function setControllerName($controller);
public function setDispatched();
/**
 * @param string $module
 */
public function setModuleName($module);
/**
 * @param string $name
 * @param string $value
 */
public function setParam($name, $value = null);
/**
 * @param string $uir
 */
public function setRequestUri($uir);
/**
 * @param string $flag
 */
public function setRouted($flag = null);
}
class Yaf_Request_Http extends Yaf_Request_Abstract {
private function __clone();
function __construct();
/**
 * @param string $name
 * @param string $default
 * @return mixed
 */
public function get($name, $default = null);
/**
 * @param string $name
 * @param string $default
 * @return mixed
 */
public function getCookie($name, $default = null);
public function getFiles();
/**
 * @param string $name
 * @param string $default
 * @return mixed
 */
public function getPost($name, $default = null);
/**
 * @param string $name
 * @param string $default
 * @return mixed
 */
public function getQuery($name, $default = null);
public function getRequest();
/**
 * @return bool
 */
public function isXmlHttpRequest();
}
class Yaf_Request_Simple extends Yaf_Request_Abstract {
const SCHEME_HTTP = http;
const SCHEME_HTTPS = https;
private function __clone();
function __construct();
public function get();
public function getCookie();
public function getFiles();
public function getPost();
public function getQuery();
public function getRequest();
public function isXmlHttpRequest();
}
class Yaf_Response_Abstract {
const DEFAULT_BODY = "content";
protected $_header;
protected $_body;
protected $_sendheader;
/**
 * @param string $content
 * @param string $key
 * @return bool
 */
public function appendBody($content, $key = null);
/**
 * @param string $key
 * @return bool
 */
public function clearBody($key = null);
public function clearHeaders();
private function __clone();
function __construct();
public function __destruct();
/**
 * @param string $key
 * @return mixed
 */
public function getBody($key = null);
public function getHeader();
/**
 * @param string $content
 * @param string $key
 * @return bool
 */
public function prependBody($content, $key = null);
public function response();
protected function setAllHeaders();
/**
 * @param string $content
 * @param string $key
 * @return bool
 */
public function setBody($content, $key = null);
public function setHeader();
public function setRedirect($url);
private function __toString();
}
interface Yaf_Route_Interface {
/**
 * @param Yaf_Request_Abstract $request
 * @return bool
 */
abstract public function route(Yaf_Request_Abstract $request);
}
class Yaf_Route_Map implements Yaf_Route_Interface {
protected $_ctl_router;
protected $_delimeter;
/**
 * @param string $controller_prefer
 * @param string $delimiter
 */
function __construct($controller_prefer = false, $delimiter = '_');
/**
 * @param Yaf_Request_Abstract $request
 * @return bool
 */
public function route(Yaf_Request_Abstract $request);
}
class Yaf_Route_Regex implements Yaf_Route_Interface {
protected $_route;
protected $_default;
protected $_maps;
protected $_verify;
/**
 * @param string $match
 * @param array $route
 * @param array $map
 * @param array $verify
 */
function __construct($match, array $route, array $map, array $verify = null);
/**
 * @param Yaf_Request_Abstract $request
 * @return bool
 */
public function route(Yaf_Request_Abstract $request);
}
class Yaf_Route_Rewrite implements Yaf_Route_Interface {
protected $_route;
protected $_default;
protected $_verify;
/**
 * @param string $match
 * @param array $route
 * @param array $verify
 */
function __construct($match, array $route, array $verify = null);
/**
 * @param Yaf_Request_Abstract $request
 * @return bool
 */
public function route(Yaf_Request_Abstract $request);
}
class Yaf_Route_Simple implements Yaf_Route_Interface {
protected $controller;
protected $module;
protected $action;
/**
 * @param string $module_name
 * @param string $controller_name
 * @param string $action_name
 */
function __construct($module_name, $controller_name, $action_name);
/**
 * @param Yaf_Request_Abstract $request
 * @return bool
 */
public function route(Yaf_Request_Abstract $request);
}
class Yaf_Route_Static implements Yaf_Route_Interface {
/**
 * @param string $uri
 */
public function match($uri);
/**
 * @param Yaf_Request_Abstract $request
 * @return bool
 */
public function route(Yaf_Request_Abstract $request);
}
class Yaf_Route_Supervar implements Yaf_Route_Interface {
protected $_var_name;
/**
 * @param string $supervar_name
 */
function __construct($supervar_name);
/**
 * @param Yaf_Request_Abstract $request
 * @return bool
 */
public function route(Yaf_Request_Abstract $request);
}
class Yaf_Router {
protected $_routes;
protected $_current;
/**
 * @param Yaf_Config_Abstract $config
 * @return bool
 */
public function addConfig(Yaf_Config_Abstract $config);
/**
 * @param string $name
 * @param Yaf_Route_Abstract $route
 * @return bool
 */
public function addRoute($name, Yaf_Route_Interface $route);
function __construct();
/**
 * @return string
 */
public function getCurrentRoute();
/**
 * @param string $name
 * @return Yaf_Route_Interface
 */
public function getRoute($name);
/**
 * @return mixed
 */
public function getRoutes();
/**
 * @param Yaf_Request_Abstract $request
 * @return bool
 */
public function route(Yaf_Request_Abstract $request);
}
class Yaf_Session implements Iterator, ArrayAccess, Countable {
protected static $_instance;
protected $_session;
protected $_started;
private function __clone();
function __construct();
public function count();
public function current();
/**
 * @param string $name
 */
public function del($name);
/**
 * @param string $name
 */
public function __get($name);
public static function getInstance();
/**
 * @param string $name
 */
public function has($name);
/**
 * @param string $name
 */
public function __isset($name);
public function key();
public function next();
/**
 * @param string $name
 */
public function offsetExists($name);
/**
 * @param string $name
 */
public function offsetGet($name);
/**
 * @param string $name
 * @param string $value
 */
public function offsetSet($name, $value);
/**
 * @param string $name
 */
public function offsetUnset($name);
public function rewind();
/**
 * @param string $name
 * @param string $value
 */
public function __set($name, $value);
private function __sleep();
public function start();
/**
 * @param string $name
 */
public function __unset($name);
public function valid();
private function __wakeup();
}
interface Yaf_View_Interface {
/**
 * @param string $name
 * @param string $value
 * @return bool
 */
abstract public function assign($name, $value = null);
/**
 * @param string $tpl
 * @param array $tpl_vars
 * @return bool
 */
abstract public function display($tpl, array $tpl_vars = null);
abstract public function getScriptPath();
/**
 * @param string $tpl
 * @param array $tpl_vars
 * @return string
 */
abstract public function render($tpl, array $tpl_vars = null);
/**
 * @param string $template_dir
 */
abstract public function setScriptPath($template_dir);
}
class Yaf_View_Simple implements Yaf_View_Interface {
protected $_tpl_vars;
protected $_tpl_dir;
/**
 * @param string $name
 * @param mixed $value
 * @return bool
 */
public function assign($name, $value = null);
/**
 * @param string $name
 * @param
 *        mixed &$value
 * @return bool
 */
public function assignRef($name, &$value);
/**
 * @param string $name
 * @return bool
 */
public function clear($name = null);
/**
 * @param string $tempalte_dir
 * @param array $options
 * @return public
 */
final function __construct($tempalte_dir, array $options = null);
/**
 * @param string $tpl
 * @param array $tpl_vars
 * @return bool
 */
public function display($tpl, array $tpl_vars = null);
/**
 * @param string $tpl_content
 * @param array $tpl_vars
 * @return string
 */
public function eval111111111($tpl_content, array $tpl_vars = null);
/**
 * @param string $name
 */
public function __get($name = null);
/**
 * @return string
 */
public function getScriptPath();
/**
 * @param string $name
 */
public function __isset($name);
/**
 * @param string $tpl
 * @param array $tpl_vars
 * @return string
 */
public function render($tpl, array $tpl_vars = null);
/**
 * @param string $name
 * @param mixed $value
 */
public function __set($name, $value);
/**
 * @param string $template_dir
 * @return bool
 */
public function setScriptPath($template_dir);
}