<?php

	class Framr{
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
		
		private static $errors, $templates = array();
		
		public static $view_path;
		
		public function run(){
			if(!self::$run){
				$request = str_replace(str_replace($_SERVER['DOCUMENT_ROOT'], '', dirname(__FILE__)), '', $_SERVER['REQUEST_URI']);
				$method = $_SERVER['REQUEST_METHOD'];
				
				$method = strtolower($method);
		
				foreach(self::$filters['before'] as $before)
					call_user_func($before);
				
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
					if(isset(self::$errors['404']))
						$block = self::$errors['404'];
					else
						$block = function(){ echo "We couldn't find that page."; };
				}
				
				if(count($params) == 0)
					call_user_func($block);
				else
					call_user_func($block, $params);
				
				foreach(self::$filters['after'] as $after)
					call_user_func($after);
					
				self::$run = true;
			} else {
				throw new Exception("Framr has already been run.");
			}
		}
		
		public function render_template($name, $options){
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
		
		function add_filter($type, $function){
			array_push(self::$filters[$type], $function);
		}
		
		public function add_route($method, $route, $block){
			self::$routes[$method][$route] = $block;

			return self::getInstance();
		}
		
		public function add_error($error, $block){
			self::$errors[$error] = $block;

			return self::getInstance();
		}
		
		public function add_template($name, $block){
			self::$templates[$name] = $block;

			return self::getInstance();
		}
		
		private static function getInstance(){
			if (self::$instance) {
				return self::$instance;
			}
			return self::$instance = new self();
		}
	}
	
	
	function get($route, $block){
		Framr::add_route('get', $route, $block);
	}
	
	function post($route, $block){
		Framr::add_route('post', $route, $block);
	}
	
	function put($route, $block){
		Framr::add_route('put', $route, $block);
	}
	
	function delete($route, $block){
		Framr::add_route('delete', $route, $block);
	}
	
	function not_found($block){
		Framr::add_error('404', $block);
	}
	
	function set($options){
		if(isset($options['views']))
			Framr::$view_path = $options['views'];
	}
	
	function template($name, $block){
		Framr::add_template($name, $block);
	}
	
	function render($name, $options=array()){
		Framr::render_template($name, $options);
	}
	
	function before($function){
		Framr::add_filter('before', $function);
	}
	
	function after($function){
		Framr::add_filter('after', $function);
	}
	
	function configure($function){
		call_user_func($function);
	}
	
	register_shutdown_function('Framr::run');

?>