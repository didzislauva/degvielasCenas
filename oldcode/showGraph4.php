<html>
<head>
	
	<title>Charts with Fill Between Lines</title>

    <link class="include" rel="stylesheet" type="text/css" href="javacharts/jquery.jqplot.min.css" />
   
   
  <!--[if lt IE 9]><script language="javascript" type="text/javascript" src="../excanvas.js"></script><![endif]-->
    <script class="include" type="text/javascript" src="javacharts/jquery.min.js"></script>
	
	    <script class="code" type="text/javascript" language="javascript">
				$(document).ready(function(){
					
					var radioValue=$("input[name='type']").attr("value"); //type of fuel. by default - E95
					
					 $("button[name=changeFill]").click(function () {	//Opening div with populated radio and checkboxes
						$("div[name=stationList]").show("fast");
					 }); 
					 
					  
					  $("input[name='type']").change(radioValueChanged);  //launching function when radio is changed
					  
					  function radioValueChanged()						//exact function which does something... sets a chosen value
						{
							radioValue = $(this).val();
							
						};//ef
						
					  $("input[name='adrese']").change(addressCheck);
					  
					  function addressCheck()
						{
							if ($(this).attr('checked'))
							{
								alert("add to array and redraw series")
							}
							else
							{
								alert("remove from array and redraw series");
							};
						};//ef
					  
					  $("button[name=radiovalues]").click(function() {
						alert(radioValue);								
						});//ef
			
					  
				 }); 	
	    </script>
	
</head>
<body>

<button name="changeFill">Change Fill</button>
<div name=stationList hidden=true>
	<input type="radio" name="type" value="E95" checked=true/> E95
	<input type="radio" name="type" value="E98" /> E98 
	<input type="radio" name="type" value="Petrol" /> Petrol
	<input type="radio" name="type" value="Gas" /> Gas

	<div name=inner>
		<input type="checkbox" name="adrese" value=1 /> Adrese 1
		<input type="checkbox" name="adrese" value=2 /> Adrese 2 
		<input type="checkbox" name="adrese" value=3 /> Adrese 3
		<input type="checkbox" name="adrese" value=4 /> Adrese 4
		<input type="checkbox" name="adrese" value=5 /> Adrese 5
		<input type="checkbox" name="adrese" value=6 /> Adrese 6
	</div>

</div>
<button name="radiovalues">radiovalue</button>
</body>