<div id="menu">
	<div id="nav">
	<ul>
	<li><a href="http://www.kultur-kl.de/" style="border:0;padding:0;margin:0;background:none;" target="_blank"><img style="border:1;padding:0;margin:0;background:none;" src="/wp-content/themes/gemini-plus-101/banner.jpg"></a></li>

    <li><h2>Terminkalender</h2>
       <?php ec3_get_events( '5days'); ?>
    </li>

<li><a href="http://www.raus-aus-kl.de/feed/" title="Abo" rel="alternate" type="application/rss+xml"><img src="http://www.feedburner.com/fb/images/pub/feed-icon16x16.png" alt="" style="border:0" align="absmiddle"/>Termine abonnieren</a>
</li>


    <li><h2>Allgemeine Info</h2>
		<ul class="navigation">
        <li class="page_item"><a href="http://www.raus-aus-kl.de/about/" title="Warum dies alles?">Warum dies alles?</a></li>
<li class="page_item"><a href="http://www.raus-aus-kl.de/die-stadt-im-netz/" title="Die Stadt im Netz">Die Stadt im Netz</a></li>
<li class="page_item"><a href="http://www.raus-aus-kl.de/bands-in-kaiserslautern/" title="Musikbands in Kaiserslautern">Bands in Kaiserslautern</a></li>
<li class="page_item"><a href="http://www.raus-aus-kl.de/about/" title="Warum dies alles?">Impressum</a></li> 
			<?php //wp_list_pages('sort_column=menu_order&title_li='); ?>
		</ul>
    </li>

<?php if (class_exists('ajaxNewsletter')): ?>
<li><h2>Newsletter</h2>
   <div style="padding:5px 3px;">
      <?php ajaxNewsletter::newsletterForm(); ?>
   </div>
</li>
<?php endif; ?>

<li><h2>Suche</h2><form id="searchform" method="get" action="<?php bloginfo('home'); ?>">
			<div>
			<input type="search" name="s" id="s" size="12" placeholder="Suche" value=""  autosave="RAUS-AUS-KL" results="5"/>
			<!--<input type="submit" value="<?php _e('Search'); ?>" /> --> 
			</div>
			</form></li>

   <?php if (function_exists('get_recent_comments')) { ?>
   <li><h2><?php _e('Kommentare:'); ?></h2>
        <ul> <?php get_recent_comments(); ?></ul>
   </li>

<li id="qype">
<script type="text/javascript">
var qypetoolConfig = { 
   reviewCount: 10,
   headline: 'Kaiserslautern',
   showStars: false
}
</script><script src="http://www.qype.com/qypetool/city_widget/deb32.de.js" type="text/javascript"></script>
<p><b>Ich bin <a href="http://www.qype.com/people/RausAusKL?et_cid=7&amp;et_lid=158667">RausAusKL</a> auf <a href="http://www.qype.com/world?et_cid=7&amp;et_lid=158667">Qype</a></b></p>
</li>

   <?php } ?>   

		<li><ul class="links">
			<?php get_links_list('id'); ?>
		</ul></li>

       <li>&nbsp;<br>&nbsp;<br></li>

		<li><h2>Postings</h2>
		<ul class="categories">
			<?php list_cats(); ?>
		</ul></li>

		<li><h2>Archiv</h2>
		<ul class="archives">
			<?php get_archives(); ?>
		</ul></li>
	
		<li><h2>Meta</h2>  

<?php if ( !$user_ID ) : ?>

<script src="http://feeds.feedburner.com/~s/Raus-Aus-Kaiserslautern?i=<?php the_permalink() ?>" type="text/javascript" charset="utf-8"></script>

<?php endif; ?>

</li>
</ul>
	</div> <!--end #nav -->

</div> <!-- end sidebar -->

