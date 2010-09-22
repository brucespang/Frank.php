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
		echo " AFTER!";
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
	
	get("/halt", function(){
		halt(404, 'Go away', array('Content-Type' => 'text/plain'));
	});
	
	not_found(function(){
	  echo "This file wasn't found, yo!";
	});
	
?>