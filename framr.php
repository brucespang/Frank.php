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
		
		private static $errors = array();
		
		public function run(){
			if(!self::$run){
				$request = str_replace(str_replace($_SERVER['DOCUMENT_ROOT'], '', dirname(__FILE__)), '', $_SERVER['REQUEST_URI']);
				$method = $_SERVER['REQUEST_METHOD'];
				
				$method = strtolower($method);
				
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
				
				if(count($params) == 0)
					call_user_func($block);
				else
					call_user_func($block, $params);
				
				self::$run = true;
			} else {
				throw new Exception("Framr has already been run.");
			}
		}
		
		public function add_route($method, $route, $block){
			self::$routes[$method][$route] = $block;

			return self::getInstance();
		}
		
		public function add_error($error, $block){
			self::$errors[$error] = $block;

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

	
	register_shutdown_function('Framr::run');

?>