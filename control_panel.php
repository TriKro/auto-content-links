<?php
class autoContentLinksControlPanel
{
	/**
	 * Plugin options
	 * @var array
	 * @access public
	 */
	var $options = array();
	
	/**
	 * PHP5 constructor - links to old style PHP4 constructor
	 * @param string $file
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	function __construct($file)
	{
		$this->autoContentLinksControlPanel($file);
	}
	
	/**
	 * Old style PHP4 constructor
	 * @param string $file
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	function autoContentLinksControlPanel($file)
	{
		// Add Settings link to plugin page
		add_filter("plugin_action_links_".$file, array($this, 'actlinks'));
		// Any settings to initialize
		add_action('admin_init', array($this, 'adminInit'));
		// Load menu page
		add_action('admin_menu', array($this, 'addAdminPage'));
		// Load admin CSS style sheet
		add_action('admin_head', array($this, 'registerHead'));
	}

	function adminScripts()
	{
		wp_enqueue_script('jquery-ui-sortable');
	}
	
	/**
	 * Add a setting link to the plugin settings page
	 * @param array $links
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	function actlinks($links)
	{
		// Add a link to this plugins settings page
		$settings_link = '<a href="options-general.php?page=auto-content-links">Settings</a>'; 
		array_unshift($links, $settings_link); 
		return $links; 
	}
	
	/**
	 * Initialize admin
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	function adminInit()
	{
		register_setting('autoContentLinksOptions', 'auto_content_links');
	}
	
	/**
	 * Add an admin page to the general settings panel
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	function addAdminPage()
	{
		$adminPage = add_options_page('Auto Content Links Options', 'Auto Content Links', 'administrator', 'auto-content-links', array($this, 'admin'));
		add_action('load-'.$adminPage, array($this, 'adminScripts'));
	}
	
	/**
	 * Admin page
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	function admin()
	{
		echo '<div class="wrap">';
		echo '<div class="half1">';
		echo '<form method="post" action="">';
		
		echo '<h2>Auto Content Links Settings</h2>';
		echo '<p><small>By: Rich Gubby</small></p>';
		echo '<p>A word of warning: Don\'t enter words and URLs that loop on each other - it only gets the plugin in a mess and ultimately makes your blog post look rubbish!</p>';
		echo '<table class="form-table" cellspacing="2" cellpadding="5">';
		
		settings_fields('autoContentLinksOptions');
		$this->options = get_option('auto_content_links');
		
		$update = false;
		if(isset($_REQUEST['new_option_name']) AND $_REQUEST['new_option_name'] != '')
		{
			if(!isset($this->options['keys'])) $this->options['keys'] = array();
			if(!array_key_exists($_REQUEST['new_option_name'], $this->options['keys']))
			{
				$this->options['links'][] = array(
					'name' => strip_tags(stripslashes($_REQUEST['new_option_name'])), 
					'url' => strip_tags(stripslashes($_REQUEST['new_option_url'])),
					'instances' => strip_tags(stripslashes($_REQUEST['new_option_instances'])),
					'match_whole_word' => strip_tags(stripslashes($_REQUEST['new_option_match_whole_word'])),
					'new_window' => strip_tags(stripslashes($_REQUEST['new_option_new_window'])),
					'link_autolink' => strip_tags($_REQUEST['new_option_link_autolink'])
				);
				$this->options['keys'][$_REQUEST['new_option_name']] = true;
				$update = true;
			}	
		}
		
	
		foreach($_REQUEST as $key => $val)
		{
			// Check if we're trying to delete a key
			if(strpos($key, 'deleteoption_') === 0)
			{
				$delete = $this->options['links'][str_replace('deleteoption_', '', $key)];
				unset($this->options['links'][str_replace('deleteoption_', '', $key)]);
				unset($this->options['keys'][$delete['name']]);

				$update = true;
			}
			// Check if we're updating a value
			if(strpos($key, 'link_') === 0)
			{
				// Update value
				$updateKey = substr($key, 5, 1);
				$update = $this->options['links'][$updateKey];
				
				if($key == 'link_'.substr($key,5,1).'_name') $this->options['links'][$updateKey]['name'] = strip_tags(stripslashes($val));
				if($key == 'link_'.substr($key,5,1).'_url') $this->options['links'][$updateKey]['url'] = strip_tags(stripslashes($val));
				if($key == 'link_'.substr($key,5,1).'_instances') $this->options['links'][$updateKey]['instances'] = strip_tags(stripslashes($val));
				if($key == 'link_'.substr($key,5,1).'_match_whole_word') $this->options['links'][$updateKey]['match_whole_word'] = strip_tags(stripslashes($val));
				if($key == 'link_'.substr($key,5,1).'_new_window') $this->options['links'][$updateKey]['new_window'] = strip_tags(stripslashes($val));
				if($key == 'link_'.substr($key,5,1).'_link_autolink') $this->options['links'][$updateKey]['link_autolink'] = strip_tags(stripslashes($val));
				
				$update = true;
			}
		}

		if($update == true)
		{
			update_option('auto_content_links', $this->options);
			echo "<div class='updated fade'><p><strong>Settings saved</strong></p></div>";
		}

		?>
		</table>
		<script>
			jQuery(document).ready( function() {
				var fixHelper = function(e, ui) {
					ui.children().each(function() {
							jQuery(this).width(jQuery(this).width());
					});
					return ui;
				};
 
				jQuery(".sortable tbody").sortable({
					helper: fixHelper,
					items: "tr:not(.new_link)"
				});
			});
			
			function storeOrder() {
				jQuery("tbody tr:not(.new_link)").each(function(pos, e) {
					jQuery(this).find('input[name$="_name"]').attr('name', 'link_' + pos + '_name');
					jQuery(this).find('input[name$="_url"]').attr('name', 'link_' + pos + '_url');
					jQuery(this).find('input[name$="_instances"]').attr('name', 'link_' + pos + '_instances');
					jQuery(this).find('select[name$="_match_whole_word"]').attr('name', 'link_' + pos + '_match_whole_word');
					jQuery(this).find('select[name$="_new_window"]').attr('name', 'link_' + pos + '_new_window');
					jQuery(this).find('select[name$="_link_autolink"]').attr('name', 'link_' + pos + '_link_autolink');					
				});
			}
		</script>
		<table class="sortable">
			<thead>
				<tr>
					<td></td>
					<td><?php _e('Name') ?></td>
					<td><?php _e('URL') ?></td>
					<td><?php _e('How many replacements') ?></td>
					<td><?php _e('Only match whole words') ?></td>
					<td><?php _e('Open link in new window') ?></td>
					<td><?php _e('Link autolink with same URL back to itself') ?></td>
					<td>&nbsp;</td>
				</tr>
			</thead>
			<tr class="new_link">
				<td></td>
				<td><input type="text" name="new_option_name" /></td>
				<td><input class="regular-text" type="text" name="new_option_url" /></td>
				<td><input class="small-text" type="text" name="new_option_instances" value="2" /></td>
				<td>
					<select name="new_option_match_whole_word">
						<option value="0">No</option>
						<option value="1" selected="selected">Yes</option>
					</select>
				</td>
				<td>
					<select name="new_option_new_window">
						<option value="0" selected="selected">No</option>
						<option value="1">Yes</option>
					</select>
				</td>
				<td>
					<select name="new_option_link_autolink">
						<option value="0" selected="selected">No</option>
						<option value="1">Yes</option>
					</select>
				</td>
				<td>
					<input class="button-secondary" type="submit" value="<?php _e('Delete') ?>" name="deleteoption_<?php echo $key ?>" onclick="return confirm('<?php _e('Are you sure?') ?>');" />
					<input class="button-primary" type="submit" value="<?php _e('Add') ?>" />
				</td>
			</tr>
		<?php
		if(isset($this->options['links']) AND !empty($this->options['links']))
		{
			foreach($this->options['links'] as $key => $link)
			{
				?>
				<tr>
					<td><img src="<?php echo WP_PLUGIN_URL.'/'.basename(dirname(__FILE__)).'/move.png' ?>" /></td>
					<td><input type="text" name="link_<?php echo $key ?>_name" value="<?php echo $link['name']?>" /></td>
					<td><input type="text" class="regular-text" name="link_<?php echo $key ?>_url"       value="<?php echo $link['url'] ?>" /></td>
					<td><input type="text" class="small-text"   name="link_<?php echo $key ?>_instances" value="<?php echo $link['instances'] ?>" /></td>
					<td>
						<select name="link_<?php echo $key ?>_match_whole_word">
							<option value="0" <?php selected(0, $link['match_whole_word']) ?>>No</option>
							<option value="1" <?php selected(1, $link['match_whole_word']) ?>>Yes</option>
						</select>
					</td>
					<td>
						<select name="link_<?php echo $key ?>_new_window">
							<option value="0" <?php selected(0, $link['new_window']) ?>>No</option>
							<option value="1" <?php selected(1, $link['new_window']) ?>>Yes</option>
						</select>
					</td>
					<td>
						<select name="link_<?php echo $key ?>_link_autolink">
							<option value="0" <?php selected(0, $link['link_autolink']) ?>>No</option>
							<option value="1" <?php selected(1, $link['link_autolink']) ?>>Yes</option>
						</select>
					</td>
					<td>
						<input class="button-secondary" type="submit" value="<?php _e('Delete') ?>" name="deleteoption_<?php echo $key ?>" onclick="return confirm('<?php _e('Are you sure?') ?>');" />
						<input class="button-primary" type="submit" value="<?php _e('Save') ?>" onclick="storeOrder();" />
					</td>
				</tr>
				<?php
			}
		}
		
		echo '</table><br />';
		echo '</form><p>&nbsp;</p></div>
		
		<div class="half2">
			<h3>Donate</h3>
			<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=rgubby%40googlemail%2ecom&lc=GB&item_name=Richard%20Gubby%20%2d%20WordPress%20plugins&currency_code=GBP&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted"><img class="floatright" src="'.WP_PLUGIN_URL.'/'.basename(dirname(__FILE__)).'/donate.png" /></a>
			<p>If you like this plugin, keep it Ad free and in a constant state of development by <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=rgubby%40googlemail%2ecom&lc=GB&item_name=Richard%20Gubby%20%2d%20WordPress%20plugins&currency_code=GBP&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted">donating</a> to the cause!</p> 
			<h3>Follow me</h3>
			<p>
			<a href="http://twitter.com/zqxwzq"><img class="floatleft" src="'.WP_PLUGIN_URL.'/'.basename(dirname(__FILE__)).'/follow.png" /></a>
			<p>I\'m on Twitter - make sure you <a href="http://twitter.com/zqxwzq">follow me</a>!</p>
			
			<h3>Other plugins you might like...</h3>
			<h4>Wapple Architect Mobile Plugin</h4>
			<a href="plugin-install.php?tab=search&type=term&s=wapple"><img class="floatright" src="'.WP_PLUGIN_URL.'/'.basename(dirname(__FILE__)).'/WAMP.png" alt="Wapple Architect Mobile Plugin" title="Wapple Architect Mobile Plugin" /></a>
			<p>The Wapple Architect Mobile Plugin for WordPress mobilizes your blog so your visitors can read your posts whilst they are on their mobile phone!</p>
			<p>Head over to <a href="http://wordpress.org/extend/plugins/wapple-architect/">http://wordpress.org/extend/plugins/wapple-architect/</a> and install it now
			or jump straight to the <a href="plugin-install.php?tab=search&type=term&s=wapple">Plugin Install Page</a></p>
			
			<h4>WordPress Mobile Admin</h4>
			<a href="plugin-install.php?tab=search&type=term&s=wordpress+mobile+admin+wapple"><img class="title floatleft" src="'.WP_PLUGIN_URL.'/'.basename(dirname(__FILE__)).'/WMA.png" alt="WordPress Mobile Admin" title="WordPress Mobile Admin" /></a>
			<p>WordPress Mobile Admin allows you to create posts from your 
			mobile, upload photots, moderate comments and perform basic post/page management.</p>
			<p>Download it from <a href="http://wordpress.org/extend/plugins/wordpress-mobile-admin/">http://wordpress.org/extend/plugins/wordpress-mobile-admin/</a> or
			jump straight to the <a href="plugin-install.php?tab=search&type=term&s=wordpress+mobile+admin+wapple">Plugin Install Page</a>
		</div>
		</div>';
	}
	
	/**
	 * Add styles to admin header
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	function registerHead()
	{
		$url = WP_PLUGIN_URL.'/'.basename(dirname(__FILE__)).'/auto-content-links.css';
		echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"".$url."\" />\n";	
	}
}
