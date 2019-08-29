<?php get_header(); ?>

	<div id="static">

    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
		<div class="post">
		<h3 id="post-<?php the_ID(); ?>"><?php the_title(); ?></h3>
			<div class="entrytext">
				<?php the_content('<p class="serif">Read the rest of this page &raquo;</p>'); ?>
	
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

				<?php link_pages('<p><strong>Pages:</strong> ', '</p>', 'number'); ?>
	
			</div>
		</div>
	  <?php endwhile; endif; ?>
	
	</div>

<?php get_footer(); ?>
