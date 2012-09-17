<?php
$this->attach('jquery.ui.js');
$this->attach('jquery.ui.scss');
$files = scandir($this->path . '/images');
foreach ($files as $file) {
	if (is_file($this->path . '/images/' . $file)) {
		$this->loadRes('images/' . $file, $file);
	}
}
unset($files, $file);