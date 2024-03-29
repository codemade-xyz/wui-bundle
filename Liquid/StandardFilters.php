<?php

/**
 * This file is part of the Liquid package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Liquid
 */

namespace CodeMade\WuiBundle\Liquid;

/**
 * A selection of standard filters.
 */
class StandardFilters
{
	
	/**
	 * Add one string to another
	 *
	 * @param string $input
	 * @param string $string
	 *
	 * @return string
	 */
	public static function append($input, $string) {
		return $input . $string;
	}
	

	/**
	 * Capitalize words in the input sentence
	 *
	 * @param string $input
	 *
	 * @return string
	 */
	public static function capitalize($input) {
		return preg_replace_callback("/(^|[^\p{L}'])([\p{Ll}])/u", function($matches) {
			return $matches[1] . ucfirst($matches[2]);
		}, ucwords($input));
	}	
	

	/**
	 * @param mixed $input number
	 *
	 * @return int
	 */
	public static function ceil($input) {
		return (int) ceil((float)$input);
	}
	

	/**
	 * Formats a date using strftime
	 *
	 * @param mixed $input
	 * @param string $format
	 *
	 * @return string
	 */
	public static function date($input, $format) {
        if ($input == 'now') {
            $input = time();
            return date($format, $input);
        }

		if (!is_numeric($input)) {
			$input = strtotime($input);
		}


		return date($format, $input);

	}	
	
	
	/**
	 * Default
	 *
	 * @param string $input
	 * @param string $default_value
	 *
	 * @return string
	 */
	public static function _default($input, $default_value) {
		$isBlank = $input == '' || $input === false || $input === null;
		return $isBlank ? $default_value : $input;
	}
	
	
	/**
	 * division
	 *
	 * @param int $input
	 * @param int $operand
	 *
	 * @return int
	 */
	public static function divided_by($input, $operand) {
		return (int)$input / (int)$operand;
	}
		
	
	/**
	 * Convert an input to lowercase
	 *
	 * @param string $input
	 *
	 * @return string
	 */
	public static function downcase($input) {
		return is_string($input) ? strtolower($input) : $input;
	}
	
	
	/**
	 * Pseudo-filter: negates auto-added escape filter
	 *
	 * @param string $input
	 *
	 * @return string
	 */
	public static function raw($input) {
		return $input;
	}

	/**
	 * Escape a string
	 *
	 * @param string $input
	 *
	 * @return string
	 */
	public static function escape($input) {
		return is_string($input) ? htmlentities($input, ENT_QUOTES) : $input;
	}


	/**
	 * Escape a string once, keeping all previous HTML entities intact
	 *
	 * @param string $input
	 *
	 * @return string
	 */
	public static function escape_once($input) {
		return is_string($input) ? htmlentities($input, ENT_QUOTES, null, false) : $input;
	}


	/**
	 * Returns the first element of an array
	 *
	 * @param array|\Iterator $input
	 *
	 * @return mixed
	 */
	public static function first($input) {
		if ($input instanceof \Iterator) {
			$input->rewind();
			return $input->current();
		}
		return is_array($input) ? reset($input) : $input;
	}	
	
	
	/**
	 * @param mixed $input number
	 *
	 * @return int
	 */
	public static function floor($input) {
		return (int) floor((float)$input);
	}	
	
	
	/**
	 * Joins elements of an array with a given character between them
	 *
	 * @param array|\Traversable $input
	 * @param string $glue
	 *
	 * @return string
	 */
	public static function join($input, $glue = ' ') {
		if ($input instanceof \Traversable) {
			$str = '';
			foreach ($input as $elem) {
				if ($str) {
					$str .= $glue;
				}
				$str .= $elem;
			}
			return $str;
		}
		return is_array($input) ? implode($glue, $input) : $input;
	}
	
	
	/**
	 * Returns the last element of an array
	 *
	 * @param array|\Traversable $input
	 *
	 * @return mixed
	 */
	public static function last($input) {
		if ($input instanceof \Traversable) {
			$last = null;
			foreach ($input as $elem){
				$last = $elem;
			}
			return $last;
		}
		return is_array($input) ? end($input) : $input;
	}	
	

	/**
	 * @param string $input
	 *
	 * @return string
	 */
	public static function lstrip($input) {
		return ltrim($input);
	}	
	
	
	/**
	 * Map/collect on a given property
	 *
	 * @param array|\Traversable $input
	 * @param string $property
	 *
	 * @return string
	 */
	public static function map($input, $property) {
		if ($input instanceof \Traversable) {
			$input = iterator_to_array($input);
		}
		if (!is_array($input)) {
			return $input;
		}
		return array_map(function ($elem) use ($property) {
			if (is_callable($elem)) {
				return $elem();
			} elseif (is_array($elem) && array_key_exists($property, $elem)) {
				return $elem[$property];
			}
			return null;
		}, $input);
	}
	

