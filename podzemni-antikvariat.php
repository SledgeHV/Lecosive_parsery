<?php

/*
- parser pro podzemni-antikvariat.cz
- zpracuje URL kategorie a vygeneruje XML feed
- pouziti: ?url=https://www.podzemni-antikvariat.cz/katalog/sci-fi-komiks
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
		$products = $xpath->query("//div[contains(@class, 'kniha')]");

		foreach ($products as $key => $product) {		
			
			$title = $product->getElementsByTagName('h3');
			$link = $product->getElementsByTagName('a');
			$img = $product->getElementsByTagName('img');
			$description = $xpath->query(".//*[contains(concat(' ', normalize-space(@class), ' '), ' popis ')]", $product);
			
			if (is_object($title{0})) {
				$parsed[$key]['title'] = trim($title{0}->nodeValue);
				$parsed[$key]['link'] = 'http://' . $host . $link{0}->getAttribute('href');
				$parsed[$key]['img'] = 'http://' . $host . $img{0}->getAttribute('src');
				$parsed[$key]['description'] = $description{0}->nodeValue;
			}
		}

		echo '<?xml version="1.0" encoding="UTF-8" ?><rss version="2.0"><channel><title>' . html_entity_decode(get_page_title($src)) . '</title><link>' . $url . '</link>';

		foreach ($parsed as $single) {
			echo '<item>';
	    	echo '<title>' . html_entity_decode($single['title']) . '</title>';
	    	echo '<link>' . $single['link'] . '</link>';
	    	echo '<description><![CDATA[' . $single['description'] . '<br><img src="'. $single['img'] .'">]]></description>';
			echo '</item>';
		}

		echo '</channel></rss>';

	}

} else {
	echo '
	<html>
	<body>
		<p>Bacha: Katka zapomnela na <span style="text-decoration: line-through">okurku</span> URL. Nebo ji napsala blbe.</p>
		<p>Vzor: ?url=<em>https://www.podzemni-antikvariat.cz/katalog/sci-fi-komiks</em></p>
	</body>
	</html>';
}


?>