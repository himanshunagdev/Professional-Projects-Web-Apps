<?php
  
  require_once('../setBackendPath.php');
  require_once($backendPath.'config.php');
  require_once ('../templates/twigController.php');
  require_once('../integration/processPayment.php');
  require_once('commonWeb.php');
  
  if (count($_POST)==0){
      $transactionResult = true;
  } else {
      $expirationMonth = date_parse($_POST['month'])['month'];
      $expirationYear = intval($_POST['year']);
      $cardNumber = $_POST['cardnumber'];
      $instance = new CreditCardController(35,$cardNumber,$expirationMonth,$expirationYear); 
      $transactionResult = $instance->processSale();
  }

  $transactionMsg = $transactionResult ? "" : "";

  $twig = (new TwigController)->getTwigEnvironment();
  $bookingDataArray = getBookings($_GET['booking_number']);
  
  $date = new DateTime($bookingDataArray['start_date']);
  $startDate = $date->format('F j, Y');
  $date = new DateTime($bookingDataArray['end_date']);
  $endDate = $date->format('F j, Y');
  dd($bookingDataArray);
  $conciergeCategoriesData = getConciergeCategoriesData();
   
  $allCategoryItems = array();

  $bookingData = array(
    'BookingDate' => substr($bookingDataArray['created'],0,strpos($bookingDataArray['created']," ")), 
    'BookingNumber' => $bookingDataArray['booking_number'],
    'ArrivalDate' => $startDate,
    'DepartureDate' => $endDate,
    'PropertyType' => $bookingDataArray['property_data']['type_name'],
    'PropertyAddress' => $bookingDataArray['property_data']['area_name'] . " " . $bookingDataArray['property_data']['country_name'],
    'GuestName' => $bookingDataArray['client_account_name'],
    'GuestEmail' => $bookingDataArray['client_contact_data']['email_address'],
    'GuestPhone' => ($bookingDataArray['client_contact_data']['phone_home']?$bookingDataArray['client_contact_data']['phone_home']:$bookingDataArray['client_contact_data']['phone_mobile']) 
  );

  $contactData = array(
    'YourAgent' => "",
    'LocalAgent' => "",
    'Concierge' => "",
    'EmergencyContact' => ""
  );

  $propertyTerms = array(
    'CheckIn' => "",
    'CheckOut' => "",
    'Payment' => "",
    'Cancellation' => "",
    'StayExtensions' => "",
    'PropertyRules' => "",
    'Smoking' => "Absolutely NO smoking inside the property."
  );

  // get booking data
  $bookingInfo = array(
    'bookingData' => $bookingData,
    'contactData' => $contactData,
    'propertyTerms' => $propertyTerms
  );

  // initialize templates
  $twig = (new TwigController)->getTwigEnvironment();
  $actionPageURL = "bookNowConfirmation.php?booking_number=".$bookingDataArray['booking_number'];

  // start page
  $pageTitle=$defaultCompanyName." - Book Now Confirmation";  
  $openHeadTag=true;

  include('head.php');
  ?>
  <body>  
  <?php
     require("analyticstracking.php");    
     require("mainNav.php");
  ?>
     <div class="container">
      
      <div class="row">
        <div class="col-sm-12">
        	<h2><?php echo $transactionMsg;?></h2>
        </div> 
      </div>

      <?php if ($transactionResult) { ?>
        <div class="row">
          <div class="col-sm-12">
            <?php
              echo $twig->render('bookNowConfirmation.twig', $bookingInfo);
            ?>
          </div>
        </div>
      <?php } ?>

      </div>
    <? include('footer.php'); ?>
  </body>

