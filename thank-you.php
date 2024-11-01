<?php
/*
Plugin Name: Thank You
Version: 1.0.1
Plugin URI: http://xn--wicek-k0a.pl/projekty/thank-you
Description: Redirect commenters who just made their first comment to a page of your choice.
Author: Łukasz Więcek
Author URI: http://mydiy.pl/
*/

if(!class_exists('ThankYou_Admin'))
	{
	class ThankYou_Admin
		{
		function add_config_page()
			{
			add_options_page('Thank You Configuration', 'Thank You', 7, __FILE__,array('ThankYou_Admin','config_page'));
			}

		function config_page()
			{
			if(isset($_POST['submit']))
				{
				if(!current_user_can('manage_options')) die(__('You cannot edit the Thank You options.'));
				check_admin_referer('thank-you-updatesettings');

				$options['ThankYou'] = $_POST['page_id'];
				$options_data['ThankYou-data'] = $_POST['data_ty'];

				update_option('ThankYou', $_POST['page_id']);
				update_option('ThankYou-data', $_POST['data_ty']);
				}

			$options  = get_option('ThankYou');
			$options_data  = get_option('ThankYou-data');
			
			?>
			
			<div class="wrap">
				<h2>Thank You Configuration</h2>
				<form action="" method="post" id="thankyou-conf">
					<table class="form-table">
						<?php if (function_exists('wp_nonce_field')) { wp_nonce_field('thank-you-updatesettings'); } ?>
						<tr>
							<th scope="row" valign="top">Your thanks are in:</th>
							<td><?php wp_dropdown_pages("depth=0&selected=".$options); ?></td>
						</tr>
						
						<tr>
							<th scope="row" valign="top">Verify new users by:</th>
							<td><select name="data_ty" id="data_ty"> 
									<option class="level-0" value="1"<?php if($options_data=='1') echo ' selected="selected"'; ?>>E-mail</option> 
									<option class="level-0" value="2"<?php if($options_data=='2') echo ' selected="selected"'; ?>>Nickname</option> 
								</select> 
							</td> 
						</tr>			
					</table>
					<br/>
					<span class="submit" style="border: 0;"><input type="submit" name="submit" value="Save Settings" /></span>
				</form>
				
				<?php
				if($options  = get_option('ThankYou'))
					{
					echo "<p style='margin-top: 35px;'>Selected page ID: <strong>".$options."</strong></p>";
					}
				?>
				
				<p style="margin-top: 35px;">In the content of the "thank you" message you can use the following tags:</p>
				<ul style="margin-left: 35px; list-style-type: disc;">
					<li><strong>%comment_url%</strong> - direct URL of the added comment</li>
					<li><strong>%post_url%</strong> - direct URL of the commented post</li>
				</ul>

				<p style="margin-top: 35px;">The author of the plugin is <a href="http://majsterkowo.pl/" title="DIY - zrób to sam. Blog dla majsterkowiczów">Łukasz Więcek</a>. <strong>Thank You</strong> plug was written on the basis of plug <strong>Comment Redirect</strong>.</p>
			</div>
			<?php }
		}
	}

function change_redirect($url, $comment)
	{
	global $wpdb;
	$options_data  = get_option('ThankYou-data');
	
	if($options_data==="1")		$cc = $wpdb->get_var("SELECT COUNT(comment_author_email) FROM $wpdb->comments WHERE comment_author_email = '".$comment->comment_author_email."'");
	if($options_data==="2")		$cc = $wpdb->get_var("SELECT COUNT(comment_author) FROM $wpdb->comments WHERE comment_author = '".$comment->comment_author."'");
	
	if($cc == 1)
		{
		$options  = get_option('ThankYou');
		return get_permalink($options).$rty_parameter_syntax.'?cid='.$comment->comment_ID;
		}
	else
		{
		return $url;
		}
	}

function replace_redirect($content)
	{
	$options  = get_option('ThankYou');
	if(is_page($options))
		{
		$comment_url = get_comment_link($_GET['cid']);
		$post_url = explode('#', $comment_url);

		$content = str_replace('%comment_url%', $comment_url, $content);
		$content = str_replace('%post_url%', $post_url[0], $content);
		
		$content = $content."<p style='font-size: 10px; color: #666; margin-top: 10px;'>Strona wygenerowana dzięki wtyczce <a href='http://xn--wicek-k0a.pl/projekty/thank-you'>Thank You</a> autorstwa <a href='http://mydiy.pl/'>Łukasza Więcka</a>.</p>";
		}
	return $content;
	}

add_filter('comment_post_redirect','change_redirect',10,2);
add_filter('the_content', 'replace_redirect');
add_action('admin_menu', array('ThankYou_Admin','add_config_page'));

?>