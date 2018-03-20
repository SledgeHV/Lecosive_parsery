<?php

/*
- parser pro levneknihy.cz
- zpracuje URL kategorie a vygeneruje XML feed
- pouziti: ?url=https://www.levneknihy.cz/hry-a-puzzle/
*/

function get_page_title($str) {
  if (strlen($str)>0) {
    $str = trim(preg_replace('/\s+/', ' ', $str));
    preg_match("/\<title\>(.*)\<\/title\>/i",$str,$title);
    
    return trim($title[1]);
  }
}


/* MAIN */
libxml_use_internal_errors(true);

if (isset($_GET['url'])) {

	$url = $_GET['url'];

	if (($url) && filter_var($url, FILTER_VALIDATE_URL)) {

		$host = parse_url($url, PHP_URL_HOST);
		$src = file_get_contents($url);
		$src = mb_convert_encoding($src, 'HTML-ENTITIES', "UTF-8");

		$html = new DOMDocument();
		$html->loadHTML($src);
		$xpath = new DOMXPath($html);
		$products = $xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' product ')]");

		foreach ($products as $key => $product) {
			
			$title = $xpath->query(".//*[contains(concat(' ', normalize-space(@class), ' '), ' product-title ')]", $product);
			$price = $xpath->query(".//*[contains(concat(' ', normalize-space(@class), ' '), ' product-price ')]", $product);
			$link = $product->getElementsByTagName('a');
			$image = $product->getElementsByTagName('img');

			$title_span = $title{0}->getElementsByTagName('span');
			$title_span = $title_span{0}->nodeValue;

			$parsed[$key]['author'] = trim($title_span);
			$parsed[$key]['title'] = trim(str_replace($title_span, '', $title{0}->nodeValue));
			$parsed[$key]['price'] = trim(preg_replace( '/\s+/', " ", $price{0}->nodeValue));
			$parsed[$key]['link'] = 'http://' . $host . $link{0}->getAttribute('href');
			$parsed[$key]['img'] = 'http://' . $host . $image{0}->getAttribute('src');
		}

		echo '<?xml version="1.0" encoding="UTF-8" ?><rss version="2.0"><channel><title>' . html_entity_decode(get_page_title($src)) . '</title><link>' . $url . '</link>';

		foreach ($parsed as $single) {
			echo '<item>';
	    	echo '<title>' . html_entity_decode($single['author']) . ' - ' . html_entity_decode($single['title']) . '</title>';
	    	echo '<link>' . $single['link'] . '</link>';
	    	echo '<description><![CDATA[<img src="'.$single['img'].'">' . html_entity_decode($single['price']) . ']]></description>';
			echo '</item>';
		}

		echo '</channel></rss>';

	}

} else {
	echo '
	<html>
	<body>
		<p>Bacha: Katka zapomnela na <span style="text-decoration: line-through">okurku</span> URL. Nebo ji napsala blbe.</p>
		<p>Vzor: lk.php?url=<em>https://www.levneknihy.cz/hry-a-puzzle/</em></p>
	</body>
	</html>';
}



?>