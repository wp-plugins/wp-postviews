<?php
/*
Plugin Name: WP-PostViews
Plugin URI: http://www.lesterchan.net/portfolio/programming.php
Description: Enables You To Display How Many Time A Post Had Been Viewed.
Version: 1.00
Author: GaMerZ
Author URI: http://www.lesterchan.net
*/


/*  Copyright 2005  Lester Chan  (email : gamerz84@hotmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/


### Function: Calculate Post Views
add_action('loop_start', 'process_postviews');
function process_postviews() {
	global $id;
	$post_views = intval(post_custom('views'));
	if(empty($_COOKIE[USER_COOKIE])) {
		if(is_single() || is_page()) {		
			if($post_views > 0) {
				update_post_meta($id, 'views', ($post_views+1));	
			} else {
				add_post_meta($id, 'views', 1);
			}
		}
	}
}


### Function: Display The Post Views
function the_views($text_views = 'Views', $display = true) {
	$post_views = intval(post_custom('views'));
	if($display) {
		echo $post_views.' '.$text_views;
	} else {
		return $post_views;
	}
}


### Function: Display Most Viewed Page/Post
if(!function_exists('get_most_viewed')) {
	function get_most_viewed($mode = '', $limit = 10) {
		global $wpdb, $post;
		$where = '';
		if($mode == 'post') {
			$where = 'post_status = \'publish\'';
		} elseif($mode == 'page') {
			$where = 'post_status = \'static\'';
		} else {
			$where = '(post_status = \'publish\' OR post_status = \'static\')';
		}
		$most_viewed = $wpdb->get_results("SELECT $wpdb->posts.ID, post_title, post_name, post_status, post_date, CAST(meta_value AS UNSIGNED) AS views FROM $wpdb->posts LEFT JOIN $wpdb->postmeta ON $wpdb->postmeta.post_id = $wpdb->posts.ID WHERE post_date < '".current_time('mysql')."' AND $where AND meta_key = 'views' AND post_password = '' ORDER  BY views DESC LIMIT $limit");
		if($most_viewed) {
			foreach ($most_viewed as $post) {
				$post_title = htmlspecialchars(stripslashes($post->post_title));
				$post_views = intval($post->views);
				echo "<li><a href=\"".get_permalink()."\">$post_title</a> ($post_views ".__('Views').")</li>";
			}
		} else {
			echo '<li>'.__('N/A').'</li>';
		}
	}
}


### Added by Paolo Tagliaferri (http://www.vortexmind.net - webmaster@vortexmind.net)
function get_timespan_most_viewed($mode = '', $limit = 10,$days = 7) {
	global $wpdb, $post;	
	$limit_date = current_time('timestamp') - ($days*86400); 
	$limit_date = date("Y-m-d H:i:s",$limit_date);	
	$where = '';
	if($mode == 'post') {
		$where = 'post_status = \'publish\'';
	} elseif($mode == 'page') {
		$where = 'post_status = \'static\'';
	} else {
		$where = '(post_status = \'publish\' OR post_status = \'static\')';
	}
	$most_viewed = $wpdb->get_results("SELECT $wpdb->posts.ID, post_title, post_name, post_status, post_date, CAST(meta_value AS UNSIGNED) AS views FROM $wpdb->posts LEFT JOIN $wpdb->postmeta ON $wpdb->postmeta.post_id = $wpdb->posts.ID WHERE post_date < '".current_time('mysql')."' AND post_date > '".$limit_date."' AND $where AND meta_key = 'views' AND post_password = '' ORDER  BY views DESC LIMIT $limit");
	if($most_viewed) {
		echo "<ul>";
		foreach ($most_viewed as $post) {
				$post_title = htmlspecialchars(stripslashes($post->post_title));
				$post_views = intval($post->views);
				echo "<li><a href=\"".get_permalink()."\">$post_title</a> ($post_views ".__('Views').")</li>";
		}
		echo "</ul>";
	} else {
		_e('N/A');
	}
}
?>