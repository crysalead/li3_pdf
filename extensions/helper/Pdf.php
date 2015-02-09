<?php 
/**
 * li3_pdf: Pdf for Lithium
 *
 */
namespace li3_pdf\extensions\helper;

class Pdf extends \lithium\template\Helper{
	
	protected $_classes = array(
		'svg' => 'li3_pdf\extensions\SVG_Document'
	);
	
	public $_svg = null;
	
	public function _init($attrs = []){
		parent::_init();
		$this->_svg = new $this->_classes['svg']();
	}

	public function __get($name) {
		return $this->_svg->{$name};
	}

	public function __call($name, array $arguments = []) {
		$params = [];
		foreach ($arguments as $index => $arg) {
			$params[] = &$arguments[$index];
		}
		return call_user_func_array([&$this->_svg, $name], $params);
	}

	public function render() {
		$doc = $this->_svg->render();
		$code = explode('<page', $doc);
		$header = $code[0];
		$code = explode('/page>', $doc);
		$footer = $code[count($code) - 1];
		preg_match_all('/<page>(.*?)<\/page>/s', $doc, $body);
		$files = [];
		foreach ($body[1] as $index => $code) {
			$tmp = tempnam(sys_get_temp_dir(), 'Svg');
			file_put_contents($tmp, $header.$code.$footer);
			$files[] = $tmp;
		}
		$tmp = tempnam(sys_get_temp_dir(), 'Pdf');
		$cmd = 'rsvg-convert -f pdf -o '.$tmp;
		foreach ($files as $file) {
			$cmd .= ' '.$file;
		}
		`$cmd`;
		return file_get_contents($tmp);
	}

	function lf(&$page) {
		$this->_svg->lf($page);
	}

	function cell(&$page, $x, $width, $height, $label = '', $attrs = [], $lf = false) {
		$this->_svg->cell($page, $x, $width, $height, $label, $attrs, $lf);
	}

}
?>
