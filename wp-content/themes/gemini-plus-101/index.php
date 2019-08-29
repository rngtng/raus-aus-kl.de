<?php
// test to see if gravatar plugin is already active - if so do nothing
 if ( function_exists('gravatar')) :
else:
// if not use gravatar plugin installed with theme
include_once('gravatar.php');
endif;
get_header();
?>

<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

<div class="post">
<h3 class="storytitle" id="post-<?php the_ID(); ?>"><a href="<?php the_permalink() ?>" rel="bookmark"><?php the_title(); ?></a><span class="feedback"></span></h3>
    <div class="meta"></div>
	<div class="storycontent"><?php the_content(__('(Read on ...)')); ?></div>

<script type="text/javascript"><!--
google_ad_client = "pub-4436599026785863";
google_ad_width = 468;
google_ad_height = 15;
google_ad_format = "468x15_0ads_al";
//2007-06-13: wagnerverlag
google_ad_channel = "0095988743";
google_color_border = "111111";
google_color_bg = "000000";
google_color_link = "FFC62F";
google_color_text = "EEEEEE";
google_color_url = "FFC62F";
//-->
</script>
<script type="text/javascript"
  src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>

    <div class="feedback"><?php wp_link_pages(); ?> </div> 
	 <div style="font-size:8pt;color:#666666;">Von <?php the_author() ?> am <?php the_time('j.n.y') ?> in: <?php the_category(',') ?> &#8212; Tags: <?php the_post_keytags(); ?> &#8212; <?php comments_popup_link(' Kommentar abgeben &#187;', '1 Kommentar &#187;', '% Kommentare &#187;'); ?> <?php edit_post_link(__('Edit This')); ?>
     </div>
    <!-- 	<?php trackback_rdf(); ?> 	-->
</div>

   <?php  //if( $cnt < 1):         <a href="http://www.asta.uni-kl.de/sommerfest.html" target="page" ><img src="http://www.asta.uni-kl.de/uploads/pics/WebBannerSoFe2007.gif" width="468" height="60" border="0" align="top" alt="" title="" /></a> <br><br> <?php $cnt = 1; endif; ?>

<?php comments_template(); ?>

<?php endwhile; else: ?>
<p><?php _e('Nichts gefunden'); ?></p>
<?php endif; ?>

<?php posts_nav_link(' &#8212; ', __('&laquo; Letzte Seite'), __('N&auml;chste Seite &raquo;')); ?>

<?php get_footer(); ?>
