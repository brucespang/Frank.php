<?php

	class settings{
		
		/**
		 * Array of settings and their values
		 *
		 * @var array
		 */
		private static $settings = array();
		
		/**
		 * Set a setting
		 *
		 * @param string $setting_name	Name of the setting
		 * @param mixed  $value			Value of the setting
		 */
		function set($setting_name, $value){
			self::$settings[$setting_name] = $value;
		}
		
		/**
		 * Get the value of a setting
		 *
		 * @param  string $setting_name	Name of the setting
		 * @return string 				Value of the setting
		 */
		function get($setting_name){
			if(gettype(self::$settings[$setting_name]) == 'object')
				$value = call_user_func(self::$settings[$setting_name]);
			else
				$value = self::$settings[$setting_name];

			return $value;
		}
	}

?>