<?php

	require_once 'PHPUnit/Framework.php';
	require 'index.php';
	
	class FrankTest extends PHPUnit_Framework_TestCase{

		/**
		 * Tests if templating works
		 */
		public function testTemplate(){
			$this->assertEquals("Hello from template", $this->get_data('/template', array('pass' => true)));
		}
		
		/**
		 * Tests variables in urls
		 */
		public function testHello(){
			$this->assertEquals("Hello, name", $this->get_data('/hello/name', array('pass' => true)));
		}
		
		/**
		 * Tests if passing works as expected
		 */
		public function testPass(){
			$this->assertEquals("Hello, passing", $this->get_data('/pass', array('pass' => true)));
		}
		
		/**
		 * Tests if halting works as expected
		 */
		public function testHalt(){
			$this->assertEquals("Go away", $this->get_data('/halt'));
		}
		
		/**
		 * Tests if post requests work correctly
		 */
		public function testPost(){
			$this->assertEquals("post", $this->get_data('/post', array('pass' => true), 'post'));
		}

		/**
		 * Tests if delete requests work correctly
		 */
		public function testDelete(){
			$this->assertEquals("delete", $this->get_data('/delete', array('pass' => true), 'delete'));
		}

		/**
		 * Tests if put requests work correctly
		 */
		public function testPut(){
			$this->assertEquals("put", $this->get_data('/put', array('pass' => true), 'put'));
		}
		
		/**
		 * Tests if 404s work
		 */
		public function test404(){
			$this->assertEquals("This file wasn't found, yo!", $this->get_data('/not_found', array('pass' => true)));
		}
		
		/**
		 * Tests if splat routes work
		 */
		public function testSplat(){
			$this->assertEquals("test", $this->get_data('/splat/test', array('pass' => true)));
		}

		/**
		 * Tests if regex routes work
		 */
		public function testCaptures(){
			$this->assertEquals("test", $this->get_data('/captures/test', array('pass' => true)));
		}

		/**
		 * Tests if middleware works
		 */
		public function testMiddleware(){
			$this->assertEquals("Before asdf After", $this->get_data('/middleware'));
		}
		
		/**
		 * Private Functions
		 */
		
		/**
		 * Gets the output from a url
		 *
		 * @param 	string	$url	 path (in Frank) to get the data for
		 * @param	array	$options set of options to pass to Frank::exec()
		 * @param	string	$method  type of request to the server (i.e. get, post, put, delete)
		 * @return 	string			 data the function outputs
		 */
		private function get_data($url, $options=array(), $method='get'){
			Frank::set_request($url);
			Frank::set_method($method);
			Frank::set_run(true);
			Frank::set_status(array(200, array(), false));
			$output = Frank::call($options);
			
			foreach(Frank::middleware() as $middleware){
				if(gettype($middleware) == 'string')
					$middleware = new $middleware;
			
				$output = $middleware->call($output);
			}
			
			return $output[2];
		}
	}

?>