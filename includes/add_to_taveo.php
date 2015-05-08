<?php  
//Security Enhancement
defined( 'ABSPATH' ) or die( 'Go Away.' );

add_action( 'post_submitbox_misc_actions', 'publish_in_frontpage' );
function publish_in_frontpage($post) {
?>
	<div class="misc-pub-section misc-pub-section-last" id="publish_in_frontpage">         
         <div id="tlinkmsg" style="display: none;">
         	<span id="taveos" class="wp-media-buttons-icon">There are currently 
         	<a class="scroll" href="#taveo_meta_links"><span id="curtlinks"></span> Taveo links</a> for this page.
         	</span>
         </div>
         <a href="#" class="preview button" id="taveo_post_btn">Track with Taveo</a><br>
         <div class="clear"></div>
    </div>
   
<?php    
}

function taveo_metabox_callback ( $post, $metabox ) {
?>
	<div id="tlinkdata">
		<table id="tlinktable" class="display text-center" cellspacing="0">
			<thead>
		        <tr>
		            <th>URL</th>
		            <th>Created</th>
		            <th></th>
		            <th>Last Click</th>
		            <th></th>
		            <th>Total Clicks</th>
		            <th>Comment</th>
		            <th>State</th>
		            <th>&nbsp;</th>
		        </tr>
	    	</thead>
			<tfoot>
		        <tr>
		            <th>URL</th>
		            <th>Created</th>
		            <th></th>
		            <th>Last Click</th>
		            <th></th>
		            <th>Total Clicks</th>
		            <th>Comment</th>
		            <th>State</th>
		            <th>&nbsp;</th>
		        </tr>
	    	</tfoot>    	
	    	<tbody>
	    </table>						
	</div>
<?php	 
}

//ajax callback to get current post url, so if user edits permalink while on the page
add_action( 'wp_ajax_taveo_get_permalink', 'taveo_get_permalink_callback' );

function taveo_get_permalink_callback() {
	echo get_permalink( $_POST['pid'] );
	wp_die(); // this is required to terminate immediately and return a proper response
}
?>
