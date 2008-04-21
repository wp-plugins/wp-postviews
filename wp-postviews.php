<?php
/*
Plugin Name: WP-PostViews
Plugin URI: http://lesterchan.net/portfolio/programming.php
Description: Enables you to display how many times a post/page had been viewed.
Version: 1.30
Author: Lester 'GaMerZ' Chan
Author URI: http://lesterchan.net
*/


/*  
	Copyright 2008  Lester Chan  (email : lesterchan@gmail.com)

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


### Create Text Domain For Translations
add_action('init', 'postviews_textdomain');
function postviews_textdomain() {
	load_plugin_textdomain('wp-postviews', 'wp-content/plugins/wp-postviews');
}


### Function: Post Views Option Menu
add_action('admin_menu', 'postviews_menu');
function postviews_menu() {
	if (function_exists('add_options_page')) {
		add_options_page(__('Post Views', 'wp-postviews'), __('Post Views', 'wp-postviews'), 'manage_options', 'wp-postviews/postviews-options.php') ;
	}
}


### Function: Calculate Post Views
add_action('loop_start', 'process_postviews');
function process_postviews() {
	global $id, $user_ID, $post_meta_cache;
	unset($post_meta_cache[$id]);
	$views_options = get_option('views_options');
	$post_views = get_post_custom($post_id);
	$post_views = intval($post_views['views'][0]);
	$should_count = false;
	switch(intval($views_options['count'])) {
		case 0:
			$should_count = true;
			break;
		case 1:
			if(empty($_COOKIE[USER_COOKIE]) && intval($user_ID) == 0) {
				$should_count = true;
			}
			break;
		case 2:
			if(intval($user_ID) > 0) {
				$should_count = true;
			}
			break;
	}
	if($should_count) {
		if(is_single() || is_page()) {
			if(!update_post_meta($id, 'views', ($post_views+1))) {
				add_post_meta($id, 'views', 1, true);
			}
			remove_action('loop_start', 'process_postviews');
		}	
	}
}


### Function: Display The Post Views
function the_views($display = true) {
	$post_views = intval(post_custom('views'));
	$views_options = get_option('views_options');
	$output = str_replace('%VIEW_COUNT%', number_format_i18n($post_views), $views_options['template']);
	if($display) {
		echo $output;
	} else {
		return $output;
	}
}


### Function: Display Most Viewed Page/Post
if(!function_exists('get_most_viewed')) {
	function get_most_viewed($mode = '', $limit = 10, $chars = 0, $display = true) {
		global $wpdb, $post;
		$where = '';
		$temp = '';
		if(!empty($mode) && $mode != 'both') {
			$where = "post_type = '$mode'";
		} else {
			$where = '1=1';
		}
		$most_viewed = $wpdb->get_results("SELECT DISTINCT $wpdb->posts.*, (meta_value+0) AS views FROM $wpdb->posts LEFT JOIN $wpdb->postmeta ON $wpdb->postmeta.post_id = $wpdb->posts.ID WHERE post_date < '".current_time('mysql')."' AND $where AND post_status = 'publish' AND meta_key = 'views' AND post_password = '' ORDER  BY views DESC LIMIT $limit");
		if($most_viewed) {
			if($chars > 0) {
				foreach ($most_viewed as $post) {
					$post_title = get_the_title();
					$post_views = intval($post->views);
					$post_views = number_format_i18n($post_views);
					$temp .= "<li><a href=\"".get_permalink()."\">".snippet_text($post_title, $chars)."</a> - $post_views ".__('views', 'wp-postviews')."</li>\n";
				}
			} else {
				foreach ($most_viewed as $post) {
					$post_title = get_the_title();
					$post_views = intval($post->views);
					$post_views = number_format_i18n($post_views);
					$temp .= "<li><a href=\"".get_permalink()."\">$post_title</a> - $post_views ".__('views', 'wp-postviews')."</li>\n";
				}
			}
		} else {
			$temp = '<li>'.__('N/A', 'wp-postviews').'</li>'."\n";
		}
		if($display) {
			echo $temp;
		} else {
			return $temp;
		}
	}
}


### Function: Display Most Viewed Page/Post By Category ID
if(!function_exists('get_most_viewed_category')) {
	function get_most_viewed_category($category_id = 0, $mode = '', $limit = 10, $chars = 0, $display = true) {
		global $wpdb, $post;
		$where = '';
		$temp = '';
		if(is_array($category_id)) {
			$category_sql = "$wpdb->term_taxonomy.term_id IN (".join(',', $category_id).')';
		} else {
			$category_sql = "$wpdb->term_taxonomy.term_id = $category_id";
		}
		if(!empty($mode) && $mode != 'both') {
			$where = "post_type = '$mode'";
		} else {
			$where = '1=1';
		}
		$most_viewed = $wpdb->get_results("SELECT DISTINCT $wpdb->posts.*, (meta_value+0) AS views FROM $wpdb->posts LEFT JOIN $wpdb->postmeta ON $wpdb->postmeta.post_id = $wpdb->posts.ID INNER JOIN $wpdb->term_relationships ON ($wpdb->posts.ID = $wpdb->term_relationships.object_id) INNER JOIN $wpdb->term_taxonomy ON ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id) WHERE post_date < '".current_time('mysql')."' AND $wpdb->term_taxonomy.taxonomy = 'category' AND $category_sql AND $where AND post_status = 'publish' AND meta_key = 'views' AND post_password = '' ORDER  BY views DESC LIMIT $limit");
		if($most_viewed) {
			if($chars > 0) {
				foreach ($most_viewed as $post) {
					$post_title = htmlspecialchars(stripslashes($post->post_title));
					$post_views = intval($post->views);
					$post_views = number_format_i18n($post_views);
					$temp .= "<li><a href=\"".get_permalink()."\">".snippet_text($post_title, $chars)."</a> - $post_views ".__('views', 'wp-postviews')."</li>\n";
				}
			} else {
				foreach ($most_viewed as $post) {
					$post_title = htmlspecialchars(stripslashes($post->post_title));
					$post_views = intval($post->views);
					$post_views = number_format_i18n($post_views);
					$temp .= "<li><a href=\"".get_permalink()."\">$post_title</a> - $post_views ".__('views', 'wp-postviews')."</li>\n";
				}
			}
		} else {
			$temp = '<li>'.__('N/A', 'wp-postviews').'</li>'."\n";
		}
		if($display) {
			echo $temp;
		} else {
			return $temp;
		}
	}
}


### Function: Get TimeSpan Most Viewed - Added by Paolo Tagliaferri (http://www.vortexmind.net - webmaster@vortexmind.net)
function get_timespan_most_viewed($mode = '', $limit = 10, $days = 7, $display = true) {
	global $wpdb, $post;	
	$limit_date = current_time('timestamp') - ($days*86400); 
	$limit_date = date("Y-m-d H:i:s",$limit_date);	
	$where = '';
	$temp = '';
	if(!empty($mode) && $mode != 'both') {
		$where = "post_type = '$mode'";
	} else {
		$where = '1=1';
	}
	$most_viewed = $wpdb->get_results("SELECT DISTINCT $wpdb->posts.*, (meta_value+0) AS views FROM $wpdb->posts LEFT JOIN $wpdb->postmeta ON $wpdb->postmeta.post_id = $wpdb->posts.ID WHERE post_date < '".current_time('mysql')."' AND post_date > '".$limit_date."' AND $where AND post_status = 'publish' AND meta_key = 'views' AND post_password = '' ORDER  BY views DESC LIMIT $limit");
	if($most_viewed) {
		foreach ($most_viewed as $post) {
			$post_title = get_the_title();
			$post_views = intval($post->views);
			$post_views = number_format_i18n($post_views);
			$temp .= "<li><a href=\"".get_permalink()."\">$post_title</a> - $post_views ".__('views', 'wp-postviews')."</li>";
		}
	} else {
		$temp = '<li>'.__('N/A', 'wp-postviews').'</li>'."\n";
	}
	if($display) {
		echo $temp;
	} else {
		return $temp;
	}
}


### Function: Get TimeSpan Most Viewed By Category
function get_timespan_most_viewed_cat($category_id = 0, $mode = '', $limit = 10, $days = 7, $display = true) {
	global $wpdb, $post;	
	$limit_date = current_time('timestamp') - ($days*86400); 
	$limit_date = date("Y-m-d H:i:s",$limit_date);	
	$where = '';
	$temp = '';
	if(is_array($category_id)) {
		$category_sql = "$wpdb->term_taxonomy.term_id IN (".join(',', $category_id).')';
	} else {
		$category_sql = "$wpdb->term_taxonomy.term_id = $category_id";
	}
	if(!empty($mode) && $mode != 'both') {
		$where = "post_type = '$mode'";
	} else {
		$where = '1=1';
	}
	$most_viewed = $wpdb->get_results("SELECT DISTINCT $wpdb->posts.*, (meta_value+0) AS views FROM $wpdb->posts LEFT JOIN $wpdb->postmeta ON $wpdb->postmeta.post_id = $wpdb->posts.ID INNER JOIN $wpdb->term_relationships ON ($wpdb->posts.ID = $wpdb->term_relationships.object_id) INNER JOIN $wpdb->term_taxonomy ON ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id) WHERE post_date < '".current_time('mysql')."' AND $wpdb->term_taxonomy.taxonomy = 'category' AND $category_sql AND post_date > '".$limit_date."' AND $where AND post_status = 'publish' AND meta_key = 'views' AND post_password = '' ORDER  BY views DESC LIMIT $limit");
	if($most_viewed) {
		foreach ($most_viewed as $post) {
			$post_title = get_the_title();
			$post_views = intval($post->views);
			$post_views = number_format_i18n($post_views);
			$temp .= "<li><a href=\"".get_permalink()."\">$post_title</a> - $post_views ".__('views', 'wp-postviews')."</li>";
		}
	} else {
		$temp = '<li>'.__('N/A', 'wp-postviews').'</li>'."\n";
	}
	if($display) {
		echo $temp;
	} else {
		return $temp;
	}
}


### Function: Display Total Views
if(!function_exists('get_totalviews')) {
	function get_totalviews($display = true) {
		global $wpdb;
		$total_views = $wpdb->get_var("SELECT SUM(meta_value+0) FROM $wpdb->postmeta WHERE meta_key = 'views'");
		if($display) {
			echo number_format_i18n($total_views);
		} else {
			return number_format_i18n($total_views);
		}
	}
}


### Function: Snippet Text
if(!function_exists('snippet_text')) {
	function snippet_text($text, $length = 0) {
		$text = html_entity_decode($text, ENT_QUOTES, get_option('blog_charset'));
		 if (strlen($text) > $length) {
			return htmlentities(substr($text,0,$length), ENT_COMPAT, get_option('blog_charset')).'...';
		 } else {
			return htmlentities($text, ENT_COMPAT, get_option('blog_charset'));
		 }
	}
}


### Function: Modify Default WordPress Listing To Make It Sorted By Post Views
function views_fields($content) {
	global $wpdb;
	$content .= ", ($wpdb->postmeta.meta_value+0) AS views";
	return $content;
}
function views_join($content) {
	global $wpdb;
	$content .= " LEFT JOIN $wpdb->postmeta ON $wpdb->postmeta.post_id = $wpdb->posts.ID";
	return $content;
}
function views_where($content) {
	global $wpdb;
	$content .= " AND $wpdb->postmeta.meta_key = 'views'";
	return $content;
}
function views_orderby($content) {
	$orderby = trim(addslashes($_GET['orderby']));
	if(empty($orderby) || ($orderby != 'asc' && $orderby != 'desc')) {
		$orderby = 'desc';
	}
	$content = " views $orderby";
	return $content;
}


### Process The Sorting
/*
if($_GET['sortby'] == 'views') {
	add_filter('posts_fields', 'views_fields');
	add_filter('posts_join', 'views_join');
	add_filter('posts_where', 'views_where');
	add_filter('posts_orderby', 'views_orderby');
}
*/


