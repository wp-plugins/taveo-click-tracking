<?php 
//Security Enhancement
defined( 'ABSPATH' ) or die( 'Go Away.' );


add_action( 'admin_post_taveo_options_save', 'process_taveo_options' );

function process_taveo_options(){

	$taveo_key = NULL;
	$taveo_wp_shorten = NULL;
	$taveo_wp_shorten_path = NULL;
	$taveo_error = NULL;
	if ( !current_user_can( 'manage_options' ) )
   {
      wp_die( 'You are not allowed to be on this page.' );
   }
   check_admin_referer( 'taveo_verify','taveo_dash_nonce' );
   

   if ( isset( $_POST['taveo_api_key'] ) )
   {
      $taveo_key = sanitize_text_field( $_POST['taveo_api_key'] );

   }
   if ( isset( $_POST['taveo_wp_shorten'] ) )
   {
      $taveo_wp_shorten = sanitize_text_field( $_POST['taveo_wp_shorten'] );
	  if ($taveo_wp_shorten !== "1" && $taveo_wp_shorten !== "0") {
	  	$taveo_error = "Error bad Radio box value!?!?!";
		$taveo_wp_shorten = NULL;
	  }
   }
   if ( isset( $_POST['taveo_wp_shorten_path'] ) )
   {
      $taveo_wp_shorten_path = sanitize_text_field( $_POST['taveo_wp_shorten_path'] );
	  //remove extra whitespaces
	  $taveo_wp_shorten_path = trim($taveo_wp_shorten_path);
	  $taveo_wp_shorten_path = rtrim($taveo_wp_shorten_path);
	  //remove starting and trailing slashes
	  $taveo_wp_shorten_path = trim($taveo_wp_shorten_path,'/');
	  $taveo_wp_shorten_path = rtrim($taveo_wp_shorten_path,'/');
	  if (strlen($taveo_wp_shorten_path) < 2) {
	  	$taveo_error = "Error Bad Path:". urlencode($taveo_wp_shorten_path);
		$taveo_wp_shorten_path = NULL; 
	  }
   }     
   
   if(is_multisite()){
   	  if (isset($taveo_key)) {
      	update_blog_option(null, 'taveo_api_key',$taveo_key );
	  }
   	  if (isset($taveo_wp_shorten)) {
      	update_blog_option(null, 'taveo_wp_shorten',$taveo_wp_shorten );
	  }	  
 	  if (isset($taveo_wp_shorten_path)) {
      	update_blog_option(null, 'taveo_wp_shorten_path',$taveo_wp_shorten_path );
	  }	 
   }
   else {
   	  if (isset($taveo_key)) {
      	update_option('taveo_api_key',$taveo_key);
	  }
   	  if (isset($taveo_wp_shorten)) {
      	update_option('taveo_wp_shorten',$taveo_wp_shorten);
	  }	 
   	  if (isset($taveo_wp_shorten_path)) {
      	update_option('taveo_wp_shorten_path',$taveo_wp_shorten_path);
	  }	   
   }
 
   if($taveo_wp_shorten == 1) {
      flush_rewrite_rules();
   }
   else {
   	error_log("Did not flush rules!?!?! " . $taveo_wp_shorten);
   } 
   wp_redirect(  admin_url( 'admin.php?page=taveo_dashboard&settings-updated=1' ) );
   //wp_redirect( admin_url('admin.php?page='.$_GET["page"]. '&settings-updated=1') );
   exit;

}

//The markup for the plugin settings / dashboard page
function taveo_build_config_screen(){ 
    //get the older values, wont work the first time
	if(is_multisite()){
    	$api_key = get_blog_option(null, 'taveo_api_key');
		$shorten = get_blog_option(null, 'taveo_wp_shorten');
		$shorten_path = get_blog_option(null, 'taveo_wp_shorten_path');
	}
	else {
		$api_key = get_option('taveo_api_key' );
		$shorten = get_option('taveo_wp_shorten');
		$shorten_path = get_option('taveo_wp_shorten_path');		
	}
	if (empty($shorten_path)) {
		$shorten_path = 'x';
	}
	?>
	<div class="dashboard">
    <h2>Taveo: Dashboard</h2>
 

    <?php
	  if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] == '1' )
	  {
	?>
	   <div id='message' class='updated fade'><p><strong>Settings Successfully Saved</strong></p></div>
	<?php
	  }
