	//Dummy HTML for testing
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


	//The Resulting HTML will look as follows:
	<h1>Main Heading</h1>
	<p>This is a paragraph</p>
	<div class="table-of-contents">
	<div class="toc-title">Table of Contents</div>
	<ul class="toc-list">
		<li><a title="Heading1" href="#q0">Heading1</a></li>
		<li><a title="Heading1" href="#q1">Heading2</a></li>
		<li><a title="Heading3" href="#q2">Heading3</a></li>
	</ul>
	</div>
	<h2 id="q0">How to do it?</h2>
	<p>This is a paragraph</p>
	<h2 class="main" id="q1">This is how</h2>
	<div>This is a div</div>
	<h3 class="underline-main"><span class="inner">This is just a h3</span></h3>
	<span>This is a span</span>
	<p>This is a paragraph</p>
	<h2 id="q2">This is the last h2</h2>
	<p>This is a paragraph</p>
	<h3>This is the last h3</h3>
	<h4>One more h4 tho.</h4>
	<p>End of content</p>

<?php

function url_anchor_target( $title ){
	$return = false;
	
	if ( $title ) {
		$return = trim( strip_tags($title) );

		// convert accented characters to ASCII 
		// $return = remove_accents( $return );
		
		// replace newlines with spaces (eg when headings are split over multiple lines)
		$return = str_replace( array("\r", "\n", "\n\r", "\r\n"), ' ', $return );
		
		// remove &amp;
		$return = str_replace( '&amp;', '', $return );
		
		// remove non alphanumeric chars
		$return = preg_replace( '/[^a-zA-Z0-9 \-_]*/', '', $return );
		
		// convert spaces to _
		$return = str_replace(
			array('  ', ' '),
			'_',
			$return
		);
		
		// remove trailing - and _
		$return = rtrim( $return, '-_' );
		
		$return = str_replace('_', '-', $return);
		$return = str_replace('--', '-', $return);
	}
	
	return $return;
}
		
		
function build_hierarchy( &$matches, $options ){
	$current_depth = 100;	// headings can't be larger than h6 but 100 as a default to be sure
	$html = '';
	$numbered_items = array();
	$numbered_items_min = null;

	
	// find the minimum heading to establish our baseline
	for ($i = 0; $i < count($matches); $i++) {
		if ( $current_depth > $matches[$i][2] )
			$current_depth = (int)$matches[$i][2];
	}
	
	$numbered_items[$current_depth] = 0;
	$numbered_items_min = $current_depth;

	for ($i = 0; $i < count($matches); $i++) {

		if ( $current_depth == (int)$matches[$i][2] )
			$html .= '<li>';
	
		// start lists
		if ( $current_depth != (int)$matches[$i][2] ) {
			for ($current_depth; $current_depth < (int)$matches[$i][2]; $current_depth++) {
				$numbered_items[$current_depth + 1] = 0;
				$html .= '<ul><li>';
			}
		}
		
		// list item
		if ( in_array($matches[$i][2], $options['heading_levels']) ) {
			$html .= '<a href="#' . url_anchor_target( $matches[$i][0] ) . '">';
			if ( $options['ordered_list'] ) {
				// attach leading numbers when lower in hierarchy
				$html .= '<span class="toc_number toc_depth_' . ($current_depth - $numbered_items_min + 1) . '">';
				for ($j = $numbered_items_min; $j < $current_depth; $j++) {
					$number = ($numbered_items[$j]) ? $numbered_items[$j] : 0;
					$html .= $number . '.';
				}
				
				$html .= ($numbered_items[$current_depth] + 1) . '</span> ';
				$numbered_items[$current_depth]++;
			}
			$html .= strip_tags($matches[$i][0]) . '</a>';
		}
		
		
		// end lists
		if ( $i != count($matches) - 1 ) {
			if ( $current_depth > (int)$matches[$i + 1][2] ) {
				for ($current_depth; $current_depth > (int)$matches[$i + 1][2]; $current_depth--) {
					$html .= '</li></ul>';
					$numbered_items[$current_depth] = 0;
				}
			}
			
			if ( $current_depth == (int)@$matches[$i + 1][2] )
				$html .= '</li>';
		}
		else {
			// this is the last item, make sure we close off all tags
			for ($current_depth; $current_depth >= $numbered_items_min; $current_depth--) {
				$html .= '</li>';
				if ( $current_depth != $numbered_items_min ) $html .= '</ul>';
			}
		}
	}

	return $html;
}

