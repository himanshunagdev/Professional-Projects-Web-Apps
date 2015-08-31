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

  $transactionMsg = $transactionResult ? "YHI PARTNERS CONCIERGE REQUEST FORM" : "Your transaction could not be completed.";

  $twig = (new TwigController)->getTwigEnvironment();
  $bookingDataArray = getBookings($_GET['booking_number']);
  
  $date = new DateTime($bookingDataArray['start_date']);
  $startDate = $date->format('F j, Y');
  $date = new DateTime($bookingDataArray['end_date']);
  $endDate = $date->format('F j, Y');
  dd($bookingDataArray);
  $conciergeCategoriesData = getConciergeCategoriesData();
   
  $allCategoryItems = array();

  /*foreach ($conciergeCategoriesData as $key => $value){
          if ($key != "Staffing")
              $allCategoryItems = array_merge($allCategoryItems,$value['items']);
  }*/

  //dd($conciergeCategoriesData['Staffing']['items'],$allCategoryItems);

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
    'YourAgent' => "Call YHI Booking at +1 (212) 244-4001 ext. 1 or email at booking@yhipartners.com for any general questions or concerns.",
    'LocalAgent' => "Call YHI Concierge at 1-212-244-4001 x200 or conceirge@yhipartners.com for any questions about the property for check in and check out.",
    'Concierge' => "Call +1 (212) 244-4001 ext. 200 or at concierge@yhipartners.com to request a follow up request on concierge services.",
    'EmergencyContact' => "Call +1 (212)  244-4001 in case of an emergency after hours."
  );

  $propertyTerms = array(
    'CheckIn' => "When you arrive in". $bookingDataArray['property_data']['market_name'].", please call YHI Concierge at 1-212-244-4001 x200 or conceirge@yhipartners.com.",
    'CheckOut' => "Upon departure, please leave the keys on the dining room table. If key is lost, the guest will be charged in full for new lock and key.",
    'Payment' => "If partial payment is made, please remit payment for the remaining balance 30 days prior to check in date.",
    'Cancellation' => "This reservation is non-refundable and cannot be changed or cancelled.",
    'StayExtensions' => "Stay extensions require a new reservation. Guests will be charged for late checkout or any unauthorized stay at 150% of the nightly rate for each additional day stayed.",
    'PropertyRules' => "Neither YHI Partners nor the property management shall be held responsible for any injury, loss or damage to any guests, their visitors or their property or except for damage caused as a result of YHI or the management’s gross negligence. Guests should immediately notify management if locks on doors or windows are not functioning properly or any other potentially hazardous condition is discovered. Guests are responsible for items that are provided with the property. Guests are responsible for ensuring their own property. Guests are responsible for keeping all areas of the property in good condition at all times (including bathrooms, kitchen, bedrooms, pool, laundry, and living room areas). Guests are responsible for the behavior of their visitors and any damages caused by them. Guests should keep the noise level to a minimum at all times (ex.please no loud music).",
    'Smoking' => "Absolutely NO smoking inside the property."
  );

  $conciergeData = array(
    'Staffing' => "Our Concierge Team accommodates the needs of any lifestyle, arranging staffing services such as: housekeeping, chauffeur, personal chef, private nanny, security, and much more.",
    'Activities' => "No excursion is complete without a full itinerary of activities and attractions including: sightseeing tours, yacht charters, sporting activities, as well as booking family-oriented expeditions.",
    'Entertainment' => "We craft the finest travel experiences by offering entertainment through the arrangement and procurement of restaurant and nightclub reservations, in-home entertainment, tickets to shows and sporting events, and more.",
    'Rentals' => "Get around any destination easily, with one of our convenient rental services available upon request.",
    'Transportation' => "You will never be left stranded with our exceptional travel accommodation services catering to any budget.",
    'EventPlanning' => "Let us do all the planning for your next holiday party or special event. We arrange everything including photographers, bartenders and servers, specialized catering, and event planning consultants."
  );

  // get booking data
  $bookingInfo = array(
    'bookingData' => $bookingData,
    'contactData' => $contactData,
    'conciergeData' => $conciergeData,
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

