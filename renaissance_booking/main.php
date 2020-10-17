<?php
/**
 * Plugin Name: Renaissance Booking
 * Plugin URI: https://renaissance.tk
 * Description: This is custom registration and login with ajax plugin.
 * Version: 1.0
 * Author: renaissance
 * Author URI: https://renaissance.tk
 */

add_action( 'wp_enqueue_scripts', 'rb_wedding_scripts' );
function rb_wedding_scripts() {
  
  wp_enqueue_style( 'rb-bootstrap-css', plugin_dir_url(__FILE__) . 'css/bootstrap.css', array(), '3.3.7', 'all');
  wp_enqueue_script( 'rb-bootstrap-script', plugin_dir_url(__FILE__)  . 'js/bootstrap.js', array ( 'jquery' ), 3.7, true);
  wp_enqueue_script( 'rb-bootstrap-script', plugin_dir_url(__FILE__)  . 'js/jquery.min.js', array ( 'jquery' ), 3.7, true);
}
require_once('include/plugin_shortcode.php');


add_filter( 'theme_page_templates', 'pt_add_page_template_to_dropdown' );
function sf_add_page_template_to_dropdown( $templates )
{
   $templates[plugin_dir_path( __FILE__ ) . 'booking-template.php'] = __( 'Page Template From Plugin', 'text-domain' );
 
   return $templates;
}
//craete table and page
function create_plugin_database_table_page() {
	global $wpdb;
	$table_name = $wpdb->prefix.'booking';
	if($wpdb->get_var( "show tables like '$table_name'" ) != $table_name) 
    {
    	$sql = "CREATE TABLE $table_name (
		id mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
		jobId varchar(50) NOT NULL,
		mobileNumber varchar(50) NOT NULL,
		job_date date NOT NULL default '0000-00-00',
		day varchar(50) NOT NULL,
		startTime varchar(50) NOT NULL,
		endTime varchar(50) NOT NULL,
		virtualGroup varchar(50) NOT NULL,
		create_date datetime NOT NULL default '0000-00-00 00:00:00',
		PRIMARY KEY  (id)
		);";

	    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	    dbDelta( $sql );
    }

    $login_page_title = 'booking';
    $login_page_content = '[booking]';
    $login_page_template = 'booking-template.php'; 

    $page_check_login = get_page_by_title($login_page_title);
    $login_page = array(
            'post_type' => 'page',
            'post_title' => $login_page_title,
            'post_content' => $login_page_content,
            'post_status' => 'publish',
            'post_author' => 1,
    );
    if(!isset($page_check_login->ID)){
            $login_page_id = wp_insert_post($login_page);
            if(!empty($login_page_template)){
                    update_post_meta($login_page_id, '_wp_page_template', $login_page_template);
            }
    }

    
}

register_activation_hook( __FILE__, 'create_plugin_database_table_page' );

//create pages


/*signup function end*/

register_deactivation_hook( __FILE__, 'my_plugin_remove_database' );
function my_plugin_remove_database() {
     global $wpdb;
     $table_name = $wpdb->prefix . 'booking';
     $sql = "DROP TABLE IF EXISTS $table_name";
     $wpdb->query($sql);
     delete_option("my_plugin_db_version");

     $page = get_page_by_path( 'booking' );
    wp_delete_post($page->ID,true);

    

   
}


/*load more data*/
add_action('wp_ajax_load_more_slot', 'load_more_slot_act');
add_action('wp_ajax_nopriv_load_more_slot', 'load_more_slot_act');

function load_more_slot_act() {
global $wpdb;

$url='https://renaissance.tk/machine_test_wp/webservices/get-slots';
//  Initiate curl
$ch = curl_init();
// Will return the response, if false it print the response
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// Set the url
curl_setopt($ch, CURLOPT_URL,$url);
// Execute
$result=curl_exec($ch);
// Closing
curl_close($ch);
$data=json_decode($result, true);
$body=$data['body'];
$slots=$body['slots'];

$html='';
if($data['message']=='SUCCESS'){
foreach ($slots as $key => $value) {


$html .= '<input class="form-check-input mycheck" type="radio"  name="key" id="key'.$key.'" value="'.$key.'" > '.$value['day'].','.$value['date'].', '.$value['startTime'].' to '.$value['endTime'].'<input type="hidden" name="jobId" id="jobId'.$key.'" value="'.$value['jobId'].'"><input type="hidden" name="mobileNumber" id="mobileNumber'.$key.'" value="'.$value['mobileNumber'].'"><input type="hidden" name="date" id="date'.$key.'" value="'.$value['mobileNumber'].'"><input type="hidden" name="day" id="day'.$key.'" value="'.$value['day'].'"><input type="hidden" name="startTime" id="startTime'.$key.'" value="'.$value['startTime'].'"><input type="hidden" name="endTime" id="endTime'.$key.'" value="'.$value['endTime'].'"><input type="hidden" name="virtualGroup" id="virtualGroup'.$key.'" value="'.$value['virtualGroup'].'"><br>';



}

}else{
$html .= 'No Data Found';
}

$return['status']= 0;
$return['html']= $html;


echo json_encode($return);
exit();
}