### Function: Plug Into WP-Stats
if(strpos(get_option('stats_url'), $_SERVER['REQUEST_URI']) || strpos($_SERVER['REQUEST_URI'], 'stats-options.php') || strpos($_SERVER['REQUEST_URI'], 'wp-stats/wp-stats.php')) {
	add_filter('wp_stats_page_admin_plugins', 'postviews_page_admin_general_stats');
	add_filter('wp_stats_page_admin_most', 'postviews_page_admin_most_stats');
	add_filter('wp_stats_page_plugins', 'postviews_page_general_stats');
	add_filter('wp_stats_page_most', 'postviews_page_most_stats');
}


### Function: Add WP-PostViews General Stats To WP-Stats Page Options
function postviews_page_admin_general_stats($content) {
	$stats_display = get_option('stats_display');
	if($stats_display['views'] == 1) {
		$content .= '<input type="checkbox" name="stats_display[]" id="wpstats_views" value="views" checked="checked" />&nbsp;&nbsp;<label for="wpstats_views">'.__('WP-PostViews', 'wp-postviews').'</label><br />'."\n";
	} else {
		$content .= '<input type="checkbox" name="stats_display[]" id="wpstats_views" value="views" />&nbsp;&nbsp;<label for="wpstats_views">'.__('WP-PostViews', 'wp-postviews').'</label><br />'."\n";
	}
	return $content;
}


