-> Installation Instructions
--------------------------------------------------
// Open wp-content/plugins folder

Put:
------------------------------------------------------------------
postviews.php
------------------------------------------------------------------


// Activate the WP-PostViews plugin





-> Usage Instructions
--------------------------------------------------
// Open wp-content/themes/<YOUR THEME NAME>/index.php OR single.php OR page.php

Find:
------------------------------------------------------------------
<?php while (have_posts()) : the_post(); ?>
------------------------------------------------------------------
Add Anywhere Below It:
------------------------------------------------------------------
<?php if(function_exists('the_views')) { the_views(); } ?>
------------------------------------------------------------------
Note:
------------------------------------------------------------------
The first value you pass in is the text for views.
Default: the_views('Views');
------------------------------------------------------------------


// Post Views Stats (You can place it anywhere outside the WP Loop)

// To Get Most Viewed Post

Use:
------------------------------------------------------------------
<?php if (function_exists('get_most_viewed')): ?>
	<?php get_most_viewed(); ?>
<?php endif; ?>
------------------------------------------------------------------
Note:
------------------------------------------------------------------
The first value you pass in is what you want to get, 'post', 'page' or 'both'.
The second value you pass in is the number of post you want to get.
Default: get_most_viewed('both', 10);
------------------------------------------------------------------