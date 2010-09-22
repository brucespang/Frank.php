<?php	
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
		Frank::call(array('pass' => true));
	}

	function halt(){
		$args = func_get_args();

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
		
 		Frank::set_status(array($status, $headers, $body));
		Frank::display_status(array('die' => true));
	}

?>