### Function: Add WP-PostViews Top Most/Highest Stats To WP-Stats Page Options
function postviews_page_admin_most_stats($content) {
	$stats_display = get_option('stats_display');
	$stats_mostlimit = intval(get_option('stats_mostlimit'));
	if($stats_display['viewed_most'] == 1) {
		$content .= '<input type="checkbox" name="stats_display[]" id="wpstats_viewed_most" value="viewed_most" checked="checked" />&nbsp;&nbsp;<label for="wpstats_viewed_most">'.$stats_mostlimit.' '.__('Most Viewed Posts', 'wp-postviews').'</label><br />'."\n";
	} else {
		$content .= '<input type="checkbox" name="stats_display[]" id="wpstats_viewed_most" value="viewed_most" />&nbsp;&nbsp;<label for="wpstats_viewed_most">'.$stats_mostlimit.' '.__('Most Viewed Posts', 'wp-postviews').'</label><br />'."\n";
	}
	return $content;
}


### Function: Add WP-PostViews General Stats To WP-Stats Page
function postviews_page_general_stats($content) {
	$stats_display = get_option('stats_display');
	if($stats_display['views'] == 1) {
		$content .= '<p><strong>'.__('WP-PostViews', 'wp-postviews').'</strong></p>'."\n";
		$content .= '<ul>'."\n";
		$content .= '<li><strong>'.get_totalviews(false).'</strong> '.__('views were generated.', 'wp-postviews').'</li>'."\n";
		$content .= '</ul>'."\n";
	}
	return $content;
}


### Function: Add WP-PostViews Top Most/Highest Stats To WP-Stats Page
function postviews_page_most_stats($content) {
	$stats_display = get_option('stats_display');
	$stats_mostlimit = intval(get_option('stats_mostlimit'));
	if($stats_display['viewed_most'] == 1) {
		$content .= '<p><strong>'.$stats_mostlimit.' '.__('Most Viewed Post', 'wp-postviews').'</strong></p>'."\n";
		$content .= '<ul>'."\n";
		$content .= get_most_viewed('post', $stats_mostlimit, 0, false);
		$content .= '</ul>'."\n";
	}
	return $content;
}


### Function: Post Views Options
add_action('activate_wp-postviews/wp-postviews.php', 'views_init');
function views_init() {
	// Add Options
	$views_options = array();
	$views_options['count'] = 1;
	$views_options['template'] = __('%VIEW_COUNT% views', 'wp-postviews');
	add_option('views_options', $views_options, 'Post Views Options');
}
?>