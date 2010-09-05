<?php
	require 'framr.php';
	
	configure(function(){
		$test = 'test';
	});
	
	before(function(){
	});
	
	after(function(){
		echo "AFTER!";
	});
	
	set(array('views' => dirname(__FILE__) . '/templates'));
	 
	get("/", function(){
      render('form', array('locals' => array('test' => 'test')));
	});
	
	template("form", function($locals){
		echo '<form method="post">
	        	<input type="submit" value="submit" />
	        	</form>';
	});
	
	post("/", function(){
	  echo "post";
	});
	
	get("/hello/:name", function($params){
		pass('/hello/'.$params['name'].'/test');
	});
	
	get("/hello/:name/test", function($params){
		echo 'Hi, '.$params['name']." TEST!";
	});
	
	not_found(function(){
	  echo "This file wasn't found, yo!";
	});
	
?>