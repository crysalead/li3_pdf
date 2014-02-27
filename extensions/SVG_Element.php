<?php

namespace li3_pdf\extensions;

class SVG_Element extends SVG_Abstract {
	protected $_parent;
	protected $_tag;

	public function __construct(SVG_Abstract $parent, $tag) {
		$this->_parent = $parent;
		$this->_tag = $tag;
	}

	public function parent() {
		return $this->_parent;
	}

	public function render($indent_level) {
		$content = $this->_renderElements($indent_level + 1);
		return $this->_renderElement($this->_tag, $this->_attributes, $content, $indent_level);
	}
}
?>