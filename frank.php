<?php
  
	class Frank{
		private static $instance;
		
		private static $run = false;
		
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
		
		public static function run($options=array()){
			$request = self::get_request();
			$method = $_SERVER['REQUEST_METHOD'];

			$method = strtolower($method);

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
			  	if(!isset($options['pass']) || $options['pass'] != true)
			    		foreach(self::$filters['before'] as $before)
				    		call_user_func($before);

				if(count($params) == 0)
					call_user_func($block);
				else
			    		call_user_func($block, $params); 

			  	if(!isset($options['pass']) || $options['pass'] != true)
			  		foreach(self::$filters['after'] as $after)
						call_user_func($after);

  				$yield = ob_get_contents();
			ob_end_clean();
				
			echo $yield;
		}
		
		public static function render_template($name, $options){
			$locals = $options['locals'] ? $options['locals'] : array();
			
			if(isset(self::$templates[$name])){
			
				$template = self::$templates[$name];
				
				call_user_func($template, $locals);
			
			}elseif(file_exists(self::$view_path.'/'.$name.'.html')){
			
				$template = function($path, $locals){
					require($path);
				};
				
				$template(self::$view_path.'/'.$name.'.html', $locals);
			
			}
			
			return self::$instance;
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
		
		/**
		 * Private functions
		 */

 		private static function get_request(){
 		    if(self::$request)
				return self::$request;
 		    else
 		        return str_replace(str_replace($_SERVER['DOCUMENT_ROOT'], '', dirname(__FILE__)), '', $_SERVER['REQUEST_URI']);
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
	
	
	function get($route, $block){
		Frank::add_route('get', $route, $block);
	}
	
	function post($route, $block){
		Frank::add_route('post', $route, $block);
	}
	
	function put($route, $block){
		Frank::add_route('put', $route, $block);
	}
	
	function delete($route, $block){
		Frank::add_route('delete', $route, $block);
	}
	
	function not_found($block){
		Frank::add_error('404', $block);
	}
	
	function set($options){
		if(isset($options['views']))
			Frank::$view_path = $options['views'];
	}
	
	function template($name, $block){
		Frank::add_template($name, $block);
	}
	
	function render($name, $options=array()){
		Frank::render_template($name, $options);
	}
	
	function before($function){
		Frank::add_filter('before', $function);
	}
	
	function after($function){
		Frank::add_filter('after', $function);
	}
	
	function configure($function){
		call_user_func($function);
	}
	
	function pass($route){
		Frank::set_request($route);
		Frank::run(array('pass' => true));
	}

	function halt(){
		$args = func_get_args();

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

		// Set default values
		$status = false;
		$headers = array();
		$body = '';
 
		foreach($args as $arg){
			if(is_numeric($arg))
				$status = $arg;
			elseif(is_array($arg))
				$headers = $arg;
			elseif(is_string($arg))
				$body = $arg;
		}
 
		if($status !== false){
			if(isset($status_codes[$status])){
				$status_message = $status_codes[$status];
				header("HTTP/1.1 $status $status_message");
			}
		}
 
		foreach($headers as $type => $header)
			header("$type: $header", $status);
 
		die($body);
	}

	register_shutdown_function('Frank::run', E_ALL);

?>