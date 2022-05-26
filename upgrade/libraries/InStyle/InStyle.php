<?php
	/**
	* InStyle
	* Embedded CSS to Inline CSS Converter Class
	* @version 0.1
	* @updated 09/18/2009
	* 
	* @author David Lim
	* @email miliak@orst.edu
	* @link http://www.davidandjennilyn.com
	* @acknowledgements Simple HTML Dom
	*/ 

	class InStyle {

		function convert($document) {

			// Extract the CSS
			preg_match('/<style[^>]+>(?<css>[^<]+)<\/style>/s', $document, $matches);

			// Strip out extra newlines and tabs from CSS
			$css = preg_replace("/[\n\r\t]+/s", "", $matches['css']);
			
			// Returns the css after removing media queries
			$refactoredCss = $this->findAndRemoveMediaQueries($css);
			
			// Extract each CSS declaration
			preg_match_all('/([a-zA-Z0-9_ ,#\.]+){([^}]+)}/s', $refactoredCss, $rules, PREG_SET_ORDER);
			// For each CSS declaration, explode the selector and declaration into an array
			// Array index 1 is the CSS selector
			// Array index 2 is the CSS rule(s)
			foreach ($rules as $rule) {
				$styles[trim($rule['1'])] = $styles[trim($rule['1'])].trim($rule['2']);
			}

			// DEBUG: Show selector and declaration
			if ($debug) {
				echo '<pre>';
				foreach ($styles as $selector=>$styling) {
					echo $selector . ':<br>';
					echo $styling . '<br/><br/>';
				}
				echo '</pre><hr/>';
			}
			$html_dom = new simple_html_dom();
			// Load in the HTML without the head and style definitions
			$html_dom->load($document); // Retaining styles without removing from head tag

			// For each style declaration, find the selector in the HTML and add the inline CSS
			if (!empty($styles)) {
				foreach ($styles as $selector=>$styling) {
					foreach ($html_dom->find($selector) as $element) {
						$elementStyle = $element->style;
						if(substr($elementStyle, -1) == ';'){
							$element->style .= $styling;
						} else {
							$element->style .= ";".$styling;
						}
					}
				}
				$inline_css_message = $html_dom->save();
				return $inline_css_message;
			}
			return false;
		}
		
		/**
		 * Function to find and remove media queries and return css without media queries
		 * @param type $css
		 * @return type
		 */
		function findAndRemoveMediaQueries($css){
			 $mediaBlocks = array();

			$start = 0;
			while (($start = strpos($css, "@media", $start)) !== false)	{
				// stack to manage brackets
				$s = array();

				// get the first opening bracket
				$i = strpos($css, "{", $start);

				// if $i is false, then there is probably a css syntax error
				if ($i !== false)
				{
					// push bracket onto stack
					array_push($s, $css[$i]);

					// move past first bracket
					$i++;

					while (!empty($s))
					{
						// if the character is an opening bracket, push it onto the stack, otherwise pop the stack
						if ($css[$i] == "{")
						{
							array_push($s, "{");
						}
						elseif ($css[$i] == "}")
						{
							array_pop($s);
						}

						$i++;
					}

					// cut the media block out of the css and store
					$mediaBlocks[] = substr($css, $start, ($i) - $start);

					// set the new $start to the end of the block
					$start = $i;
				}
			}
			foreach($mediaBlocks as $value){
				$css = str_replace($value,'',$css);
			}
			return $css;
		}
	}

/* End of file inline_css.php */