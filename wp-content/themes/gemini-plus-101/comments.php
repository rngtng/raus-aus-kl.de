Ähnliche Artikel:<br>
<ul><?php related_posts(); ?></ul><br>

<?php if ( !empty($post->post_password) && $_COOKIE['wp-postpass_' . COOKIEHASH] != $post->post_password) : ?>
<p><?php _e('Enter your password to view comments.'); ?></p>
<?php return; endif; ?>
<a name="respond">
<h2 id="comments"><?php comments_number(__('No Comments'), __('1 Comment'), __('% Comments')); ?>
<?php if ( comments_open() ) : ?>
	<a href="#postcomment" title="<?php _e("Leave a comment"); ?>">&raquo;</a>
<?php endif; ?>
</h2>

<?php if ( $comments ) : ?>
<?php $relax_comment_count=1; ?>

<?php foreach ($comments as $comment) : ?>
	<div class="commentbox">
	<a name="comment-<?php comment_ID() ?>"><!--<?php comment_ID(); ?>--></a>
	<div class="commentid">

  		<?php echo $relax_comment_count; ?></div>
  		<?php if ( function_exists('gravatar')) : ?>
		<a href="http://www.gravatar.com"><img src="<?php gravatar("R", 40, get_bloginfo('stylesheet_directory')."/images/default_gravatar.png"); ?>" alt="Get your own gravatar for comments by visiting gravatar.com" class="gravatar" /></a>
		<?php endif; ?>
		<p class="commentby"><?php comment_type(__('Comment'), __('Trackback'), __('Pingback')); ?> <?php _e('by'); ?> <?php comment_author_link() ?></p>
		<p class="commentinfo"><?php comment_date() ?> @ <?php comment_time() ?> <?php edit_comment_link(__("Edit This"), ' |'); ?></p>
	<?php comment_text() ?>
	</div>
<?php $relax_comment_count++; ?>
<?php endforeach; ?>

<?php else : // If there are no comments yet ?>
	<p><?php _e('No comments yet.'); ?></p>
<?php endif; ?>

<p class="post"><?php comments_rss_link(__('<abbr title="Really Simple Syndication">RSS</abbr> feed for comments on this post.')); ?>
<?php if ( pings_open() ) : ?>
	<a href="<?php trackback_url() ?>" rel="trackback"><?php _e('TrackBack <abbr title="Uniform Resource Identifier">URI</abbr>'); ?></a>
<?php endif; ?>
</p>

<?php if ( comments_open() ) : ?>
<h2 id="postcomment"><?php _e('Leave a comment'); ?></h2>

<?php if ( get_option('comment_registration') && !$user_ID ) : ?>
<p>You must be <a href="<?php echo get_option('siteurl'); ?>/wp-login.php?redirect_to=<?php the_permalink(); ?>">logged in</a> to post a comment.</p>
<?php else : ?>

<form action="<?php echo get_option('siteurl'); ?>/wp-comments-post.php" method="post" id="commentform">

<?php if ( $user_ID ) : ?>

<p>Logged in as <a href="<?php echo get_option('siteurl'); ?>/wp-admin/profile.php"><?php echo $user_identity; ?></a>. <a href="<?php echo get_option('siteurl'); ?>/wp-login.php?action=logout" title="<?php _e('Log out of this account') ?>">Logout &raquo;</a></p>

<?php else : ?>

<p><input type="text" name="author" id="author" value="<?php echo $comment_author; ?>" size="22" tabindex="1" />
<label for="author"><small>Name <?php if ($req) _e('(required)'); ?></small></label></p>

<p><input type="text" name="email" id="email" value="<?php echo $comment_author_email; ?>" size="22" tabindex="2" />
<label for="email"><small>Mail (will not be published) <?php if ($req) _e('(required)'); ?></small></label></p>

<p><input type="text" name="url" id="url" value="<?php echo $comment_author_url; ?>" size="22" tabindex="3" />
<label for="url"><small>Website</small></label></p>

<?php endif; ?>

<!--<p><small><strong>XHTML:</strong> You can use these tags: <?php echo allowed_tags(); ?></small></p>-->
<script type="text/javascript">edToolbar();</script>
   <textarea name="comment" id="comment" cols="100%" rows="7" tabindex="4"></textarea>
   <script type="text/javascript">var edCanvas = document.getElementById('comment');</script>
<!--<p><textarea name="comment" id="comment" cols="100%" rows="10" tabindex="4"></textarea></p>-->
<?php if ( function_exists('show_gravatar_signup')):
show_gravatar_signup();
endif; ?>
<p><input name="submit" type="submit" id="submit" tabindex="5" value="Submit Comment" />
<input type="hidden" name="comment_post_ID" value="<?php echo $id; ?>" />
</p>
<?php do_action('comment_form', $post->ID); ?>

</form>


<?php endif; // If registration required and not logged in ?>

<?php else : // Comments are closed ?>
<p><?php _e('Sorry, the comment form is closed at this time.'); ?></p>
<?php endif; ?>
<br>
<script type="text/javascript"><!--
google_ad_client = "pub-4436599026785863";
google_ad_width = 468;
google_ad_height = 15;
google_ad_format = "468x15_0ads_al_s";
//2006-12-17: Raus-Aus-KL-Posting, kaiserslautern
google_ad_channel = "1077175854+9849826780";
google_color_border = "000000";
google_color_bg = "000000";
google_color_link = "FFC62f";
google_color_text = "CCCCCC";
google_color_url = "999999";
//--></script>
<script type="text/javascript"
  src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>