<?php
 
	require('helpers.php');
	require('core.php');
	require('settings.php');
	require('library.php');

	/**
	 * Runs Frank
	 */
	function run(){
		if(Frank::was_run() !== true){
			$output = Frank::call();
			foreach(Frank::middleware() as $middleware){
				if(gettype($middleware) == 'string')
					$middleware = new $middleware;
			
				$output = $middleware->call($output);
			}
			
			Frank::output($output);
		}
	}

	register_shutdown_function('run', E_ALL);

?>