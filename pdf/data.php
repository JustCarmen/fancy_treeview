<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

Zend_Session::writeClose();

$filename = WT_DATA_DIR . '/fancy_treeview_tmp.txt';
$content = WT_Filter::post('pdfContent');

// make our datafile if it does not exist.
if(!file_exists($filename)) {
	$handle = fopen($filename, 'w');
	@fclose($handle);
	@chmod($filename, WT_PERM_FILE);
}

// Let's make sure the file exists and is writable first.
if (is_writable($filename)) {

    if (!$handle = @fopen($filename, 'w')) {
         exit;
    }

    // Write the pdfContent to our data.txt file.
    if (@fwrite($handle, $content) === FALSE) {
        exit;
    }

    @fclose($handle);
}