<?php

	/**
	 * Reverse Preg Match Array
	 *
	 * Tries to match the contents of an array to a given string.
	 * 
	 * @param  string $string 		String to search with array
	 * @param  array  $regex_array  Regexes to use for search
	 * @param  array  $also_match	Other regexs to match
	 * @return array or false (depending on whether or not matches were found)
	 */
	function reverse_preg_match_array($string, $regex_array, $also_match=array()){
		$matches = array();
			
		foreach($regex_array as $regex){
			
			$new_regex = $regex;
			
			foreach($also_match as $match)
				$new_regex = preg_replace($match, '.*?', $new_regex);

			if(preg_match("#^$new_regex$#", $string))
				$matches[] = $regex;
		}
	
		if(count($matches) > 0)
			return $matches;
		else
			return false;
	}
	
	/**
	 * URL Difference
	 *
	 * Returns an array containing the differences (split by /) in two urls
	 * 
	 * @param  string $url_1 Original url
	 * @param  string $url_2 URL whose differences are returned
	 * @return array
	 */
	function url_diff($url_1, $url_2){
		//If the two urls are exactly the same, than we don't need to do anything.
		if($url_1 == $url_2)
			return array();
			
		$differences = array();
		$url_1 = explode('/', $url_1);
		$url_2 = explode('/', $url_2);
		
		foreach($url_1 as $key => $url_1_item){
			if($url_2[$key] !== $url_1_item)
				$differences[$url_2[$key]] = $url_1_item;
		}
		
		return $differences;
	}

?>