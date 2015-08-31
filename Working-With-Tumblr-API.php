<?php
// load configuration
require_once("../setBackendPath.php");
require_once($backendPath."config.php");
require_once($backendPath.'securityFunctions.php');
require_once("commonWeb.php");

//$dataId is the tag name    
$dataId = ( isset($_GET["id"])) ? $_GET["id"] : "";
//array of blog-data
$blogDataArray=getBlogData($dataId);           

function getBlogData($marketName){
    //create client
    $consumerKey="Add your Key";
    $consumerSecret="Add Secret key";
    $blogName='yr_username.tumblr.com';

    $client = new Tumblr\API\Client($consumerKey, $consumerSecret);
    $response=$client->getBlogPosts(($blogName), array('tag' => $marketName));
    // get posts
    $posts=$response->posts;
    $blogs=$response->blog;
    global $dataId;
	$x=0;
	foreach ($posts as $post){

		if (isset($post->title)){ 
			if(isset($post->body)){
			    $dataArray[]=array(
					'imageSrc'=> getImgSrc($post->body,"<img","/>"),
					'title'=>$post->title,
					'tags'=>convertTagsIntoString(array_map('strval',$post->tags)),
					'description'=>trimblanklines(trimblankspaces(shortenedDescription(getDescription($post->body,"</p>",$dataId),250))),
					'postId'=> $post->id	
				);
			}
		}  
	$x++;
	}   
	return $dataArray;
}

//dd ($blogDataArray);
//To get first image of the blog post
function getImgSrc($body,$startTag, $endTag){
	$startPos=strpos(substr($body,0,strlen($body)),$startTag);
	$endPos=strpos(substr($body,$startPos,strlen($body)),$endTag);
	$substr=substr($body,0,strlen($body));
	$imgSrc=substr( $substr, $startPos, $endPos);
	return $imgSrc;
}

//getting the description without the image
function getDescription($body,$startTag,$dataId){ 
	if ($dataId == 'california'){
		$description = $body;
		$description = preg_replace("/<img[^>]+\>/i", "", $description); 
		return substr($description,stripos($description,"<p>"),stripos($description,"</p>"));
	}
	$startPos=strpos(substr($body,0,strlen($body)),$startTag)+strlen($startTag);
	$substr=substr($body,0,strlen($body));
	$description=substr($substr, $startPos);
	return strip_tags($description,"<br></br>");
}

//get tags 
function convertTagsIntoString($tagNames){
	$str = "";
	for ($i = 0; $i < count($tagNames); $i++)
		$str .= "<a href=" . $_SERVER['PHP_SELF'] . "?id=" . urlencode(utf8_encode($tagNames[$i])) .">" . "#" . $tagNames[$i] . "</a>" . ($i==count($tagNames)-1? "." : ", ");
	return $str;
}
//for trimming the UTF-8 description
function trimblankspaces( $string ) 
{ 
    $string = preg_replace( "/(^\s+)|(\s+$)/us", "", $string ); 
    
    return $string; 
}
function trimblanklines($str, $charlist = NULL, $encoding = NULL) {
        if ($encoding === NULL) {
            $encoding = mb_internal_encoding(); // Get internal encoding when not specified.
        }
        if ($charlist === NULL) {
            $charlist = "\\x{20}\\x{9}\\x{A}\\x{D}\\x{0}\\x{B}"; // Standard charlist, same as trim.
        } else {
            $chars = preg_split('//u', $charlist, -1, PREG_SPLIT_NO_EMPTY); // Splits the string into an array, character by character.
            foreach ($chars as $c => &$char) {
                if (preg_match('/^\x{2E}$/u', $char) && preg_match('/^\x{2E}$/u', $chars[$c+1])) { // Check for character ranges.
                    $ch1 = hexdec(substr($chars[$c-1], 3, -1));
                    $ch2 = (int)substr(mb_encode_numericentity($chars[$c+2], [0x0, 0x10ffff, 0, 0x10ffff], $encoding), 2, -1);
                    $chs = '';
                    for ($i = $ch1; $i <= $ch2; $i++) { // Loop through characters in Unicode order.
                        $chs .= "\\x{" . dechex($i) . "}";
                    }
                    unset($chars[$c], $chars[$c+1], $chars[$c+2]); // Unset the now pointless values.
                    $chars[$c-1] = $chs; // Set the range.
                } else {
                    $char = "\\x{" . dechex(substr(mb_encode_numericentity($char, [0x0, 0x10ffff, 0, 0x10ffff], $encoding), 2, -1)) . "}"; // Convert the character to it's unicode codepoint in \x{##} format.
                }
            }
            $charlist = implode('', $chars); // Return the array to string type.
        }
        $pattern = '/(^[' . $charlist . ']+)|([' . $charlist . ']+$)/u'; // Define the pattern.
        return preg_replace($pattern, '', $str); // Return the trimmed value.
    }

//Shorten the description
function shortenedDescription($description, $length){
	if (strlen($description) < $length)
		return $description;
	else 
	return substr($description, 0, stripos ($description, "." ,$length)+1);	
}

// set default page information
$pageTitle=$defaultCompanyName." ".strToUpper($_GET["id"])." Guide";
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
		height:250px;
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
		font-weight: 500px;
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
           <div class='landing_header_title header-title-color'><?=strToUpper($_GET["id"])?> BLOG</div>
           <div id='concierge-contact-info'>
           		<div>Please contact our Concierge team with your inquiry at anytime.</div>
           		<div>+1-212-244-4001 x200 | <a href="mailto:concierge@yhipartners.com">concierge@yhipartners.com</a></div>
           	</div>
           <div class="itinerary_property_description"><?"body HERE"?></div>
       </div>    
   </div>
    <div class="row">
  		<div class="col-lg-12 col-md-12 col-sm-12 pull-left">		
            <? 
            echo "<hr class='dark_line_break'/>";
           	echo "<p class='landing_header_subtitle'>".strToUpper($_GET["id"])."</p>";
           	echo "<hr class='dark_line_break'/>";    
            foreach($blogDataArray as $sectionName=>$sectionArray){  	
	         	while(!empty($sectionArray)){
	         		$rowArray = array_slice($sectionArray,0, 5);
	     			$sectionArray = array_splice($sectionArray,5);
		     			echo "<div class='col-sm-4 remove-col-left-space'>";
			     			echo "<div class='guide-item'>"; 
	     						echo $rowArray['imageSrc'] . "class='img-responsive img-styling'>";
	     						echo "<div class='bolded-font label-top-padding'><a href=http://yhipartners.tumblr.com/post/".$rowArray['postId']."/".$rowArray['title'].">".$rowArray['title']."</a></div>";
				     			echo "<div class='bolded-font area-color'>" . $rowArray['tags'] . "</div>";
				     			echo "<div class='text-justify description-top-padding'>" . $rowArray['description'] . "</div>";	
		     				echo "</div>";
		     			echo "</div>";
	     		}		     	
	     	}
			?>
			<hr class="section_footer">
			<br>
			<?php
				echo '</div>';
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
