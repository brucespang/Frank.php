<?php
	class Frank{
		private static $instance;
	
		private static $run = false;
	
		private static $method;
	
		private static $dead = false;
	
		private static $status;

		private static $headers;

		private static $body = '';
	
		private static $routes = array(
			'get' => array(),
			'post' => array(),
			'put' => array(),
			'delete' => array()
		);
	
		private static $filters = array(
			'before' => array(),
			'after' => array()
		);
	
		private static $errors, $request, $templates = array();

		public static $view_path;
	
		/**
		 * Public functions
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
		
			self::set_status(array(200, array(), $yield));
			return array(self::$status, self::$headers, self::$body);
		}
	
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
		
			return self::$instance;
		}
	
		public static function set_run($run){
			self::$run = $run;
		}
		
		public static function was_run(){
			return self::$run;
		}
		
		public static function set_status($status){
			self::$status = $status[0];
			self::$headers = $status[1];

			if($status[2] !== false)
				self::$body .= $status[2];
			else
				self::$body = '';
		}
		
		public static function get_status(){
			return array(self::$status, self::$headers, self::$body);
		}
	
		public static function add_filter($type, $function){
			array_push(self::$filters[$type], $function);
		}
	
		public static function add_route($method, $route, $block){
			self::$routes[$method][$route] = $block;

			return self::getInstance();
		}
	
		public static function add_error($error, $block){
			self::$errors[$error] = $block;

			return self::getInstance();
		}
	
		public static function add_template($name, $block){
			self::$templates[$name] = $block;

			return self::getInstance();
		}
	
		public static function set_request($request){
			self::$request = $request;
		}
	
		public static function set_method($method){
			self::$method = $method;
		}
	
		public static function display_status($options=array()){

			// Mark self as dead if told to die
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
		
			
			if(isset($status_codes[self::$status])){
				$status_message = $status_codes[self::$status];
				header('HTTP/1.1 '.self::$status." $status_message");
			}

			foreach(self::$headers as $type => $header)
				header("$type: $header", self::$status);

			echo(self::$body);
			
			// Clean up status in case this is run again. (Probably shouldn't happen, but who knows?)
			self::set_status(array(200, array(), ''));
			
			if(isset($options['die']) && $options['die'] === true)
				die();
		}
	
		/**
		 * Private functions
		 */

		private static function get_request(){
		    if(self::$request)
				return self::$request;
		    else
		        return str_replace(str_replace($_SERVER['DOCUMENT_ROOT'], '', dirname(__FILE__)), '', $_GET['q']);
		}

		private static function get_method(){
			if(self::$method)
				return strtolower(self::$method);
			else
				return strtolower($_SERVER['REQUEST_METHOD']);
		}
	 
		private static function getInstance(){
			if (self::$instance) {
				return self::$instance;
			}
			return self::$instance = new self();
		}
	
		private static function route($method, $request){
		    $params = array();
		
			if(isset(self::$routes[$method][$request])){
				$block = self::$routes[$method][$request];
			}else{	
				$wilds = preg_grep('#^/.*/:.*$#', array_keys(self::$routes[$method]));
			
				foreach($wilds as $wild){
					$wild_preg = preg_replace('/:[A-Za-z0-9]+/', '(.*?)', $wild);
					if(preg_match("#^$wild_preg\$#", $request, $matches)){
						$block = self::$routes[$method][$wild];
						preg_match('/:[A-Za-z0-9]+/', $wild, $wild_names);
						foreach($wild_names as $key => $wild_name){
							$wild_name = str_replace(':', '', $wild_name);
							$params[$wild_name] = urldecode($matches[$key+1]);
						}
					}
					
				}
			}
		
			if(!isset($block)){
				header("HTTP/1.0 404 Not Found");
				if(isset(self::$errors['404']))
					$block = self::$errors['404'];
				else
					$block = function(){ echo "We couldn't find that page."; };
			}
		
			return array($block, $params);
		}
	}
?>