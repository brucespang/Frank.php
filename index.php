<?php
	require 'frank.php';
	
	class Helpers{
	  function hello($name){
	    return "Hello, $name";
	  }
	}
	
	configure(function(){
		$test = 'test';
		set(array('views' => dirname(__FILE__) . '/templates'));
	});
	
	after(function(){
		echo " AFTER!";
	});
	 
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
		echo Helpers::hello($params['name']);
		halt(404, 'Go away', array('Content-Type' => 'text/plain'));
	});
	
	not_found(function(){
	  echo "This file wasn't found, yo!";
	});
	
?>