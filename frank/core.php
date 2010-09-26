<?php
	class Frank{

		/**
		 * Stores whether or not Frank has been successfully run.
		 *
		 * @var boolean
		 */
		private static $run = false;
		
		/**
		 * Requested Path
		 *
		 * @var string
		 */
		private static $request = '';
		
		/**
		 * Method of the request (e.g. get, post, put, delete)
		 *
		 * @var string
		 */
		private static $method = '';
	
		/**
		 * Checks if halt has killed frank's execution. We can't use die() from the 
		 * command line, so for tests to work, this has to be present.
		 *
		 * @var boolean
		 */
		private static $dead = false;
	
		/**
		 * Response status code to return to client
		 *
		 * @var integer
		 */
		private static $status = 200;

		/**
		 * Array of headers to return to client
		 *
		 * @var array
		 */
		private static $headers = array();

		/**
		 * Script's output
		 *
		 * @var string
		 */
		private static $body = '';
	
		/**
		 * Array of each request method's routes and corresponding functions
		 *
		 * @var array
		 */
		private static $routes = array(
			'get' => array(),
			'post' => array(),
			'put' => array(),
			'delete' => array()
		);
	
		/**
		 * Before and After arrays of functions to call before or after script execution
		 *
		 * @var array
		 */
		private static $filters = array(
			'before' => array(),
			'after' => array()
		);
	
		/**
		 * Array of errors and their corresponding functions
		 *
		 * @var array
		 */
		private static $errors = array();
		
		/**
		 * Array of template functions
		 *
		 * @var array
		 */
		private static $templates = array();

		/**
		 * Array of middleware classes
		 *
		 * @var array
		 */
		private static $middleware = array();

		/**
		 * Template directory location
		 *
		 * @var string
		 */
		public static $view_path = '';
	
		/**
		 * Public functions
		 */	
		
		/**
		 * Main function, gets route information, and executes script
		 *
		 * @param  array $options General execution control
		 * @return array Standard rack return: Status Code, array of headers, body
		 */
		public static function call($options=array()){
			$request = self::get_request();
			$method = self::get_method();
			
			$routing_information = self::route($method, $request);
			$block = $routing_information[0];
			$params = $routing_information[1];

			// Create functions from all of the Helpers class methods
			if(class_exists('Helpers')){
			  foreach(get_class_methods('Helpers') as $function){
			    // Fairly hackish, so it would be good to rewrite this.
				if(!function_exists($function)){
				    eval("function $function(){
				      return call_user_func_array(array('Helpers', '$function'), func_get_args());
				    }");
				}
			  }
			}

			// Catch all output so that halting works.
			ob_start();
			  	if((!isset($options['pass']) || $options['pass'] != true) && self::$dead == false)
					foreach(self::$filters['before'] as $before)
						call_user_func($before);

				if(count($params) == 0)
					call_user_func($block);
				else
					call_user_func($block, $params); 

			  	if((!isset($options['pass']) || $options['pass'] != true) && self::$dead == false)
			  		foreach(self::$filters['after'] as $after)
						call_user_func($after);

				$yield = ob_get_contents();
			ob_end_clean();
		
			self::set_status(array(self::$status, self::$headers, $yield));
			return array(self::$status, self::$headers, self::$body);
		}
	
		/**
		 * Renders a template
		 *
		 * @param string $name		Name of the template
		 * @param array  $options	Options to control template rendering
		 */
		public static function render_template($name, $options){
			$locals = $options['locals'] ? $options['locals'] : array();
			
			if(isset(self::$templates[$name])){
		
				$template = self::$templates[$name];

				ob_start();
					call_user_func($template, $locals);
					self::$body .= ob_get_contents();
				ob_end_clean();

			}elseif(file_exists(self::$view_path.'/'.$name.'.html')){
		
				$template = function($path, $locals){
					require($path);
				};
				
				ob_start();
					$template(self::$view_path.'/'.$name.'.html', $locals);
					self::$body .= ob_get_contents();
				ob_end_clean();
			}
		}
		
		/**
		 * Outputs the status code, headers, and content
		 *
		 * @param array $options Output options
		 */
		public static function output($output, $options=array()){
			// Mark Frank as dead if told to die
			if(isset($options['die']) && $options['die'] == true)
				self::$dead = true;

			// Status shouldn't be displayed when running from command line
			if(defined('STDIN'))
				return;
				
			
			// List of status codes for ease of mapping $status
			$status_codes = array(
								// Informational 1xx
								100 => 'Continue',
								101 => 'Switching Protocols',
								// Successful 2xx
								200 => 'OK',
								201 => 'Created',
								202 => 'Accepted',
								203 => 'Non-Authoritative Information',
								204 => 'No Content',
								205 => 'Reset Content',
								206 => 'Partial Content',
								// Redirection 3xx
								300 => 'Multiple Choices',
								301 => 'Moved Permanently',
								302 => 'Found',
								303 => 'See Other',
								304 => 'Not Modified',
								305 => 'Use Proxy',
								307 => 'Temporary Redirect',
								// Client Error 4xx
								400 => 'Bad Request',
								401 => 'Unauthorized',
								402 => 'Payment Required',
								403 => 'Forbidden',
								404 => 'Not Found',
								405 => 'Method Not Allowed',
								406 => 'Not Acceptable',
								407 => 'Proxy Authentication Required',
								408 => 'Request Timeout',
								409 => 'Conflict',
								410 => 'Gone',
								411 => 'Length Required',
								412 => 'Precondition Failed',
								413 => 'Request Entity Too Large',
								414 => 'Request-URI Too Long',
								415 => 'Unsupported Media Type',
								416 => 'Request Range Not Satisfiable',
								417 => 'Expectation Failed',
								// Server Error 5xx
								500 => 'Internal Server Error',
								501 => 'Not Implemented',
								502 => 'Bad Gateway',
								503 => 'Service Unavailable',
								504 => 'Gateway Timeout',
								505 => 'HTTP Version Not Supported'
			                );
		
			
			if(isset($status_codes[$output[0]])){
				$status_message = $status_codes[$output[0]];
				header('HTTP/1.1 '.$output[0]." $status_message");
			}

			foreach($output[1] as $type => $header)
				header("$type: $header", $output[0]);

			echo($output[2]);
			
			// Clean up status in case this is run again. (Probably shouldn't happen, but who knows?)
			self::set_status(array(200, array(), ''));
			
			if(isset($options['die']) && $options['die'] === true)
				die();
		}
		
		/**
		 * Sets self::$run
		 *
		 * @param boolean $run	Value to set self::$run to
		 */
		public static function set_run($run){
			self::$run = $run;
		}
		
		/**
		 * Value of self::$run
		 *
		 * @return boolean	Value of self::$run
		 */
		public static function was_run(){
			return self::$run;
		}
		
		/**
		 * Updates Frank's status
		 *
		 * @param array $status	Standard Rack result-style array
		 */
		public static function set_status($status){
			self::$status = $status[0];
			self::$headers = $status[1];

			if($status[2] !== false)
				self::$body .= $status[2];
			else
				self::$body = '';
		}
		
		/**
		 * Gets Frank's status
		 *
		 * @return array Standard Rack result-style array
		 */
		public static function get_status(){
			return array(self::$status, self::$headers, self::$body);
		}
	
		/**
		 * Adds a before or after filter function
		 *
		 * @param string   $type	 Type of filter to add (e.g. before/after)
		 * @param function $function Function to call when filter is called
		 */
		public static function add_filter($type, $function){
			array_push(self::$filters[$type], $function);
		}
		
		/**
		 * Adds a route function
		 *
		 * @param string   $method		Request method (get, post, etc...)
		 * @param string   $route		Path of the function
		 * @param function $function	Function to execute when path is requested
		 */
		public static function add_route($method, $route, $function){
			self::$routes[$method][$route] = $function;
		}
		
		/**
		 * Adds an error function
		 *
		 * @param string   $error		Type of error to add
		 * @param function $function	Function to execute on error
		 */
		public static function add_error($error, $function){
			self::$errors[$error] = $function;
		}
	
		/**
		 * Adds a template from a function
		 *
		 * @param string	$name		Name of the template
		 * @param function	$function	Function that contains the template
		 */
		public static function add_template($name, $function){
			self::$templates[$name] = $function;
		}
	
		/**
		 * Sets the request url
		 *
		 * @param string $request	Path to set request to
		 */
		public static function set_request($request){
			self::$request = $request;
		}
	
		/**
		 * Sets the request method
		 *
		 * @param string $method	Method to set Frank's request method to
		 */
		public static function set_method($method){
			self::$method = $method;
		}
	
		/**
		 * Adds a piece of middleware
		 *
		 * @param object or string $middleware 	Object for middleware, or name of middleware class
		 */
		public static function add_middleware($middleware){
			self::$middleware[] = $middleware;
		}
		
		/**
		 * Gets an array of middleware to use
		 *
		 * @return array	List of middleware
		 */
		public static function middleware(){
			return self::$middleware;
		}
	
		/**
		 * Private functions
		 */

		/**
		 * Gets the requested path
		 *
		 * @return string	Path requested
		 */
		private static function get_request(){
		    if(!self::$request)
		        self::$request = str_replace(str_replace($_SERVER['DOCUMENT_ROOT'], '', dirname(__FILE__)), '', $_GET['q']);

			return self::$request;	
		}

		/**
		 * Gets the request method
		 *
		 * @return string Request Method
		 */
		private static function get_method(){
			if(!self::$method)
				self::$method = $_SERVER['REQUEST_METHOD'];
				
			return strtolower(self::$method);
		}
	
		/**
		 * Finds a function to call from a request path
		 *
		 * @param string $method	Request method
		 * @param string $request	Request path
		 * @return array			Function to call and parameters to call it with
		 */
		private static function route($method, $request){
		    $params = array(
							'splat' => array(),
							'captures' => array()
							);
			
			if(isset(self::$routes[$method][$request])){
				$function = self::$routes[$method][$request];
			} elseif(($route = reverse_preg_match_array($request, array_keys(self::$routes[$method]), array('#\*(/|$)#', '/:[A-Za-z0-9]+/'))) && $route !== false){
				$route = end($route);
				
				// The only different things between the request url and the
				// route should be the regex's, so we get them.
				$changes = url_diff($request, $route);

				$function = self::$routes[$method][$route];

				foreach($changes as $index => $value){
					
					if(preg_match('/^:/', $index)){
						
						//Strip leading :
						$index = preg_replace('/^:/', '', $index);
						
						$params[$index] = $value;
						
					}elseif($index == '*'){
						
						$params['splat'][] = $value;
						
					} else {
						$params['captures'][] = $value;
					}
				}
			}
		
			if(!isset($function)){
				self::$status = 404;
				
				//We don't want to display headers for command line
				if(!defined('STDIN'))
					header("HTTP/1.1 404 Not Found");
					
				if(isset(self::$errors['404']))
					$function = self::$errors['404'];
				else
					$function = function(){ echo "We couldn't find that page."; };
			}
		
			return array($function, $params);
		}
	}
?>