	/**
	 * subtraction
	 *
	 * @param int $input
	 * @param int $operand
	 *
	 * @return int
	 */
	public static function minus($input, $operand) {
		return (int)$input - (int)$operand;
	}
	
	
	/**
	 * modulo
	 *
	 * @param int $input
	 * @param int $operand
	 *
	 * @return int
	 */
	public static function modulo($input, $operand) {
		return (int)$input % (int)$operand;
	}	
	
	
	/**
	 * Replace each newline (\n) with html break
	 *
	 * @param string $input
	 *
	 * @return string
	 */
	public static function newline_to_br($input) {
		return is_string($input) ? str_replace(array(
			"\n", "\r"
		), '<br />', $input) : $input;
	}	
	

	/**
	 * addition
	 *
	 * @param float $input
	 * @param float $operand
	 *
	 * @return float
	 */
	public static function plus($input, $operand) {
		$input = is_numeric($input) ? $input : 0;
		$operand = is_numeric($operand) ? $operand : 0;
		return $input + $operand;
	}


	/**
	 * Prepend a string to another
	 *
	 * @param string $input
	 * @param string $string
	 *
	 * @return string
	 */
	public static function prepend($input, $string) {
		return $string . $input;
	}
	

	/**
	 * Remove a substring
	 *
	 * @param string $input
	 * @param string $string
	 *
	 * @return string
	 */
	public static function remove($input, $string) {
		return str_replace($string, '', $input);
	}


	/**
	 * Remove the first occurrences of a substring
	 *
	 * @param string $input
	 * @param string $string
	 *
	 * @return string
	 */
	public static function remove_first($input, $string) {
		if (($pos = strpos($input, $string)) !== false) {
			$input = substr_replace($input, '', $pos, strlen($string));
		}

		return $input;
	}	
	

	/**
	 * Replace occurrences of a string with another
	 *
	 * @param string $input
	 * @param string $string
	 * @param string $replacement
	 *
	 * @return string
	 */
	public static function replace($input, $string, $replacement = '') {
		return str_replace($string, $replacement, $input);
	}


	/**
	 * Replace the first occurrences of a string with another
	 *
	 * @param string $input
	 * @param string $string
	 * @param string $replacement
	 *
	 * @return string
	 */
	public static function replace_first($input, $string, $replacement = '') {
		if (($pos = strpos($input, $string)) !== false) {
			$input = substr_replace($input, $replacement, $pos, strlen($string));
		}

		return $input;
	}
	
	
	/**
	 * Reverse the elements of an array
	 *
	 * @param array|\Traversable $input
	 *
	 * @return array
	 */
	public static function reverse($input) {
		if ($input instanceof \Traversable) {
			$input = iterator_to_array($input);
		}
		return array_reverse($input);
	}
	
	
	/**
	 * Round a number
	 *
	 * @param float $input
	 * @param int $n precision
	 *
	 * @return float
	 */
	public static function round($input, $n = 0) {
		return round((float)$input, (int)$n);
	}	
	
	
	/**
	 * @param string $input
	 *
	 * @return string
	 */
	public static function rstrip($input) {
		return rtrim($input);
	}	
									
	
	/**
	 * Return the size of an array or of an string
	 *
	 * @param mixed $input
	 *
	 * @return int
	 */
	public static function size($input) {
		if ($input instanceof \Iterator) {
			return iterator_count($input);
		}
		if (is_string($input) || is_numeric($input)) {
			return strlen($input);
		} elseif (is_array($input)) {
			return count($input);
		} elseif (is_object($input)) {
			if (method_exists($input, 'size')) {
				return $input->size();
			}
		}

		return $input;
	}
	

	/**
	 * @param array|\Iterator|string $input
	 * @param int $offset
	 * @param int $length
	 *
	 * @return array|\Iterator|string
	 */
	public static function slice($input, $offset, $length = null) {
		if ($input instanceof \Iterator) {
			$input = iterator_to_array($input);
		}
		if (is_array($input)) {
			$input = array_slice($input, $offset, $length);
		} elseif (is_string($input)) {
			$input = $length === null
				? substr($input, $offset)
				: substr($input, $offset, $length);
		}

		return $input;
	}	
	
	
	/**
	 * Sort the elements of an array
	 *
	 * @param array|\Traversable $input
	 * @param string $property use this property of an array element
	 *
	 * @return array
	 */
	public static function sort($input, $property = null) {
		if ($input instanceof \Traversable) {
			$input = iterator_to_array($input);
		}
		if ($property === null) {
			asort($input);
		} else {
			$first = reset($input);
			if ($first !== false && is_array($first) && array_key_exists($property, $first)) {
				uasort($input, function($a, $b) use ($property) {
					if ($a[$property] == $b[$property]) {
						return 0;
					}

					return $a[$property] < $b[$property] ? -1 : 1;
				});
			}
		}

		return $input;
	}	
	

