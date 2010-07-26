<?php
	require 'framr.php';
	
	get("/", function(){
	  echo '<form method="post">
	        <input type="submit" value="submit" />
	        </form>';
	});
	
	post("/", function(){
	  echo "post";
	});
	
	get("/hello/:name", function($params){
		echo 'Hi, '.$params['name'];
	});
	
	get("/hello/:name/test", function($params){
		echo 'Hi, '.$params['name']." TEST!";
	});
	
	not_found(function(){
	  echo "This file wasn't found, yo!";
	});
?>