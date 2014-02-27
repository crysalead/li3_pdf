<?php

namespace li3_pdf\extensions;

use lithium\util\Inflector;

class SVG_Abstract {
	const INDENT_SIZE = 4;

	protected $_attributes = array();

	protected $_elements = array();

	protected function _renderElements($indent_level) {
		$code = null;

		foreach ($this->_elements as $elem) {
			$code .= $elem->render($indent_level);
		}

		return $code;
	}

	protected function _renderElement($tag, $attrs = array(), $content = null, $indent_level, $force_content = false) {
		$elem = PHP_EOL;

		$indent = $indent_level * self::INDENT_SIZE;
		$elem .= str_repeat(' ', $indent);

		$elem .= '<';
		$elem .= $tag;

		if (sizeof($attrs)) {
			foreach ($attrs as $name => $value) {
				if ($name == 'content') {
					$content = $value;
					continue;
				}
				$elem .= ' ';
				//$elem .= str_replace('_', '-', Inflector::underscore($name)) . '="' . $value . '"';
				$elem .= $name . '="' . $value . '"';
			}
		}

		if (null !== $content || $force_content) {
			$elem .= '>';
			$elem .= $content;

			$elem .= PHP_EOL;

			$elem .= str_repeat(' ', $indent);
			$elem .= '</' . $tag . '>';
		} else {
			$elem .= ' />';
		}

		return $elem;
	}

	public function attr($name, $value) {
		$this->_attributes[$name] = $value;
		return $this;
	}

	public function __get($name) {
		$elem = new SVG_Element($this, $name);
		$this->_elements[] = $elem;
		return $elem;
	}

	public function __call($name, $arguments) {
		return $this->attr($name, array_shift($arguments));
	}
}

?>