	/**
	 * Split input string into an array of substrings separated by given pattern.
	 *
	 * @param string $input
	 * @param string $pattern
	 *
	 * @return array
	 */
	public static function split($input, $pattern) {
		// Unlike PHP explode function, empty string after split filtering is empty array in Liquid.
		if (!is_string($input) || $input === '') {
			return array();
		}
		return explode($pattern, $input);
	}


	/**
	 * @param string $input
	 *
	 * @return string
	 */
	public static function strip($input) {
		return trim($input);
	}
	
	
	/**
	 * Removes html tags from text
	 *
	 * @param string $input
	 *
	 * @return string
	 */
	public static function strip_html($input) {
		return is_string($input) ? strip_tags($input) : $input;
	}
	

	/**
	 * Strip all newlines (\n, \r) from string
	 *
	 * @param string $input
	 *
	 * @return string
	 */
	public static function strip_newlines($input) {
		return is_string($input) ? str_replace(array(
			"\n", "\r"
		), '', $input) : $input;
	}	
	

	/**
	 * multiplication
	 *
	 * @param int $input
	 * @param int $operand
	 *
	 * @return int
	 */
	public static function times($input, $operand) {
		return (int)$input * (int)$operand;
	}
	

	/**
	 * Truncate a string down to x characters
	 *
	 * @param string $input
	 * @param int $characters
	 * @param string $ending string to append if truncated
	 *
	 * @return string
	 */
	public static function truncate($input, $characters = 100, $ending = '...') {
		if (is_string($input) || is_numeric($input)) {
			if (strlen($input) > $characters) {
				return substr($input, 0, $characters) . $ending;
			}
		}

		return $input;
	}
		

	/**
	 * Truncate string down to x words
	 *
	 * @param string $input
	 * @param int $words
	 * @param string $ending string to append if truncated
	 *
	 * @return string
	 */
	public static function truncatewords($input, $words = 3, $ending = '...') {
		if (is_string($input)) {
			$wordlist = explode(" ", $input);

			if (count($wordlist) > $words) {
				return implode(" ", array_slice($wordlist, 0, $words)) . $ending;
			}
		}

		return $input;
	}	
	

	/**
	 * Remove duplicate elements from an array
	 *
	 * @param array|\Traversable $input
	 *
	 * @return array
	 */
	public static function uniq($input) {
		if ($input instanceof \Traversable) {
			$input = iterator_to_array($input);
		}
		return array_unique($input);
	}
	
	/**
	 * Convert an input to uppercase
	 *
	 * @param string $input
	 *
	 * @return string
	 */
	public static function upcase($input) {
		return is_string($input) ? strtoupper($input) : $input;
	}

	/**
	 * URL encodes a string
	 *
	 * @param string $input
	 *
	 * @return string
	 */
	public static function url_encode($input) {
		return urlencode($input);
	}

    /**
     * Translate from locales file
     *
     * @param string $input
     *
     * @return string
     */
	public static function t($input) {
	    if (empty($input)) {
	        return '';
        }
        $keys = self::_t_getKey($input);
        if (empty(Liquid::getLocaleArray())) {
            return '';
        }
        return Liquid::$propertyAccessor->getValue(Liquid::getLocaleArray(), $keys);
    }
    protected static function _t_getKey($input) {
        return '['.str_replace('.', '][', $input).']';
    }

    public static function get_build($name, $ext)
    {
        $html = '';
        $content = [];

        $file = Liquid::$project_dir.'/public/build/entrypoints.json';
        $app = !empty(Liquid::$project_env) ? Liquid::$project_env : 'prod';

        if (file_exists($file)) {
            $content_array = json_decode(file_get_contents($file), true);
            $content = isset($content_array['entrypoints'][$name][$ext]) ? $content_array['entrypoints'][$name][$ext] : [];
        }

        if ($content) {
            foreach ($content as $item)
            {
                if ($app == 'dev') {
                    $item .= '?'.time();
                }
                if ($ext == 'js') {
                    $html .= self::script_tag($item);
                }
                if ($ext == 'css') {
                    $html .= self::stylesheet_tag($item);
                }
            }
        }

        return $html;
    }

    public static function stylesheet_tag($url) {
        return '<link href="'.$url.'" rel="stylesheet">';
    }

    public static function script_tag($url) {
        return '<script src="'.$url.'" type="text/javascript"></script>';
    }
	
	/**
	 * Use overloading to get around reserved php words - in this case 'default'
	 *
	 * @param string $name
	 * @param array $arguments
	 *
	 * @return string
	 *
	 */
	public function __call($name, $arguments) {
        if ($name === 'default') {
            return $this->_default($arguments[0], $arguments[1]);
        }
    }

}
