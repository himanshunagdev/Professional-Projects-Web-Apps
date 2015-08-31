<?php
require_once("../setBackendPath.php");
require_once($backendPath."adminModuleStart.php");
?>    

	<!-- load scheduler-->
    <link rel="STYLESHEET" href="../dhtmlxScheduler/codebase/dhtmlxscheduler.css" type="text/css" media="screen" title="no title" charset="utf-8">		
    <script src="../dhtmlxScheduler/codebase/dhtmlxscheduler.js" type="text/javascript"></script>

    <!-- Google Maps -->
    <script src="http://maps.google.com/maps/api/js?sensor=false"></script>

    <script src='../dhtmlxScheduler/codebase/sources/ext/dhtmlxscheduler_minical.js' type="text/javascript"></script>
    <script src='../dhtmlxScheduler/codebase/sources/ext/dhtmlxscheduler_recurring.js' type="text/javascript"></script>
    <script src='../dhtmlxScheduler/codebase/sources/ext/dhtmlxscheduler_agenda_view.js' type="text/javascript"></script>
    <script src="../dhtmlxScheduler/codebase/sources/ext/dhtmlxscheduler_map_view.js"></script>
    <script src="../dhtmlxScheduler/codebase/sources/ext/dhtmlxscheduler_key_nav.js" type="text/javascript" charset="utf-8"></script>
    <script src="../dhtmlxScheduler/codebase/sources/ext/dhtmlxscheduler_editors.js"></script>
    <script src="../dhtmlxScheduler/codebase/sources/ext/dhtmlxscheduler_year_view.js"></script>
   
    <style>
        html,body {
            width: 100%;
            height: 100%;
            overflow: hidden;
            margin: 0px;
            padding: 0px;
        }

        .well {
            text-align: left;
        }
        .container-fluid #scheduler_here {
            height: 700px;
            width:  100%;
            border: 2px solid #cecece;
        }
        #scheduler_here {
            border-radius: 4px;
        }
        /*event in day or week view*/
        .dhx_cal_event.Guide_event div{
            background-color: #FF3300 ;
            color: black !important;
        }
        .dhx_cal_event.Logistic_event div{
            background-color: #FF9933 ;
            color: black !important;
        }
        .dhx_cal_event.Staffing_event div{
            background-color: #FF9900 ;
            color: black !important;
        }
        /*multi-day event in month view*/
        .dhx_cal_event_line.Guide_event{
            background-color: #FF3300 ;
            color: black !important;
        }
        .dhx_cal_event_line.Logistic_event{
            background-color: #FF9933 ;
            color: black !important;
        }
        .dhx_cal_event_line.Staffing_event{
            background-color: #FF9900 ;
            color: black !important;
        }
        /*event with fixed time, in month view*/
        .dhx_cal_event_clear.Guide_event{
            color: black !important;
        }
        .dhx_cal_event_clear.Logistic_event{
            color: black !important;
        }
        .dhx_cal_event_clear.Staffing_event{
            color: black !important;
        }

    </style>                            
</head>

<body>

    <div class="container-fluid">

        <div class="dhx_cal_container panel" id="scheduler_here">
            <div class="dhx_cal_navline"> 

                <div class="dhx_cal_prev_button">&nbsp;</div>
                <div class="dhx_cal_next_button">&nbsp;</div>
                <div class="dhx_cal_today_button"></div>
                <div class="dhx_cal_date"></div>
                <div class="dhx_cal_tab" name="agenda_tab" style="right:280px;"></div>
                <div class="dhx_cal_tab" name="map_tab" style="right:280px;"><label>Map</label></div>
                <div class="dhx_cal_tab" name="day_tab" ></div>
                <div class="dhx_cal_tab" name="week_tab" ></div>
                <div class="dhx_cal_tab" name="month_tab" ></div>
                <div class="dhx_cal_tab" name="year_tab" style="right:280px;"></div>
                <div class="dhx_cal_date"></div>
                <div class="dhx_minical_icon" id="dhx_minical_icon" 
                    onclick="show_minical()">&nbsp;
                </div>
            </div>

            <div class="dhx_cal_header"></div>
            <div class="dhx_cal_data"></div>

        </div>
       		  <div style='float: left; padding:10px;'>
        	  <div id="cal_here" style='width:250px;'></div>
   	   	</div>
    </div>   

    