function extract_headings( &$find, &$replace, $content = '' ){
	$options = array(		// default options
		'fragment_prefix' => 'i',
		'position' => 1,
		'start' => 4,
		'show_heading_text' => true,
		'heading_text' => 'Contents',
		'auto_insert_post_types' => array('page'),
		'show_heirarchy' => true,
		'ordered_list' => true,
		'smooth_scroll' => false,
		'smooth_scroll_offset' => 30,
		'visibility' => true,
		'visibility_show' => 'show',
		'visibility_hide' => 'hide',
		'visibility_hide_by_default' => false,
		'width' => 'Auto',
		'width_custom' => '275',
		'width_custom_units' => 'px',
		'wrapping' => 0,
		'font_size' => '95',
		'font_size_units' => '%',
		'lowercase' => false,
		'hyphenate' => false,
		'bullet_spacing' => false,
		'include_homepage' => false,
		'exclude_css' => false,
		'exclude' => '',
		'heading_levels' => array('1', '2', '3', '4', '5', '6'),
		'restrict_path' => '',
		'css_container_class' => '',
		'sitemap_show_page_listing' => true,
		'sitemap_show_category_listing' => true,
		'sitemap_heading_type' => 3,
		'sitemap_pages' => 'Pages',
		'sitemap_categories' => 'Categories',
		'show_toc_in_widget_only' => false,
		'show_toc_in_widget_only_post_types' => array('page')
	);
	$matches = array();
	$anchor = '';
	$items = false;
	
	if ( is_array($find) && is_array($replace) && $content ) {
		// get all headings
		// the html spec allows for a maximum of 6 heading depths
		if ( preg_match_all('/(<h([1-6]{1})[^>]*>).*<\/h\2>/msuU', $content, $matches, PREG_SET_ORDER) ) {

			// remove undesired headings (if any) as defined by heading_levels
			if ( count($options['heading_levels']) != 6 ) {
				$new_matches = array();
				for ($i = 0; $i < count($matches); $i++) {
					if ( 
						in_array($matches[$i][2], $options['heading_levels']) 
						// && strpos($matches[$i][1], 'bluf-heading') !== false
					){
						$new_matches[] = $matches[$i];
					}
				}
				$matches = $new_matches;
			}

			// remove specific headings if provided via the 'exclude' property
			if ( $options['exclude'] ) {
				$excluded_headings = explode('|', $options['exclude']);
				if ( count($excluded_headings) > 0 ) {
					for ($j = 0; $j < count($excluded_headings); $j++) {
						// escape some regular expression characters
						// others: http://www.php.net/manual/en/regexp.reference.meta.php
						$excluded_headings[$j] = str_replace(
							array('*'), 
							array('.*'), 
							trim($excluded_headings[$j])
						);
					}

					$new_matches = array();
					for ($i = 0; $i < count($matches); $i++) {
						$found = false;
						for ($j = 0; $j < count($excluded_headings); $j++) {
							if ( @preg_match('/^' . $excluded_headings[$j] . '$/imU', strip_tags($matches[$i][0])) ) {
								$found = true;
								break;
							}
						}
						if (!$found) $new_matches[] = $matches[$i];
					}
					if ( count($matches) != count($new_matches) )
						$matches = $new_matches;
				}
			}

			// remove empty headings
			$new_matches = array();
			for ($i = 0; $i < count($matches); $i++) {
				if ( trim( strip_tags($matches[$i][0]) ) != false )
					$new_matches[] = $matches[$i];
			}
			if ( count($matches) != count($new_matches) )
				$matches = $new_matches;

			// check minimum number of headings
			if ( count($matches) >= $options['start'] ) {

				for ($i = 0; $i < count($matches); $i++) {
					// get anchor and add to find and replace arrays
					$anchor = url_anchor_target( $matches[$i][0] );
					$find[] = $matches[$i][0];
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

					// assemble flat list
					if ( !$options['show_heirarchy'] ) {
						$items .= '<li><a href="#' . $anchor . '">';
						if ( $options['ordered_list'] ) $items .= count($replace) . ' ';
						$items .= strip_tags($matches[$i][0]) . '</a></li>';
					}
				}

				// build a hierarchical toc?
				// we could have tested for $items but that var can be quite large in some cases
				if ( $options['show_heirarchy'] ) $items = build_hierarchy( $matches, $options );
				
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