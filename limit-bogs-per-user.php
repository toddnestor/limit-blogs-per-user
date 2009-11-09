<?php
/*
Plugin Name:Limit Blogs per User
Plugin Author:Brajesh K. Singh
Plugin URI:http://www.thinkinginwordpress.com/2009/03/limit-number-of-blogs-per-user-for-wordpress-mu-and-buddypress-websiteblog-network/
Author URI:http://ThinkingInWordpress.com
Version:1.1
*/

add_filter("wpmu_active_signup","tiw_check_current_users_blog"); //send fake/true enable or disabled request
add_action("wpmu_options","tiw_display_options_form"); //show the form to allow how many number of blogs per user
add_action("update_wpmu_options","tiw_save_num_allowed_blogs");//action to save number of allowed blogs per user

/****Check ,whether blog registration is allowed,and how many blogs per logged in user is allowed */

function tiw_check_current_users_blog($active_signup)
{
	if( !is_user_logged_in()||is_site_admin() )
	return $active_signup;//if the user is not logged in or, is site admin,do not change the site policies

	
	//now let us check for the subscribes for root blog/othe blog,if they create new blog ,they should be removed from the main blog and assigned as admin to the new blog
	
	
	global $current_user;
	$number_of_blogs_per_user=tiw_num_allowed_blogs();//find 
	if(!$number_of_blogs_per_user)
	return $active_signup;//do not change policy the plugin is not used as limit can be set zero directly from wpmu option disallowing the blog creation
	//find all blogs for the current user
	$blogs=get_blogs_of_user($current_user->ID);//get all blogs of user
	$count=count($blogs);//let us count it
	
foreach($blogs as $key => $blog){
	$cap_key = $wpdb->base_prefix . $blog->userblog_id . '_capabilities';

if (  (is_array($current_user->$cap_key) && in_array('subscriber', $current_user->$cap_key)) )
$count--;//decrement the count if the user is a subscriber of any blog
}
	
	//if number of allowed blog is greater than 0 and current user owns less number of blogs */
	if($number_of_blogs_per_user>0&&$count<$number_of_blogs_per_user)
			return $active_signup;
	else
	return "none";
}

/****How many blogs are allowed per user *************/

function tiw_num_allowed_blogs()
{
$num_allowed_blog=get_site_option("tiw_allowed_blogs_per_user");//find how many blogs are allowed
if(!isset($num_allowed_blog))
$num_allowed_blog=0;

return $num_allowed_blog;//return the number of allowed blogs
}

/*****Show the Number of Blogs to restrict per user at the bottom of Site options ****/
function tiw_display_options_form()
{
?>
	<h3><?php _e('Limit Blog Registrations Per User') ?></h3>
	<table>
	<tbody>
		<tr valign="top"> 
				<th scope="row">Number of blogs allowed per User</th> 
				<td>
					<input type="text" name="num_allowed_blogs" value="<?php echo tiw_num_allowed_blogs()?>" />
					<p>If the Value is Zero,It indicates any number of blog is allowed</p>
				</td>
		</tr>
	</tbody>
	</table>
<?php
}

/**************Save the Number of blogs per user when the form is updated **************/
function tiw_save_num_allowed_blogs()
{
$allowed_number_of_blogs=intval($_POST["num_allowed_blogs"]);//how many blogs the user has set
//save to the database
update_site_option("tiw_allowed_blogs_per_user",$allowed_number_of_blogs);//now update

}
?>