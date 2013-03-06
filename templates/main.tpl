<!DOCTYPE HTML>
<html lang="en-US">
<head>
	<meta charset="UTF-8">
	<title>[+output.moduleName+]</title>
	[+output.css&js+]
</head>
<body>
	<h1>[+output.moduleName+] Module</h1>
	
	<div id="actions">
		  <ul class="actionButtons">
			  <li id="Button1">
				<a href="#" class="js-button-preview"><img src="media/style/[+output.managerTheme+]/images/icons/save.png"> Preview</a>
			  </li>
			  [+output.deleteButton+]
		  </ul>
	</div>
	
	<div class="sectionHeader">Setup [+output.campaignName+]</div>
	
	<div class="sectionBody">
		[+message.error+]
	    [+message.success+]
	    
	    <form action="[+do.preview+]" method="post" class="js-form-config">
		    <p>Select a list you want to send the newsletter to</p>
		    [+output.lists+]
		    <br/>
		    <div class="split"></div>
		    <br/>
		    
		    <div class="groups">
			    <p>Select one or more interest groups</p>
			    [+output.groups+]
			    <br/>
			    <div class="split"></div>
			    <br/>
		    </div>


		    <p>Select the document you want to send</p>
		    [+output.documents+]
		    <br/>
		    <div class="split"></div>
		    <br/>
		    <p>Select the type of newsletter you want to send</p>
		    
		    [+output.types+]
		    
	    </form>
	</div>
</body>
</html>