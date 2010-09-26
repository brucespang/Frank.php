<?php
	require 'frank/frank.php';
	
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
		echo ". Good bye!";
	});
	 
	get("/", function(){
		echo "Welcome to Frank.php";
	});
	
	get("/template", function(){
		render('template', array('locals' => array('name' => 'template')));	
	});
	
	template("template", function($locals){
		echo 'Hello from '.$locals['name'];
	});
	
	post("/post", function(){
		echo "post";
	});
	
	put("/put", function(){
		echo "put";
	});
	
	delete("/delete", function(){
		echo "delete";
	});
	
	get("/pass", function(){
		pass('/hello/passing');
	});
	
	get("/hello/:name", function($params){
		echo hello($params['name']);
	});
	
	get("/splat/*", function($params){
		echo $params['splat'][0];
	});
	
	get("/captures/(.*?)", function($params){
		echo $params['captures'][0];
	});
	
	get("/halt", function(){
		halt(404, 'Go away', array('Content-Type' => 'text/plain'));
	});
	
	not_found(function(){
	  echo "This file wasn't found, yo!";
	});

	class Middleware{
		function call($output){
			return array(200, array(), 'asdf');
		}
	}

	class WrapMiddleware{
		function call($output){
			return array(200, array(), "Before $output[2] After");
		}
	}

	get("/middleware", function(){
		middleware('Middleware');
		middleware('WrapMiddleware');
	});
	
?>