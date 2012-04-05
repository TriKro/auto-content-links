<?php
/*
Plugin Name: Auto Content Links
Plugin URI: http://redyellow.co.uk/plugins/auto-content-links/
Description: Automatically make words in your posts into links
Author: Rich Gubby
Version: 1.4
Author URI: http://redyellow.co.uk/
*/

if(!is_admin())
{
	// Activate the plugin
	add_filter('the_content', 'autoContentLinksContent');
} else
{
	require_once('control_panel.php');
	new autoContentLinksControlPanel(plugin_basename(__FILE__));
}

if(!function_exists('autoContentLinksCurrentPageURL'))
{
	function autoContentLinksCurrentPageURL()
	{
		// Construct current page
		$currentPageURL = 'http';
		if ($_SERVER['HTTPS'] == 'on') $currentPageURL .= 's';
		
		$currentPageURL .= '://';
		if ($_SERVER['SERVER_PORT'] != '80') 
		{
			$currentPageURL .= $_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].$_SERVER['REQUEST_URI'];
		} else 
		{
			$currentPageURL .= $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
		}
		return $currentPageURL;
	}
}

if(!function_exists('autoContentLinksContent'))
{
	/**
	 * Take original content, apply replacement links to it, and return it
	 * @param string $content
	 * @access public
	 * @since 1.0
	 * @return string
	 */
	function autoContentLinksContent($content)
	{
		$options = get_option('auto_content_links');
		
		// Set default value for autolink linking back to itself
		if(!isset($link['link_autolink'])) $link['link_autolink'] = true;
		
		if(isset($options['links']) AND !empty($options['links']))
		{
			foreach($options['links'] as $link)
			{
				if(!(preg_match("@".preg_quote($link['url']) .'$@', autoContentLinksCurrentPageURL())) OR $link['link_autolink'] == true)
				{
					$wordBoundary = '';
					if($link['match_whole_word'] == true) $wordBoundary = '\b';
					
					$newWindow = '';
					if($link['new_window'] == true) $newWindow = ' target="_blank"';
					
					$content = preg_replace('@('.$wordBoundary.$link['name'].$wordBoundary.')(?!([^<>]*<[^Aa<>]+>)*([^<>]+)?</[aA]>)(?!([^<\[]+)?[>\]])@', '<a'.$newWindow.' href="'.$link['url'].'">'.$link['name'].'</a>', $content, $link['instances']);
				}			
			}
		}
		return $content;
	}
}
