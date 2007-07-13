<?php
/*
+----------------------------------------------------------------+
|																							|
|	WordPress 2.1 Plugin: WP-PostViews 1.20									|
|	Copyright (c) 2007 Lester "GaMerZ" Chan									|
|																							|
|	File Written By:																	|
|	- Lester "GaMerZ" Chan															|
|	- http://www.lesterchan.net													|
|																							|
|	File Information:																	|
|	- Post Views Options Page														|
|	- wp-content/plugins/postviews/postviews-options.php				|
|																							|
+----------------------------------------------------------------+
*/


### Variables Variables Variables
$base_name = plugin_basename('postviews/postviews-options.php');
$base_page = 'admin.php?page='.$base_name;
$id = intval($_GET['id']);
$mode = trim($_GET['mode']);
$views_settings = array('views_options', 'widget_views_most_viewed');
$views_postmetas = array('views');


### Form Processing 
if(!empty($_POST['do'])) {
	// Decide What To Do
	switch($_POST['do']) {
		case __('Update Options', 'wp-postviews'):
			$views_options = array();
			$views_options['count'] = intval($_POST['views_count']);
			$views_options['template'] =  addslashes(trim($_POST['views_template_template']));
			$update_views_queries = array();
			$update_views_text = array();
			$update_views_queries[] = update_option('views_options', $views_options);
			$update_views_text[] = __('Post Views Options', 'wp-postviews');
			$i=0;
			$text = '';
			foreach($update_views_queries as $update_views_query) {
				if($update_views_query) {
					$text .= '<font color="green">'.$update_views_text[$i].' '.__('Updated', 'wp-postviews').'</font><br />';
				}
				$i++;
			}
			if(empty($text)) {
				$text = '<font color="red">'.__('No Post Views Option Updated', 'wp-postviews').'</font>';
			}
			break;
		//  Uninstall WP-PostViews
		case __('UNINSTALL WP-PostViews', 'wp-postviews') :
			if(trim($_POST['uninstall_views_yes']) == 'yes') {
				echo '<div id="message" class="updated fade">';
				echo '<p>';
				foreach($views_settings as $setting) {
					$delete_setting = delete_option($setting);
					if($delete_setting) {
						echo '<font color="green">';
						printf(__('Setting Key \'%s\' has been deleted.', 'wp-postviews'), "<strong><em>{$setting}</em></strong>");
						echo '</font><br />';
					} else {
						echo '<font color="red">';
						printf(__('Error deleting Setting Key \'%s\'.', 'wp-postviews'), "<strong><em>{$setting}</em></strong>");
						echo '</font><br />';
					}
				}
				echo '</p>';
				echo '<p>';
				foreach($views_postmetas as $postmeta) {
					$remove_postmeta = $wpdb->query("DELETE FROM $wpdb->postmeta WHERE meta_key = '$postmeta'");
					if($remove_postmeta) {
						echo '<font color="green">';
						printf(__('Post Meta Key \'%s\' has been deleted.', 'wp-postviews'), "<strong><em>{$postmeta}</em></strong>");
						echo '</font><br />';
					} else {
						echo '<font color="red">';
						printf(__('Error deleting Post Meta Key \'%s\'.', 'wp-postviews'), "<strong><em>{$postmeta}</em></strong>");
						echo '</font><br />';
					}
				}
				echo '</p>';
				echo '</div>'; 
				$mode = 'end-UNINSTALL';
			}
			break;
	}
}


