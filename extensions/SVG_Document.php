<?php

namespace li3_pdf\extensions;

use lithium\util\Set;

class SVG_Document extends SVG_Abstract {

	public $_y = null;
	protected $_pageCounter = 0;
	protected $_pageSet = [0];

	// Default A4 dimensions
	protected $_attrs = [
		'xmlns' => 'http://www.w3.org/2000/svg',
		'width' => '595',
		'height' => '842',
		'horizontalMargin' => 15,
		'verticalMargin' => 15,
		'lineHeight' => 12,
		'preserveAspectRatio' => 'xMinYMin meet'
	];

	protected $_layout = [
		'header' => null, 
		'footer' => null
	];

	public function attributes($attrs = []) {
		$this->_attrs = $attrs + $this->_attrs;
	}

	public function layout($key, $value) {
		$this->_layout = [$key => $value] + $this->_layout;
	}

	public function render() {
		$svg = '<?xml version="1.0" encoding="utf-8" standalone="yes"?>';
		$content = $this->_renderElements(1);
		foreach ($this->_pageSet as $index => $pageNumber) {
			$content = str_replace('<pageSetNumber'.$index.'/>', $pageNumber, $content);
		}
		$svg .= $this->_renderElement('svg', $this->_attrs, $content, 0, true);
		return $svg;
	}

	public function __get($name) {
		$elem = new SVG_Element($this, $name);
		$this->_elements[] = $elem;
		if ($name == 'page') {
			$this->_pageCounter++;
			$this->_pageSet[count($this->_pageSet) -1]++;
			$this->_y = $this->_attrs['verticalMargin'];
			foreach ($this->_layout as $closure) {
				if(is_callable($closure)) $closure($this, $elem);
			}
  		}
		return $elem;
	}

	function pagination() {
		return $this->_pageCounter.' / <pageSetNumber'.(count($this->_pageSet) -1).'/>';
	}

	function addPageSet() {
		$this->_pageCounter = 0;
		$this->_pageSet[] = 0;
	}

	function lf(&$page) {
		if ($this->_y + (2 * $this->_attrs['lineHeight']) >= $this->_attrs['height'] - $this->_attrs['verticalMargin']) {
			$page = $this->page;
		} else {
			$this->_y += $this->_attrs['lineHeight'];
		}
	}

	function cell(&$page, $x, $width, $height, $label = '', $attrs = [], $lf = false) {
		$default = [
			'rect' => [
				'fill' => 'none',
				'stroke' => '#BBBBBB',
				'stroke-width' => '0.5px',
				'margin-x' => 0,
				'margin-y' => 0
			],
			'text' => [
				'font-family' => 'Verdana',
				'font-size' => 7,
				'margin-x' => 0,
				'margin-y' => 8
			]
		];
		$default = Set::merge($default, $attrs);

		$rect = $page->rect;
		$rect
			->x($x + $default['rect']['margin-x'])
			->y($this->_y + $default['rect']['margin-y'])
			->width($width)
			->height($height)
			->parent();
		unset($default['rect']['margin-x']);
		unset($default['rect']['margin-y']);
		foreach ($default['rect'] as $attr => $value) {
			$rect->attr($attr, $value);
		}

		if ($label != '') {
			$text = $page->text;
			$text
				->x($x + $default['text']['margin-x'])
				->y($this->_y + $default['text']['margin-y'])
				->content($label)
				->parent();
			unset($default['text']['margin-x']);
			unset($default['text']['margin-y']);
			foreach ($default['text'] as $attr => $value) {
				$text->attr($attr, $value);
			}
		}

		if ($lf) {
			$this->lf($page);
		}
	}
}

?>