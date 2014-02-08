<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

require_once(WT_MODULES_DIR.$this->getName().'/pdf/dompdf/dompdf_config.inc.php');
Zend_Loader_Autoloader::getInstance()->pushAutoloader('DOMPDF_autoload','');

// be sure the font directory is writable
$fontsDir = WT_MODULES_DIR.$this->getName().'/pdf/dompdf/lib/fonts';
if (is_dir($fontsDir) && !is_writable($fontsDir)) {
	@chmod($fontsDir, WT_PERM_EXE);
}

$filename = WT_DATA_DIR . '/fancy_treeview_tmp.txt';

if(!$filename || !is_writable($fontsDir)) {
	header('Location: module.php?mod='.$this->getName().'&mod_action=show&rootid='.WT_Filter::get('rootid'));
	WT_FlashMessages::addMessage(WT_I18N::translate('The pdf could not be generated.'));
	if (WT_USER_GEDCOM_ADMIN) {
		WT_FlashMessages::addMessage(WT_I18N::translate('Make sure the folders %s exists and are writable.', dirname($filename).' and '.WT_MODULES_DIR.$this->getName().'/pdf/dompdf/lib/fonts'));
	}	
}
else {
	$html =
	  '<html>
		<head>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
			<link type="text/css" href="style.css" rel="stylesheet" />
		</head>
		<body>'.@file_get_contents($filename).'</body>
	  </html>';
	
	$dompdf = new DOMPDF();
	$dompdf->set_base_path(WT_MODULES_DIR.$this->getName().'/pdf/'); // works only for the template file (set absolute links for images and all other links)
	$dompdf->set_paper('a3', 'portrait');
	$dompdf->load_html($html);
	$dompdf->render();
	
	// create the page
	$canvas				= $dompdf->get_canvas();
	$font 				= Font_Metrics::get_font("DejaVu Sans", "normal");
	$headertext_left 	= WT_SERVER_NAME.substr(WT_SCRIPT_PATH, 0, -1);
	$headertext_right 	= WT_I18N::translate('Page')." {PAGE_NUM} ".WT_I18N::translate('of')." {PAGE_COUNT} ";
	$headerpos_right	= $canvas->get_width() - $canvas->get_text_width($headertext_right, $font, 9, 0) + 100;
	
	$canvas->page_text(20, 10, $headertext_left, $font, 9, array(0,0,0));
	$canvas->page_text($headerpos_right, 10, $headertext_right, $font, 9, array(0,0,0));
	
	// pdf output
	$dompdf->stream(WT_Filter::get('title').'.pdf');
	
	// remove the temporary text file
	@unlink(WT_DATA_DIR . '/fancy_treeview_tmp.txt');
}
