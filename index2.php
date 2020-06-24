<?php

function str_replace_first($from, $to, $content){
    $from = '/'.preg_quote($from, '/').'/';
    return preg_replace($from, $to, $content, 1);
}

function extract_headings( &$find, &$replace, $content = '' ){
	$matches = array();
	$anchor = '';
	$items = false;
	
	if ( is_array($find) && is_array($replace) && $content ) {
		if ( preg_match_all('/(<h([1-6]{1})[^>]*>).*<\/h\2>/msuU', $content, $matches, PREG_SET_ORDER) ) {

			$new_matches = array();
			for ($i = 0; $i < count($matches); $i++) {
				if ( 
					in_array($matches[$i][2], array('1', '2', '3', '4', '5', '6')) 
					// && strpos($matches[$i][1], 'bluf-heading') !== false
				){
					$new_matches[] = $matches[$i];
				}
			}
			$matches = $new_matches;

			// remove empty headings
			$new_matches = array();
			for ($i = 0; $i < count($matches); $i++) {
				if ( trim( strip_tags($matches[$i][0]) ) != false )
					$new_matches[] = $matches[$i];
			}
			if ( count($matches) != count($new_matches) ){
				$matches = $new_matches;
			}

			for ($i = 0; $i < count($matches); $i++) {
				// get anchor and add to find and replace arrays
				$anchor = 'q'.($i+1);
				$find[] = $matches[$i][0];
				$h = str_replace('<h'.$matches[$i][2], '<h'.$matches[$i][2].' id="'.$anchor.'"', $matches[$i][0]);
				$content = str_replace_first($matches[$i][0], $h, $content);
				$replace[] = str_replace(
					array(
						$matches[$i][1],				// start of heading
						'</h' . $matches[$i][2] . '>'	// end of heading
					),
					array(
						$matches[$i][1] . '<span id="' . $anchor . '">',
						'</span></h' . $matches[$i][2] . '>'
					),
					$matches[$i][0]
				);

				$items .= '<li><a href="#' . $anchor . '">';
				$items .= count($replace) . '. ';
				$items .= strip_tags($matches[$i][0]) . '</a></li>';
			}
		}
	}
	
	return $items.$content;
}

function the_content( $content ){
	$items = $css_classes = $anchor = '';
	$find = $replace = array();

	$items = extract_headings($find, $replace, $content);
	$html = '<div id="toc_container" class="' . $css_classes . '">';
	$html .= '<ul class="toc_list">' . $items . '</ul></div>' . "\n";

	return $html;
}

$content = '
<hr>
<h1>Main Heading</h1>
<p>This is a paragraph</p>
<div class="table-of-contents"><div class="toc-title">Table of Contents</div><ul class="toc-list"></ul></div>
<h2>How to do it?</h2>
<p>This is a paragraph</p>
<h2 class="main">This is how</h2>
<div>This is a div</div>
<h3 class="underline-main"><span class="inner">This is just a h3</span></h3>
<span>This is a span</span>
<p>This is a paragraph</p>
<h2>This is the last h2</h2>
<p>This is a paragraph</p>
<h3>This is the last h3</h3>
<h4>One more h4 tho.</h4>
<p>End of content</p>
';

echo the_content($content);
?>