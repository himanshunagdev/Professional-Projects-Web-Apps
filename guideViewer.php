<?php
    // displays an itinerary using a csv guest folder

	// load configuration
	require_once("../setBackendPath.php");
	require_once($backendPath."config.php");
    require_once("commonWeb.php");

	// check if file exists and load
    $dataId = ( isset($_GET["id"])) ? $_GET["id"] : "";
	$rawDataArray=getBlogData($dataId);           
    //$csvFilename = GUEST_IMAGE_URL.'guide_'.$dataId.'.csv';
    $destinationData=loadMarkets("market_types",$dataId);
    function getBlogData($marketName){
    //create client
	    $consumerKey="Uzk96TtxXZPJJLaqU3a91VoYN1Un5ewaluxUEpcQF5gfURlsEA";
	    $consumerSecret="kphLJhxkE8M7hAm29SHT01gb50sV4wx8s7Lxc98eaf8FkSD6Hc";
	    $blogName='yhipartners.tumblr.com';

	    $client = new Tumblr\API\Client($consumerKey, $consumerSecret);
	    $response=$client->getBlogPosts(($blogName), array('tag' => $marketName));
	    // get posts
	    $posts=$response->posts;
	    $blogs=$response->blog;
	    
		$x=0;
		foreach ($posts as $post){

			if (isset($post->title)){ 

			    $blogData[]=array(
			          'title' => $post->title,
			          'body' => $post->body,
			          'slug' => $post->slug,
			          'tags' => $post->tags
			    );
			}    
		$x++;
		}     
		return $blogData;
	}
	$dataArray=$rawDataArray;

    /*//if(file_get_contents($csvFilename) && $destinationData){
        $rawDataArray = loadArrayFromCSV($csvFilename);*/
				
	// get destination name
	$destinationName=$destinationData['label'];

	//creation sections
	$dataArray=convertToSections($rawDataArray["contents"],"type");
	dd($dataArray);

	// get first section name
	//reset($dataArray); // make sure array pointer is at first element
	$initialSectionName =  $dataId = ( isset($_GET["sectionName"])) ? $_GET["sectionName"] : key($dataArray); 

	if (isset($_GET["test"])){
		dd($destinationData, $dataArray);
	}
	} else { 
		die('Invalid ID');
	}
	

		
	function convertToSections($dataArray,$sectionCol){
		$returnArray=[];
		$oldType="";
		
		// creation sections
		foreach($dataArray as $data) {
			$type = $data[$sectionCol];			
			if ($type !== $oldType){
				$returnArray[$type]=[];
				$oldType=$type;
			}
			$returnArray[$type][]=$data;
		}	
		
		return $returnArray;
	}
		 

    // creates individual sections of city guide based on number of rows in dataArray which corresponds to the number of sections in guide
    function createSection($sectionArray, $sectionName, $destinationName){		
		
       
 	}

 	function shortenedDescription($description, $length){
 		if (strlen($description) < $length)
 			return $description;
 		else
        	return substr($description, 0, stripos ($description, "." ,$length)+1);
	}

    // set default page information
    $pageTitle=$defaultCompanyName." ".$destinationName." Guide";
    include("head.php");
    ?>
    <style>
	    .guide-item{
			background-color: white;
			color:black;
			margin-top:10px;
			margin-bottom: 10px;
			margin-left: 0px;
			margin-right: 0px;
			padding: 10px;
			height: 550px;
			border: 1px solid black;

		}

		.body-background-color{
			background-color: lightgrey; 
		}

		.row-left-space{
			padding-left: 15px;
		}

		.remove-col-left-space{
			padding-left: 0px;
		}

		.img-styling{
			margin:0;
			height:220px;
			width:100%;
		}

		.bolded-font{
			font-weight: bold;
		}

		.text-justify{
			text-align: justify;
		}
		.description-top-padding{
			padding-top: 5%;
		}

		.label-top-padding{
			padding-top: 2%;
			font-size:20px;
		}

		.area-color{
			color: grey;
		}

		.header-title-color{
			color: white !important;
		}

		#concierge-contact-info{
			padding-top: 10px;
			padding-bottom: 15px;
		}

	</style>
    <?php
    echo '<body class="pull_top body-background-color"> ';    

    require("analyticstracking.php");    
    require("mainNav.php");

