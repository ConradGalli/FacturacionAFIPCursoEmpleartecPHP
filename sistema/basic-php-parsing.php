<?php 

include("simple_html_dom.php");

$html = file_get_html('http://www.air-computers.com/air2011/index.php');

$scripts = $html->find('script');
    foreach($scripts as $s) {
        if(strpos($s->innertext, 'ndolar') !== false) {
            echo $s;
        }
    }

?>