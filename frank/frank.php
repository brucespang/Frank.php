<?php
 
	require('core.php');
	require('library.php');

	function run(){
		if(Frank::was_run() !== true){
			Frank::call();
			Frank::display_status();
		}
	}

	register_shutdown_function('run', E_ALL);

?>