<html xmlns="http://www.w3.org/1999/xhtml">

<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?php

include_once('../simple_html_dom.php');

$html= file_get_html('http://www.gudriem.lv/degviela?lng=lv');

$table = $html->find('table[id=tresult]');




foreach ($table as $v){

	foreach($v->find('script') as $e) {
		$e->outertext= '';
		}
	foreach ($v->find('img') as $e){
		$e->outertext='';
		}
	
	foreach($v->find('div[class=tooltip_small]') as $e)
		$e->style= '';
		
    echo $v.'<br>';
}
?>