<script type="text/javascript">
	
	var initDatastoreController;
	var lookupDatastore;
    var guideDatastore;
	var bookingsDatastore;
	var booking_id;
    var scheduler;
    var priorities;
    var objects;
	var first_Event={};
	  
	function startProcess() {   
		
		// initial datastores
		bookingsDatastore = new dhtmlXDataStore({datatype: "json"});
		lookupDatastore = new dhtmlXDataStore({datatype: "json"});
		guideDatastore = new dhtmlXDataStore({datatype: "json"});	

		initDatastoreController.addDatastore(bookingsDatastore,"bookings","view");
		initDatastoreController.addDatastore(lookupDatastore,"lookups","view");
		initDatastoreController.addDatastore(guideDatastore,"guides","view"); // needs to be last for filter to work	
		
		initDatastoreController.refreshDatastores();
		
        //the default scheduler format
        scheduler.config.xml_date= "%Y-%m-%d %H:%i";

        //Default hour on load set to 8:00am
        scheduler.config.scroll_hour = new Date().setTime(8,0,0,0);

        //Initializing the Scheduler [ set initial view | set initial date | Set Default View ]
        scheduler.init('scheduler_here', new Date(2015,0,10), "week");

        //Touch Support
        scheduler.config.touch = "force";
        

        //scheduler.locale.labels.today_button = "Today";

        //icons on the single click quick-info
        scheduler.config.icons_select = [
            "icon_details",
            "icon_delete"
        ];
        // called after data is loaded
        scheduler.attachEvent("onXLE", function (){
            console.log("Schedule Data is Loaded");
            
            // get all events
            eventData=getAllEvents();
            
            // jump to first data 
            scheduler.setCurrentView( eventData[0].start_date, scheduler._mode );


            window.first_Event = eventData[0].start_date;

            //sets the time format of y-axis
            scheduler.config.hour_date = "%h:%i %A";   

            //set multiday event
            scheduler.config.multi_day = true;
        
            //Set Margin
            scheduler.xy.margin_top    = 50; 
           
            if ( Object.getOwnPropertyNames(first_Event).length === 0 ) {
                //agenda view start date
                scheduler.config.agenda_start = new Date (2015,0,10);
                //map view start date
                scheduler.config.map_start = new Date (2015,0,10);
            }else{ 
                //agenda view start date
                scheduler.config.agenda_start = new Date (getDateFromEvent(first_Event));
                //map view start date           
                scheduler.config.map_start = new Date (getDateFromEvent(first_Event));  
            }
            //no of months in a row
            scheduler.config.year_x = 6; //3 months in a row
            
            //no of months in a column
            scheduler.config.year_y = 2; //3 months in a column
        });
                //Naming the Tabs 
        scheduler.locale.labels.agenda_tab="Agenda"; 
        scheduler.locale.labels.map_tab ="Map";
        scheduler.locale.labels.day_tab ="Day";
        scheduler.locale.labels.month_tab = "Month";
        scheduler.locale.labels.week_tab = "Week";
        scheduler.locale.labels.year_tab = "Year";
        
        //Redirecting it to map view on clicking loaction button
		scheduler._click.buttons.location = function(id){
            scheduler.setCurrentView(new Date(first_Event), "map");
        };
		// text to display on the scheduler itself
        scheduler.templates.event_text = function(start,end,event){
            return '<div>' +
						'<p>'+event.text+'</p>'+
                        '<div>' +
                            '<p>'+ getLookupLabel(event.event_type)/*getSchedulerOptions("event_type")[indexOf(event.event_type)].label*/ +'</p>'+
                        '</div>' +  
                        '<div>' +
                            '<p>'+getLookupLabel(event.guide_id) +'</p>'+
                        '</div>' +
						'<div>' +
					 		'<img src="'+event.image +'" alt="" height="auto" width="100%">'+
						"</div>" +	
				"</div>";		
        };
	
        // enable parameter to get full day event option on the lightbox form
        scheduler.config.full_day = true; 	

        //Details on Double Click
        scheduler.config.details_on_dblclick = true;
        scheduler.config.details_on_create = true;

         //seperate short events
        scheduler.config.separate_short_events = true;

        // loading data from CORVUS serivces but need to wait until complete then call onXLE
        loadAjaxController(scheduler,
            false,
            "itineraries",
            "scheduler",
            {booking_number:pageRequestParmeters.booking_number},
            "edit",
            true
        )

        //agenda events start date display
       scheduler.templates.agenda_date = function(start, end) {
            start= first_Event;
            return start;
        }

        //CTRL+ C/Copy Event
        scheduler.attachEvent("onEventCopied", function(ev) {
                //alert(typeof scheduler.config.agenda_start);
                modified_event_id = ev.id;
                scheduler.updateEvent(ev.id);
            });

        //CTRL+ X/Cut Event
        scheduler.attachEvent("onEventCut", function(ev) {
                modified_event_id = ev.id;
                scheduler.updateEvent(ev.id);
            });

        //CTRL + V / Paste event
        scheduler.attachEvent("onEventPasted", function(isCopy, modified_ev, original_ev) {
            modified_event_id = null;
            scheduler.updateEvent(modified_ev.id);

            var evs = scheduler.getEvents(modified_ev.start_date, modified_ev.end_date);
            if (evs.length > 1) {
                dhtmlx.modalbox({
                    text: "There is another event at this time! What do you want to do?",
                    width: "500px",
                    position: "middle",
                    buttons:["Revert changes", "Edit event", "Save changes"],
                    callback: function(index) {
                        switch(+index) {
                            case 0:
                                if (isCopy) {
                                    // copy operation, need to delete new event
                                    scheduler.deleteEvent(modified_ev.id);
                                } else {
                                    // cut operation, need to restore dates
                                    modified_ev.start_date = original_ev.start_date;
                                    modified_ev.end_date = original_ev.end_date;
                                    scheduler.setCurrentView();
                                }
                                break;
                            case 1:
                                scheduler.showLightbox(modified_ev.id);
                                break;
                            case 2:
                                return;
                        }
                    }
                });
            }
        });
        
        //Map View start date set
        scheduler.templates.map_date = function(start, end) {
            start= first_Event; 
            scheduler.config.quick_info_detached = true;
            return start;
        }

        //Year view display events on scroll
        scheduler.templates.year_tooltip = function(start,end,ev){
            return ev.text;
        };

        //applying css to different types of event
        scheduler.templates.event_class = function (start, end, event) {
            if (event.event_type == '1797') 
                return "Guide_event";
            else if(event.event_type == '1798')
                return "Logistic_event"; 
            else if(event.event_type == '1799')
                return "Staffing_event";
        };
        /**************************************************************************LightBox Configuration*************************************************************************/

        scheduler.locale.labels.section_eventName = 'Event Name';
        scheduler.locale.labels.section_description = 'Description';
        scheduler.locale.labels.section_image = 'Image';
        scheduler.locale.labels.section_priority = 'Priority';
        scheduler.locale.labels.section_recurring = 'Recurring';
        scheduler.locale.labels.section_time = 'Time';
        scheduler.locale.labels.section_latitude = "Latitude";
        scheduler.locale.labels.section_longitude ="Longitude";

        //Set Priority Options
		priorities=[
	            { key: "1", label: 'High' },
	            { key: "2", label: 'Medium' },
	            { key: "3", label: 'Low' }
	        ];
        
        //LightBox Sections    
        scheduler.locale.labels.section_eventType = "Event Type";
        scheduler.locale.labels.section_guideSection ="Guide Section";
        scheduler.locale.labels.section_guideItem = "Guide Item";
        
		
        scheduler.config.lightbox.sections = [
            { name:"eventName",     height:25,              map_to:"text",              type:"textarea" ,         focus:true},
            { name:"description",    height:200,            map_to:"details",           type:"textarea",          focus:false},
			{ name:"image",          height:25,             map_to:"image",             type:"textarea" ,         focus:false},
            { name:"latitude",       height:25,             map_to:"lat",               type:"textarea" },
            { name:"longitude",      height:25,             map_to:"lng",               type:"textarea" },
            { name:"recurring", 	 height:115, 			map_to:"rec_type", 	        type:"recurring",         button:"recurring"},
            { name:"time", 			 height:72, 			map_to:"auto",		        type:"calendar_time"}
        ];
		// add custom handlers
		scheduler.attachEvent("onLightbox", function (id){
            
			// add handler changing fields
	        $( getSchedulerSectionElement( scheduler.locale.labels.section_guideItem ) ).change(function() {
				var guideId=scheduler.formSection('guideItem').getValue();
	            console.log("Guide Section is changed to "+guideId); 

                if (guideId !== "") {
                    var guideItem=guideDatastore.item(guideId);

                    setFormSection('description',true,guideItem.description);
					
					/*/
                    setFormSection('image',!changeGuideItemResult,value);
                    setFormSection('latitude',!changeGuideItemResult,value);
                    setFormSection('longitude',!changeGuideItemResult,value);

                    scheduler.formSection('image').setValue(guideItem.image);
                    var imageSection=scheduler.formSection('image');
                    imageSection.control.disabled = !changeGuideItemResult;  
                    var latSection=scheduler.formSection('latitude');
                    latSection.control.disabled = !changeGuideItemResult;     
                    var lngSection=scheduler.formSection('longitude');
                    lngSection.control.disabled = !changeGuideItemResult;
					*/
					
                } else (changeGuideItemResult) {

                }
	        });
		});


	
		// load options when last data finished loaded
		guideDatastore.attachEvent("onXLE", function(){
			console.log("onXLE Guide Data");
			
			// get booking_id
			booking_id=datastoreLookupForeignKey(bookingsDatastore,"",pageRequestParmeters.booking_number,"booking_number","id");
			
			scheduler.config.lightbox.sections.splice(1,0,

					{ name:"eventType",       		height:25,             map_to:"event_type",        	  type:"select",          options:getSchedulerOptions("event_type")},
                    
					{ name:"guideItem",      		height:25,             map_to:"guide_id",             type:"select",           options:getSchedulerOptions("guide_item"),       filtering:true}
			);	
         
		});
			
        //Header on LightBox
        scheduler.templates.lightbox_header = function(start, end, event){
            return event.text;
        }
        //Quick info Location button
        scheduler.locale.labels.icon_location = "Location";
        objects = scheduler.config.lightbox.sections;

        //Info Button on the Light box
        scheduler.config.buttons_right = ["dhx_custom_btn_info"];
        scheduler.locale.labels["dhx_custom_btn_info"] = "Info";
	}
	
	function getSchedulerSectionElement(sectionLabel){
		return "div.dhx_cal_lsection:contains('"+sectionLabel+"') + div :first-child";
	}
	function setFormSection(sectionName,disabled,value){
        var section=scheduler.formSection(sectionName);
        section.setValue(value);
        section.control.disabled = disabled;
    }
	
    //guideDatastore.item(guideDatastore.first())
	function getSchedulerOptions(optionsType){
		var options=[];
		var item,i;

		if (optionsType =="event_type" || optionsType =="guide_section") {
			lookupOptions = getLookupList(lookupDatastore, optionsType);
			for (var i = 0; i < lookupOptions.length; i++){
				options.push({key:lookupOptions[i].value, label:lookupOptions[i].text})
			}
		} else if (optionsType =="guide_item") {
			var booking=bookingsDatastore.item(booking_id);
			var market_type_code=booking.market_type_code;	
			
			options.push({key:"", label:""});	

			for (var i = 0; i < guideDatastore.dataCount(); i++){
				item=guideDatastore.item(guideDatastore.idByIndex(i));
				
				// filter by market code and section TODO
				if (item.markets.indexOf(market_type_code)>=0) {
					for (var x = 0; x < item.sections.length; x++){
						options.push(
							{
								key:item.id,
							 	label:item.sections[x]+" - "+item.name
						});
					}	
				}		
			}	
			
			options = _.sortBy(options, 'label');
		}
		return options;
	}
	
	//Get all the events
	function getAllEvents(){
		return scheduler.getEvents(new Date(2010,1,10),new Date(2030,2,10));  // hardcoded date range not sure why
	}

	//Showing Mini-Calender at the top
	function show_minical(){
	    if (scheduler.isCalendarVisible()){
	        scheduler.destroyCalendar();
	    } else {
	        scheduler.renderCalendar({
	            position:"dhx_minical_icon",
	            date:scheduler._date,
	            navigation:true,
	            handler:function(date,calendar){
	                scheduler.setCurrentView(date);
	                scheduler.destroyCalendar()
	            }
	        });
	    }
	}
    function getLabel(key){

        var eventTypeObjects = getSchedulerOptions("event_type");
        console.log(eventTypeObjects);
        for (var i = 0; i <= eventTypeObjects.length; i++) {
            if (eventTypeObjects[i].key === key) {
                return eventTypeObjects[i].label;
            };
        };
    }
	function getDateFromEvent(date)
	{
	    return [date.getFullYear(),date.getMonth() ,date.getDate()];
	}
</script>

<?php
require_once($backendPath."adminModuleEnd.php");
?>   