<?php
  require_once('../setBackendPath.php');
  require_once($backendPath.'config.php');
  require_once ('../templates/twigController.php');
  require_once('commonWeb.php');
    
  // check if valid booking
   if ( !isset($_REQUEST['booking_number']) || !isset($_REQUEST['guest_email']) ) {
	   die ("Access Denied");
   } else {
	   $booking_number=$_REQUEST['booking_number'];
   }
   $bookingDataArray = getBookings($booking_number);

   if (count($bookingDataArray)==0 ){
      die ("Booking Number Not Valid");
   }
   if($_REQUEST['guest_email']!== $bookingDataArray['client_contact_data']['email_address']){
      die ("Email Not Valid");
   }

   // load conceirge options data
   $conciergeCategoriesData = loadSelectTree("services");
   $preferencesData = loadSelectTree("services_internal");

  // load or save file 
  $jsonFilename ="conciergeRequest_".$booking_number . ".json";  
  if (count($_POST)!==0){
    //save data
    $requestData = $_POST;
    foreach ($requestData as $key => $value){
      //eliminates if no text or data is entered 
      if (empty($value)) 
        unset($requestData[$key]);
    }
    file_put_contents($jsonFilename,json_encode($requestData));
  } else {
    	$requestData=array(); 		
    	// load data from file
    	if (file_exists($jsonFilename)){
    		$requestData=json_decode(file_get_contents($jsonFilename),true); 	
      } 
  }
  dd($requestData);

  $bookingData = array(
    'BookingNumber' => $bookingDataArray['booking_number'],
    'PropertyLocation' => $bookingDataArray['property_data']['area_name'] . " " . $bookingDataArray['property_data']['country_name'],
    'GuestFirstName' => $bookingDataArray['client_contact_data']['first_name'],
    'GuestLastName' => $bookingDataArray['client_contact_data']['last_name'],
    'Cell'=> $bookingDataArray['client_contact_data']['phone_mobile'],
    'PropertyName'=>$bookingDataArray['property_data']['property_name'],
    'PropertyBedrooms'=>$bookingDataArray['property_data']['listing_bedrooms'],
    'GuestEmail'=>   $bookingDataArray['client_contact_data']['email_address']
  );

  // define form
  $formAction = $_SERVER['PHP_SELF'];
  $twig = (new TwigController)->getTwigEnvironment(); 

  // get concierge form data
  $conciergeInfo = array(
    'formAction'=> $formAction,
    'booking_number' => $booking_number,
    'bookingData' => $bookingData,
    'conciergeCategoriesData'=>$conciergeCategoriesData,
    'preferencesData' => $preferencesData,
	  'requestData' => $requestData
  );

  // start page
  $pageTitle=$defaultCompanyName." - Concierge Request"; 

  include('head.php');
  ?>
  <body>  
  <?php
    require("analyticstracking.php");    
    require("mainNav.php");
  ?>
  <div class="container-fluid" style="padding-bottom:5%;padding-left:5%;padding-right:5%;">  
      <div class="row">
        <div class="col-sm-12">
          <h2><?php echo "CONCIERGE REQUEST";?></h2>
        </div> 
      </div>
    <div class="row">
      <div class="col-sm-12">
        <?=$twig->render('conciergeRequest.twig', $conciergeInfo)?>
      </div>
    </div>
  </div>
  
  <? include('footer.php'); ?>
  </body>
