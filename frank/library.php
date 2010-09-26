<?php	

	/**
	 * API for a get request function
	 *
	 * @param string   $route		Request path
	 * @param function $function	Function to call on $route request
	 */
	function get($route, $function){
		Frank::add_route('get', $route, $function);
	}
	
	/**
	 * API for a post request function
	 *
	 * @param string   $route		Request path
	 * @param function $function	Function to call on $route request
	 */
	function post($route, $function){
		Frank::add_route('post', $route, $function);
	}
	
	/**
	 * API for a put request function
	 *
	 * @param string   $route		Request path
	 * @param function $function	Function to call on $route request
	 */
	function put($route, $function){
		Frank::add_route('put', $route, $function);
	}
	
	/**
	 * API for a delete request function
	 *
	 * @param string   $route		Request path
	 * @param function $function	Function to call on $route request
	 */
	function delete($route, $function){
		Frank::add_route('delete', $route, $function);
	}
	
	/**
	 * API for a 404 error
	 *
	 * @param function $function	Function to call on 404
	 */
	function not_found($function){
		Frank::add_error('404', $function);
	}
	
	/**
	 * Sets options for Frank
	 *
	 * @param array $options 	Options for Frank
	 */
	function set($options){
		foreach($options as $option => $value)
			settings::set($option, $value);
	}

	/**
	 * API for adding a template function
	 *
	 * @param string   $name		Name of template
	 * @param function $function	Function for template
	 */
	function template($name, $function){
		Frank::add_template($name, $function);
	}

	/**
	 * API for showing a template
	 *
	 * @param string   $name		Name of template
	 * @param function $options		Options for template
	 */
	function render($name, $options=array()){
		Frank::render_template($name, $options);
	}

	/**
	 * API for adding a before filter function
	 *
	 * @param function $function	Function to call before execution
	 */
	function before($function){
		Frank::add_filter('before', $function);
	}

	/**
	 * API for adding an after filter function
	 *
	 * @param function $function	Function to call after execution
	 */
	function after($function){
		Frank::add_filter('after', $function);
	}

	/**
	 * API for configuring
	 *
	 * @param function $function	Function that configures Frank
	 */
	function configure($function){
		call_user_func($function);
	}

	/**
	 * API to make another route handle execution
	 *
	 * @param string $route		Route to pass execution to
	 */
	function pass($route){
		Frank::set_request($route);
		Frank::call(array('pass' => true));
	}

	/**
	 * Halts execution
	 */
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
		Frank::output(Frank::get_status(), array('die' => true));
	}

	/**
	 * Adds a middleware to use
	 *
	 * @param string or object $middleware
	 */
	function middleware($middleware){
		Frank::add_middleware($middleware);
	}

?>