/*booking*/
add_action('wp_ajax_book_slot', 'book_slot_act');
add_action('wp_ajax_nopriv_book_slot', 'book_slot_act');

function book_slot_act() {
	global $wpdb;
    $created_date =date('Y-m-d H:i:s');
	$wpdb->insert('wp_booking', array(
    'jobId' => $_POST['jobId'],
    'mobileNumber' => $_POST['mobileNumber'],
    'job_date' => $_POST['date'], 
    'day' => $_POST['day'], 
    'startTime' => $_POST['startTime'], 
    'endTime' => $_POST['endTime'], 
    'virtualGroup' => $_POST['virtualGroup'], 
    'create_date' => $created_date,
    ));


	$data = array(
    'jobId' => $_POST['jobId'],
    'mobileNumber' => $_POST['mobileNumber'],
    'virtualSlot'   => array(
    	'date' => $_POST['date'], 
	    'day' => $_POST['day'], 
	    'startTime' => $_POST['startTime'], 
	    'endTime' => $_POST['endTime'], 
	    'virtualGroup' => $_POST['virtualGroup'], 
	    'create_date' => $created_date,
    )
	);

	
	
	# Create a connection
	$url = 'https://renaissance.tk/machine_test_wp/webservices/book-slot';
	$ch = curl_init($url);
	# Form data string
	$postString = http_build_query($data, '', '&');
	# Setting our options
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $postString);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	# Get the response
	$response = curl_exec($ch);
	curl_close($ch);
	$data=json_decode($response, true);
	

	if($data['success']==true){
		$return['status']= 0;
	    $return['html']= '<div class="alert alert-success" role="alert">
		  '.$data['message'].' !!
		</div>';
	}else{
		$return['status']= 1;
	   $return['html']= '<div class="alert alert-success" role="alert">
		  Somthing went wrong !!
		</div>';;
	}
	


	echo json_encode($return);
	exit();
}

add_action('wp_footer', 'rb_hook_javascript_footer');
function rb_hook_javascript_footer() {
?>
 <script>
 	function load_more(){
 		
	jQuery("#btnload_more").html('Loading..');
	
   jQuery.ajax({
    url : '<?php echo admin_url('admin-ajax.php');?>',
    type : 'post',
    data : {action:'load_more_slot'},
    success : function( response ) {  
    //alert(response);      
        response = jQuery.parseJSON(response);       
        if(response.status==0){
          jQuery("#btnload_more").html('Load More');
          jQuery("#all_data").html(response.html);
          
        }else{
             jQuery("#btnload_more").html('Load More');
            
             
        }
    }
    });
	
  }

  function submit_data(){
  	  if (jQuery("input[name='key']:checked").length) {
          var key = jQuery("input[name='key']:checked").val();
          
          var jobId = jQuery("#jobId"+key).val();
          var mobileNumber = jQuery("#mobileNumber"+key).val();
          var date = jQuery("#date"+key).val();
          var day = jQuery("#day"+key).val();
          var startTime = jQuery("#startTime"+key).val();
          var endTime = jQuery("#endTime"+key).val();
          var virtualGroup = jQuery("#virtualGroup"+key).val();
          jQuery("#btnsubmit").html('Sending..');
          jQuery.ajax({
		    url : '<?php echo admin_url('admin-ajax.php');?>',
		    type : 'post',
		    data : {jobId:jobId,mobileNumber:mobileNumber,date:date,day:day,startTime:startTime,endTime:endTime,virtualGroup:virtualGroup,action:'book_slot'},
		    success : function( response ) {  
		         
		        response = jQuery.parseJSON(response);       
		        if(response.status==0){
		          jQuery("#btnsubmit").html('Submit');
		          jQuery("#all_data").html(response.html);
		          
		        }else{
		             jQuery("#btnsubmit").html('Submit');
		             jQuery("#all_data").html(response.html);
		            
		             
		        }
		    }
		    });

  	      
      }
      else {
          
  	    alert('Please select at least one slot !!');
      }
  	
  }
 </script>
<?php
}