### Determines Which Mode It Is
switch($mode) {
		//  Deactivating WP-PostViews
		case 'end-UNINSTALL':
			$deactivate_url = 'plugins.php?action=deactivate&amp;plugin=postviews/postviews.php';
			if(function_exists('wp_nonce_url')) { 
				$deactivate_url = wp_nonce_url($deactivate_url, 'deactivate-plugin_postviews/postviews.php');
			}
			echo '<div class="wrap">';
			echo '<h2>'.__('Uninstall WP-PostViews', 'wp-postviews').'</h2>';
			echo '<p><strong>'.sprintf(__('<a href="%s">Click Here</a> To Finish The Uninstallation And WP-PostViews Will Be Deactivated Automatically.', 'wp-postviews'), $deactivate_url).'</strong></p>';
			echo '</div>';
			break;
	// Main Page
	default:
		$views_options = get_option('views_options');
?>
<script type="text/javascript">
	/* <![CDATA[*/
	function views_default_templates(template) {
		var default_template;
		switch(template) {
			case 'template':
				default_template = "<?php _e('%VIEW_COUNT% views', 'wp-postviews'); ?>";
				break;
		}
		document.getElementById("views_template_" + template).value = default_template;
	}
	/* ]]> */
</script>
<?php if(!empty($text)) { echo '<!-- Last Action --><div id="message" class="updated fade"><p>'.$text.'</p></div>'; } ?>
<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>"> 
<div class="wrap"> 
	<h2><?php _e('Post Views Options', 'wp-postviews'); ?></h2> 
	<fieldset class="options">
		<legend><?php _e('Post Views Options', 'wp-postviews'); ?></legend>
		<table width="100%"  border="0" cellspacing="3" cellpadding="3">
			 <tr valign="top">
				<th align="left" width="30%"><?php _e('Count Views From:', 'wp-postviews'); ?></th>
				<td align="left">
					<select name="views_count" size="1">
						<option value="0"<?php selected('0', $views_options['count']); ?>><?php _e('Everyone', 'wp-postviews'); ?></option>
						<option value="1"<?php selected('1', $views_options['count']); ?>><?php _e('Guests Only', 'wp-postviews'); ?></option>
						<option value="2"<?php selected('2', $views_options['count']); ?>><?php _e('Registered Users Only', 'wp-postviews'); ?></option>
					</select>
				</td>
			</tr>
			<tr valign="top">
				<th align="left" width="30%"><?php _e('Views Template:', 'wp-postviews'); ?></th>
				<td align="left">
					<input type="text" id="views_template_template" name="views_template_template" size="70" value="<?php echo htmlspecialchars(stripslashes($views_options['template'])); ?>" /><br />
						<?php _e('HTML is allowed.', 'wp-postviews'); ?><br />
						%VIEW_COUNT% - <?php _e('The number of views.', 'wp-postviews'); ?><br />
						<input type="button" name="RestoreDefault" value="<?php _e('Restore Default Template', 'wp-postviews'); ?>" onclick="views_default_templates('template');" class="button" />
				</td>
			</tr>
		</table>
	</fieldset>
	<div align="center">
		<input type="submit" name="do" class="button" value="<?php _e('Update Options', 'wp-postviews'); ?>" />&nbsp;&nbsp;<input type="button" name="cancel" value="<?php _e('Cancel', 'wp-postviews'); ?>" class="button" onclick="javascript:history.go(-1)" /> 
	</div>
</div>
</form> 

<!-- Uninstall WP-PostViews -->
<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>"> 
<div class="wrap"> 
	<h2><?php _e('Uninstall WP-PostViews', 'wp-postviews'); ?></h2>
	<p style="text-align: left;">
		<?php _e('Deactivating WP-PostViews plugin does not remove any data that may have been created, such as the views data. To completely remove this plugin, you can uninstall it here.', 'wp-postviews'); ?>
	</p>
	<p style="text-align: left; color: red">
		<strong><?php _e('WARNING:', 'wp-postviews'); ?></strong><br />
		<?php _e('Once uninstalled, this cannot be undone. You should use a Database Backup plugin of WordPress to back up all the data first.', 'wp-postviews'); ?>
	</p>
	<p style="text-align: left; color: red">
		<strong><?php _e('The following WordPress Options/PostMetas will be DELETED:', 'wp-postviews'); ?></strong><br />
	</p>
	<table width="70%"  border="0" cellspacing="3" cellpadding="3">
		<tr class="thead">
			<td align="center"><strong><?php _e('WordPress Options', 'wp-postviews'); ?></strong></td>
			<td align="center"><strong><?php _e('WordPress PostMetas', 'wp-postviews'); ?></strong></td>
		</tr>
		<tr>
			<td valign="top" style="background-color: #eee;">
				<ol>
				<?php
					foreach($views_settings as $settings) {
						echo '<li>'.$settings.'</li>'."\n";
					}
				?>
				</ol>
			</td>
			<td valign="top" style="background-color: #eee;">
				<ol>
				<?php
					foreach($views_postmetas as $postmeta) {
						echo '<li>'.$postmeta.'</li>'."\n";
					}
				?>
				</ol>
			</td>
		</tr>
	</table>
	<p>&nbsp;</p>
	<p style="text-align: center;">
		<input type="checkbox" name="uninstall_views_yes" value="yes" />&nbsp;<?php _e('Yes', 'wp-postviews'); ?><br /><br />
		<input type="submit" name="do" value="<?php _e('UNINSTALL WP-PostViews', 'wp-postviews'); ?>" class="button" onclick="return confirm('<?php _e('You Are About To Uninstall WP-PostViews From WordPress.\nThis Action Is Not Reversible.\n\n Choose [Cancel] To Stop, [OK] To Uninstall.', 'wp-postviews'); ?>')" />
	</p>
</div> 
</form>
<?php
} // End switch($mode)
?>