?> 

   <div class="container-fluid landing_wrapper">
	   
       <div class="row">
           <div class="col-lg-12 col-md-12 col-sm-12 pull-left">
               <div class="landing_header_title header-title-color"><?=$destinationName?> Guide</div>
               <div id='concierge-contact-info'>
               		<div>Please contact our Concierge team with your inquiry at anytime.</div>
               		<div>+1-212-244-4001 x200 | <a href="mailto:concierge@yhipartners.com">concierge@yhipartners.com</a></div>
               	</div>
               <div class="itinerary_property_description"><?=$destinationData['description']?></div>
           </div>    
       </div>
	   
        <div class="row">
      		<div class="col-lg-12 col-md-12 col-sm-12 pull-left">
                <div class="viewNavigator">
					<div class="row">
                  	  <div class="btn-group btn-group-justified" id="viewNavigatorButtonGroup">
		                  <? 
		                  foreach($dataArray as $sectionName=>$sectionArray):
		                      echo '<a class="btn btn-md viewButton col-xs-6" role="button" id="'.$sectionName.'_button">'.ucfirst($sectionName).'</a>';
		  				   endforeach;
		                  ?>
					   </div>		
                    </div>
                </div>
				<hr class="dark_line_break">
			</div>
		</div>	

        <div class="row">
      		<div class="col-lg-12 col-md-12 col-sm-12 pull-left">		
                <? 
                $k=0;
                foreach($dataArray as $sectionName=>$sectionArray){
                
			        echo '<div class="hidden section_wrapper" id="'.$sectionName.'_view">';
			      	 	echo '<p class="landing_header_subtitle">'.strToUpper($sectionName).'</p>';
			      		echo '<hr class="dark_line_break" />';						

		         	while(!empty($sectionArray)){
		         		$rowArray = array_slice($sectionArray,0, 3);
		     			$sectionArray = array_splice($sectionArray, 3);

		     			echo "<div class='row row-left-space'>";
		     			for ($i = 0; $i < count($rowArray); $i++){
		     				echo "<div class='col-sm-4 remove-col-left-space'>";
		     					echo "<div class='guide-item'>";
			     					echo "<img src=" . $rowArray[$i]['image'] . " class='img-responsive img-styling'>";
			     					echo "<div class='bolded-font label-top-padding'>" . $rowArray[$i]['name'] . "</div>";	
			     					echo "<div class='bolded-font area-color'>" . $rowArray[$i]['area'] . "</div>";
			     					echo "<div class='text-justify description-top-padding'>" . shortenedDescription($rowArray[$i]['description'],250) . "</div>"; 
		     					echo "</div>";
		     				echo "</div>";
		     			}
		     			echo "</div>";
		     		$k++;
		     		}

		     		?>
		            <hr class="section_footer">
		            <br>
					<?php
			        echo '</div>';
				}
                ?>
            </div>
  	  	</div>
    </div>
            
    <?php include("footer.php");?>
</body>
</html>
<script>
    $(document).ready(function(){

        var sectionView="";  
  		sectionView=switchCurrentView(sectionView,"<?=$initialSectionName?>","selectedViewButton"); 	
  	    $('.viewButton').click(function(event) {
			event.preventDefault();
  	        var buttonName=$(this).attr("id");
  	        var newView=buttonName.substr(0,buttonName.indexOf("_"));
			sectionView=switchCurrentView(sectionView,newView,"selectedViewButton"); 	
  	    });
    });
</script>