?>
  <div id="wrapper">	
	  <div class="container block1">
		<p> Enter your Taveo API key below. Your Taveo API key can be found on your "Account" page in the Taveo Admin portal.<br> 
			<a href="https://admin.taveo.net/login?nxt=/account" target="_blank">Click here to view your Taveo Account</a><br>
		</p>
		<?php
		if ( empty($api_key)) {
			echo '<h4>Don\'t have a Taveo account? <a href="https://admin.taveo.net/register">Create one for FREE.</a></h4>';
		}?>
	    <form action="admin-post.php" method="post" >
			<input type="hidden" name="action" value="taveo_options_save" />
			<?php wp_nonce_field('taveo_verify','taveo_dash_nonce'); ?>

		    <table class="form-table">
		        <tr>
		            <th scope="row">API Key :</th>
		            <td>
		                <fieldset>
		                    <label>
		                        <input size="30" placeholder="Please enter your API Key" class="taveotextinput" name="taveo_api_key" type="text" id="taveo_api_key" value="<?php echo (isset($api_key) && $api_key != '') ? $api_key : ''; ?>"/>
		                        
		                    </label>
		                </fieldset>
		            </td>
		        </tr>
				<tr>
					<th scope="row">Use WordPress to shorten URLS?</th>
					<td>
						<fieldset>
							<p><label>
								<input id="tsno" name="taveo_wp_shorten" type="radio" value="0" class="tog" <?php checked( empty( $shorten) || (0 == $shorten) ); ?>">
								No	
						 	</label></p>
							<p><label>
								<input id="tsyes" name="taveo_wp_shorten" type="radio" value="1" class="tog" <?php checked( (1 == $shorten) ); ?>">
								Yes!	
						 	</label></p>
						 	<p class="description">When this is enabled, you can use your Domain ( <?php echo site_url(); ?> ) for tracking and shortening purposes. This is very 
						 		useful for sites which don't allow 3rd party redirects (ex: Pinterest) and to cloak your usage of Taveo. </p> 	
						</fieldset>
					</td>
				</tr>
				<tr>
					<th scope="row"> Shorten Path? </th>
					<td>
						<fieldset>
							<p><label>
						 		<?php echo site_url(); ?>/<input type="text" id="tspath" name="taveo_wp_shorten_path" value="<?php echo $shorten_path ?>" <?php disabled( empty( $shorten) || (0 == $shorten) ); ?> >/
					 		</label></p>
					 		<p class="description">, to We recommend 'x' here, but anything will do. </p>
					 	</fieldset>	
					</td>
				</tr>
		    </table>
		    
		    <input name="submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" /> 
		</form>	   
		<hr> 
		<h2 class="text-center"> Your Taveo Links </h2>
		<table id="taveo_links" class="display text-center" cellspacing="0">
			<thead>
		        <tr>
		            <th>URL</th>
		            <th>Destination</th>
		            <th>Last Click</th>
		            <th></th>
		            <th>Total Clicks</th>
		            <th>Comment</th>
		            <th>&nbsp;</th>
		        </tr>
	    	</thead>
			<tfoot>
		        <tr>
		            <th>URL</th>
		            <th>Destination</th>
		            <th>Last Click</th>
		            <th></th>
		            <th>Total Clicks</th>
		            <th>Comment</th>
		            <th>&nbsp;</th>
		        </tr>
	    	</tfoot>    	
	    	<tbody>
		
	   	<?php
	    	//add taveo link data
			if ( !empty( $api_key )) {
				//make request to Taveo API server and get response
				$response = wp_remote_get( add_query_arg( array(
		    								'apikey' => $api_key), TAVEO_API_LINKS_URL ), array('sslverify' => TAVEO_SSL_VERIFY) );
				$rcode = wp_remote_retrieve_response_code( $response );
				$overview = json_decode( wp_remote_retrieve_body( $response ), true );
				if (!(200 ==  $rcode) ) {
				    //Error, print what happened
				    ?>
				    <tr><td scope="row">Received Error from Taveo Server: <?php echo $overview['msg']; ?></tr></td>
				    <tr style="display:none;">error</tr>
					<?php
				}
				//non error, show data
				else if(($rcode == 200) && ($overview['status']=='ok')) {
					//iterate over returned data
					$arr = $overview['links'];
					foreach($arr as $value) {
						echo '<tr>';
						echo '<td>'. $value['url'] . '</td>';
						echo '<td title="'. $value['dest'] .'">'. $value['dest'] . '</td>';
						echo '<td>'. $value['last_click'][0] . '</td>';
						echo '<td>'. $value['last_click'][1] . '</td>';
						echo '<td>'. $value['total_clicks'] . '</td>';
						echo '<td>'. $value['comment'] . '</td>';
						echo '<td> <a title="View Stats in Taveo" 
   									  href="https://admin.taveo.net/linkstats?lid='. $value['id'] . '"
   									  target="_blank">
   								<span class="dashicons dashicons-chart-bar"></span></a></td>'; 
						echo '</tr>';
					}
				}
			}
	    ?>
	    	</tbody>
		</table>        
	    </div> <!-- End container left -->
	
	    
	    <div class="block2" id="sidebar-container">	    	
			<div id="sidebar">
			<h3 class="text-center">Account Information</h3>
		<table class="taveo_table">
		    <?php
			if ( !empty( $api_key )) {
				//make request to Taveo API server and get response
				$response = wp_remote_get( add_query_arg( array(
		    								'apikey' => $api_key), TAVEO_API_OVERVIEW_URL ), array('sslverify' => TAVEO_SSL_VERIFY) );
				$rcode = wp_remote_retrieve_response_code( $response );
				$overview = json_decode( wp_remote_retrieve_body( $response ), true );
				if ($rcode == 401) {
				    ?>
				    <th scope="row">Bad API Key, Enter Correct key in the box to the right! </th>
					<?php				
				}
				else if ($rcode == 429) {
				    ?>
				    <th scope="row">Too many requests to the server, please wait a while before continuing... </th>
					<?php				
				}
				else if ($rcode == 500) {
				    ?>
				    <th scope="row">Taveo Server Error, try again later </th>
					<?php				
				}				
				else if (!(200 ==  $rcode) ) {
				    //Error, print what happened
				    ?>
				    <th scope="row">Received Error from Taveo Server: <?php echo $overview['msg']; ?></th>
					<?php
				}
				
				if($overview['status']=='ok') {
					?>
		            <tr>
		                <th scope="row">Account :</th>
		                <td>
		                    <?php echo $overview['account']; ?>
		                </td>
		            </tr>
		            <tr>
		                <th scope="row">Clicks today:</th>
		                <td>
		                    <?php echo $overview['clicks_today']; ?>
		                </td>
		            </tr>
		            <tr>
		                <th scope="row">Clicks this month :</th>
		                <td>
		                    <?php echo $overview['clicks_month']; ?>
		                </td>
		            </tr>
		    		<?php 
				}
				else{
					?>
		            <tr>
		            	<th scope="row">There was a problem! Please check your API Key or wait a few minutes.</th>
		                
		            </tr>
		    		<?php 
		    	}   
		    }
		    else {
		    	?> 
		    	<tr>
		    		<th scope="row">Enter your API key to see account information </th>
		    	</tr>
		    	<?php
		    }
		        
		    ?>
	    </table><br><br>				
				<?php
							
				//This is for future use
				$service_banners = array(
					array(
						'url' => 'http://taveo.com/link1',
						'img' => 'banner-1.png',
						'alt' => 'Website Review banner',
					),
				);
	
				$plugin_banners = array(
					array(
						'url' => 'https://admin.taveo.net/?nxt=/docs',
						'img' => 'banner-1.png',
						'alt' => 'Browse the Taveo Documentation',
					),
					//array(
					//	'url' => 'http://taveo.com/link1',
					//	'img' => 'banner-1.png',
					//	'alt' => 'Future Banner 2'
					//),
					//array(
					//	'url' => 'https://taveo.com/link1',
					//	'img' => 'banner-1.png',
					//	'alt' => 'Future Banner 3',
					//),
				);
	
				shuffle( $service_banners );
				shuffle( $plugin_banners );
				$service_banner = $service_banners[0];
				//Future Use
				//echo '<a target="_blank" href="' . esc_url( $service_banner['url'] ) . '"><img width="261" height="190" src="' . plugins_url( 'images/' . $service_banner['img'], __FILE__ ) . '" alt="' . esc_attr( $service_banner['alt'] ) . '"/></a><br/><br/>';
	
				$i = 0;
				foreach ( $plugin_banners as $banner ) {
					if ( $i == 2 ) {
						break;
					}
					echo '<a target="_blank" href="' . esc_url( $banner['url'] ) . '"><img width="261" src="' . plugins_url( 'images/' . $banner['img'], __FILE__ ) . '" alt="' . esc_attr( $banner['alt'] ) . '"/></a><br/><br/>';
					$i ++;
				}
				?>
				
			</div>
		</div>
	</div>
</div>
<?php }
?>
