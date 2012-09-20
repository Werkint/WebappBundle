<?php
namespace Werkint\Bundle\WebappBundle\Webapp\View;
use \Werkint\Bundle\Webapp\Twig;
use \JsMin, \MinifyCSS, \SassParser;

trait ViewHead {

	private $_head = array();
	private $headRes = array();
	private $headResNames = '';

	private $headVars = array();

	public function headAdd($html) {
		$this->_head = array_merge((array)$this->_head, array($html));
		return $this;
	}

	public function headRss($title, $link) {
		$this->headAdd('<link rel="alternate" type="application/rss+xml" title="' . htmlspecialchars($title) . '" href="' . $link . '" />');
		return $this;
	}

	public function headFavicon($name = 'favicon') {
		$this->headAdd('<link href="' . $name . '.png" rel="icon" type="image/png" />');
		return $this;
	}

	public function headVarAdd($name, $value) {
		$this->headVars[$name] = $value;
		return $this;
	}

	public function headVarsAdd($vars) {
		foreach ($vars as $k=> $v) {
			$this->headVarAdd($k, $v);
		}
		return $this;
	}

	public function headScript($name, $global = false, $vars = array()) {
		$name_abs = ($global ? '' : APP_DIR_WWW . APP_PATH_RES . '/') . $name;
		if (!file_exists($name_abs)) {
			return $this;
		}
		$this->headRes[] = array(
			'type'  => 'script',
			'name'  => $name,
			'data'  => file_get_contents($name_abs),
			'mtime' => filemtime($name_abs)
		);
		$this->headResNames .= "\n" . $name_abs;
		$this->headVarsAdd($vars);
		return $this;
	}

	public function headStyle($name, $global = false, $vars = array()) {
		$name_abs = ($global ? '' : APP_DIR_WWW . APP_PATH_RES . '/') . $name;
		if (!file_exists($name_abs)) {
			return $this;
		}
		$this->headRes[] = array(
			'type'  => 'style',
			'name'  => $name,
			'data'  => file_get_contents($name_abs),
			'mtime' => filemtime($name_abs)
		);
		$this->headResNames .= "\n" . $name_abs;
		$this->headVarsAdd($vars);
		return $this;
	}

	private function headFormScripts() {
		$this->headVarAdd('app-url', APP_URL);
		$this->headVarAdd('webapp-res', $this->prespath);
		$this->headVarAdd('app-res', APP_PATH_RES);
		if (!file_exists($this->presdir)) {
			mkdir($this->presdir);
		}

		$revision = file_exists($this->prevpath) ? file_get_contents($this->prevpath) : '';
		$revision = substr(crc32($revision), 0, 6);
		$filename = md5(join(',', array_keys($this->headVars)) . join(',', array_values($this->headVars)) . $this->headResNames) . '_rev' . $revision;
		$filedir = $this->presdir . '/' . $filename;
		$filepath = $this->prespath . '/' . $filename;

		$rewrite_css = file_exists($filedir . '.css') ? filemtime($filedir . '.css') : true;
		$rewrite_js = file_exists($filedir . '.js') ? filemtime($filedir . '.js') : true;
		if ($rewrite_css !== true && $rewrite_js !== true) {
			foreach ($this->headRes as $obj) {
				if ($obj['type'] == 'style') {
					if ($obj['mtime'] > $rewrite_css) {
						$rewrite_css = true;
						break;
					}
				} else if ($obj['type'] == 'script') {
					if ($obj['mtime'] > $rewrite_js) {
						$rewrite_js = true;
						break;
					}
				}
			}
		}
		$rewrite_css = $rewrite_css === true;
		$rewrite_js = $rewrite_js === true;

		if ($rewrite_js || $rewrite_css) {
			$data_styles = $data_scripts = array();
			$data_scripts[] = 'window.CONST = {};';
			foreach ($this->headVars as $k=> $v) {
				$data_styles[] = '$const-' . strtolower(str_replace('_', '-', $k)) . ': "' . str_replace('"', '\\"', $v) . '";';
				$data_scripts[] = 'window.CONST.' . strtolower(str_replace('-', '_', $k)) . ' = "' . str_replace('"', '\\"', $v) . '";';
			}
			foreach ($this->headRes as $obj) {
				if ($obj['type'] == 'style') {
					$data_styles[] = $obj['data'];
				} else if ($obj['type'] == 'script') {
					$data_scripts[] = $obj['data'];
				}
			}
		}

		if ($rewrite_css) {
			$parser = array(
				'style'     => 'nested',
				'cache'     => FALSE,
				'syntax'    => 'scss',
				'debug'     => APP_DEBUG
			);
			$parser = new SassParser($parser);
			try {
				$data_styles = $parser->toCss(join("\n", $data_styles), false);
			} catch (\Exception $e) {
				throw new \Exception('SCSS error: ' . $e->getMessage() . ', loaded files: ' . print_r($this->headResNames, true));
			}
			file_put_contents($filedir . '.css', $data_styles);
		}
		if ($rewrite_js) {
			$data_scripts = join("\n", $data_scripts);
			if (!APP_DEBUG) {
				\JsMin\Minify::minify($data_scripts);
			}
			file_put_contents($filedir . '.js', $data_scripts);
		}

		$this->headAdd('<link href="' . $filepath . '.css" rel="stylesheet" type="text/css" />');
		$this->headAdd('<script src="' . $filepath . '.js"></script>');
	}

	private $rendered;

	public function headRender() {
		if ($this->rendered) {
			return $this->rendered;
		}
		$this->events->trigger('headRender');
		$this->headFormScripts();
		$html = '';
		foreach ($this->_head as $str) {
			$html .= $str . "\n";
		}
		return $this->rendered = $html;
	}

}
