<?php

	require_once 'PHPUnit/Framework.php';
	require 'index.php';
	
	class FrankTest extends PHPUnit_Framework_TestCase{

		/**
		 * @method testTemplate
		 * 
		 * Tests if templating works
		 */
		public function testTemplate(){
			$this->assertEquals("Hello from template", $this->get_data('/template', array('pass' => true)));
		}
		
		/**
		 * @method testHello
		 *
		 * Tests if Frank says Hello, name on request of /hello/name
		 */
		public function testHello(){
			$this->assertEquals("Hello, name", $this->get_data('/hello/name', array('pass' => true)));
		}
		
		/**
		 * @method testPass
		 *
		 * Tests if passing works as expected
		 */
		public function testPass(){
			$this->assertEquals("Hello, passing", $this->get_data('/pass', array('pass' => true)));
		}
		
		/**
		 * @method testHalt
		 *
		 * Tests if halting works as expected
		 */
		public function testHalt(){
			$this->assertEquals("Go away", $this->get_data('/halt'));
		}
		
		/**
		 * @method testPost
		 *
		 * Tests if post requests work correctly
		 */
		public function testPost(){
			$this->assertEquals("post", $this->get_data('/post', array('pass' => true), 'post'));
		}

		/**
		 * @method testDelete
		 *
		 * Tests if delete requests work correctly
		 */
		public function testDelete(){
			$this->assertEquals("delete", $this->get_data('/delete', array('pass' => true), 'delete'));
		}

		/**
		 * @method testPost
		 *
		 * Tests if put requests work correctly
		 */
		public function testPut(){
			$this->assertEquals("put", $this->get_data('/put', array('pass' => true), 'put'));
		}
		
		/**
		 * Private Functions
		 */
		
		/**
		 * @method get_data
		 * @param 	string	path (in Frank) to get the data for
		 * @param	array	set of options to pass to Frank::exec()
		 * @param	method	type of request to the server (i.e. get, post, put, delete)
		 * @return 	string	data the function outputs
		 */
		private function get_data($url, $options=array(), $method='get'){
			Frank::set_request($url);
			Frank::set_method($method);
			Frank::set_run(true);
			Frank::set_status(array(200, array(), false));
			$status = Frank::call($options);
			return $status[2];
		}
	}

?>