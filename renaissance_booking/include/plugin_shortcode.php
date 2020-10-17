<?php
add_shortcode('booking', 'rb_booking_shortcode');
function rb_booking_shortcode($attr) {
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


$html= '<div class="card" style="background: white;padding: 20px;">
          
          <div class="card-body">
            <h5 class="card-title text-center">BOOK A SLOT</h5>
            <p class="card-text">This is an appointment page which helps us to give you the best service...</p>
            <p class="card-text">Select your preferred slot for our partner visit:</p><div id="all_data">';
            
             if($data['message']=='SUCCESS'){
              $i=0;
              foreach ($slots as $key => $value) {

                
                if($i >= 3){
                  break;
                }else{ 
                  $html .= '<input class="form-check-input mycheck" type="radio"  name="key" id="key'.$key.'" value="'.$key.'" > '.$value['day'].','.$value['date'].', '.$value['startTime'].' to '.$value['endTime'].'<input type="hidden" name="jobId" id="jobId'.$key.'" value="'.$value['jobId'].'"><input type="hidden" name="mobileNumber" id="mobileNumber'.$key.'" value="'.$value['mobileNumber'].'"><input type="hidden" name="date" id="date'.$key.'" value="'.$value['mobileNumber'].'"><input type="hidden" name="day" id="day'.$key.'" value="'.$value['day'].'"><input type="hidden" name="startTime" id="startTime'.$key.'" value="'.$value['startTime'].'"><input type="hidden" name="endTime" id="endTime'.$key.'" value="'.$value['endTime'].'"><input type="hidden" name="virtualGroup" id="virtualGroup'.$key.'" value="'.$value['virtualGroup'].'"><br>';
                  $i++; 
                }
                

               
              }

             }else{
              $html .= 'No Data Found';
             }
              
              
   $html .= '</div></div>';
   if($i!=0){
    $html .='<a href="javascript:;" onclick="load_more();" id="btnload_more" class="btn btn-primary float-right">Load More</a>
    <a href="javascript:;" class="btn btn-primary float-left" onclick="submit_data();" id="btnsubmit">Submit</a>';
   }
   
   $html .= '</div>
  
</div>';

return $html;
}
