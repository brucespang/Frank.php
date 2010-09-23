<?php
 
	require('core.php');
	require('library.php');

	/**
	 * Runs Frank
	 */
	function run(){
		if(Frank::was_run() !== true){
			Frank::call();
			Frank::output();
		}
	}

	register_shutdown_function('run', E_ALL);

?>