<?php
$this->attach('listfiltered.js');
$this->attach('listfiltered.scss');
$files = scandir($this->path . '/images');
foreach ($files as $file) {
	if (is_file($this->path . '/images/' . $file)) {
		$this->loadRes('images/' . $file, $file);
	}
}
unset($files, $file);