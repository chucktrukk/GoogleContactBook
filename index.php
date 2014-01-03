<?php
require_once 'lib/google-api-php-client/src/Google_Client.php';
require_once 'lib/google-api-php-client/src/contrib/Google_Oauth2Service.php';
require_once 'lib/google-api-php-client/src/contrib/Google_CalendarService.php';
session_start();

$client = new Google_Client();
$client -> setApplicationName("Google Calendar PHP Starter Application");
$client->setScopes(array('https://www.googleapis.com/auth/userinfo.email','https://www.googleapis.com/auth/userinfo.profile','http://www.google.com/m8/feeds/','https://www.googleapis.com/auth/calendar','https://www.googleapis.com/auth/calendar.readonly'));

$cal = new Google_CalendarService($client);
$plus = new Google_Oauth2Service($client);

if (isset($_GET['logout'])) {
	unset($_SESSION['token']);
}

if (isset($_GET['code'])) {
	$client -> authenticate($_GET['code']);
	$_SESSION['token'] = $client -> getAccessToken();
	header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']);
}

if (isset($_SESSION['token'])) {
	$client -> setAccessToken($_SESSION['token']);
}

if (!$client -> getAccessToken()) {
	$auth = $client -> createAuthUrl();
}
?>
<!doctype html>
<!--[if IE 9]><html class="lt-ie10" lang="en" > <![endif]-->
<html class="no-js" lang="en" data-useragent="Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.2; Trident/6.0)">
	<head>
		<title>GoogleContactBook</title>
		<link rel="stylesheet" href="css/foundation.css" />
		<link rel="stylesheet" href="css/style.css">
		<script src="js/modernizr.js"></script>
	</head>
	<body>
		<!-- Navigation -->
		<nav class="top-bar" data-topbar>
			<ul class="title-area">
				<!-- Title Area -->
				<li class="name">
					<h1 style="color: #ffffff">GoogleContactBook</h1>
				</li>
			</ul>
		</nav>
		<!-- End Top Bar -->
		<?php
		if (isset($auth)) {
			print "<div style='text-align: center'><img src='img/logo.PNG' width='700px' height='700px' />
			<div style='margin-top:50px;margin-bottom: 50px;'><a class=login style='background-color:#E32B1D;color:#FFFFFF;padding: 10px;' href='$auth'>Connect Using Gmail</a></div></div>";
		}else{
			//Fetch User Profile	
			$userinfo = $plus->userinfo->get();
			$_SESSION['token'] = $client -> getAccessToken();
			
			//Fetch Contacts information
			$max_results = 200;
			$req = new Google_HttpRequest("https://www.google.com/m8/feeds/contacts/default/full?max-results=" . $max_results . "&alt=json");
			$val = $client -> getIo() -> authenticatedRequest($req);
			$response_as_array = json_decode($val -> getResponseBody(), true);
			$feed = $response_as_array['feed'];
			$entries = $feed['entry'];
			
			$id = $feed['id'];
			$author = $feed['author'];
			
			 $totalresults = $feed['openSearch$totalResults'];
	
			foreach ($author as $obj) {
				$name = $obj['name']['$t'];
				$email = $obj['email']['$t'];
			}	
			
			//Fetch Calender data
			$events = $cal->events;
			$eventlist=$events->listEvents($email);
			$events=$eventlist['items'];
			$_SESSION['token'] = $client -> getAccessToken();
				
 		?>		
		<div class="row">
			<div class="large-3 panel columns">
				<img height="500px" width="500px" src="<?php echo $userinfo["picture"]; ?>">
				<div class="panel" style="text-align: center">
					<h6><?php echo $userinfo["name"]; ?></h6>
				</div>
				<hr>
				<div class="row">
					<div class="button-bar">
						<ul class="button-group radius" style="width: 100%;">
							<li style="width: 100%;text-align: center;margin: 5px;">
								<h4>Total Contacts: <?php echo $totalresults['$t']; ?></h4> 
							</li>
							<li style="width: 100%;">
								<a href="<?php echo $userinfo["link"]; ?>" style="width: 100%" id="logout" class="button">Google +</a>
							</li>
							<li style="width: 100%;">
								<a class='button logout' style='background-color:#E32B1D;color:#FFFFFF;padding: 10px;width: 100%;' href='?logout'>Disconnect Gmail</a>
							</li>
						</ul>
					</div>
				</div>
			</div>

			<div class="large-9 columns">
				<dl class="tabs" data-tab>
				  <dd class="active"><a href="#ContactsList">Contacts</a></dd>
				  <dd><a href="#EventList">Event</a></dd>
				</dl>
				<div class="tabs-content">
				  <div class="content active" id="ContactsList">
				    	<!-- Search Bar -->
						<div class="row">
							<div class="large-12 columns">
								<div class="radius panel">
									<div class="row">
										<div class="columns">
											<input type="text" placeholder="Search contacts" id="filter" />
										</div>
									</div>
								</div>
							</div>
						</div>
						<!-- End Search Bar -->
		
						<!-- Thumbnails -->
						<div class="row" style="height: 450px; width:100%; overflow-y:scroll; margin-bottom: 10px;">
							
							
							<ul class="tweet-user-list" >
								<?php
								foreach ($entries as $entry) {
									print "<li>
									<div class='large-12 columns'>
									<div class='panel radius'>
									<div class='row'>
									<div class='large-4 columns'>
									<h4>".$entry['title']['$t'] ."</h4>
									<hr>
									</div>
									<div class='large-8 columns' style='text-align: left'>";
									if (isset($entry['gd$email'])) {
										$emailentry = $entry['gd$email'];
										foreach ($emailentry as $emailobj) {
											print "<div class='row'>
										<div class='large-3 columns'>
										Email:
										</div>
										<div class='large-9 columns'>".$emailobj['address']."</div>
										</div>";
										}
									}
									if (isset($entry['gd$phoneNumber'])) {
										$phonenumber = $entry['gd$phoneNumber'];
										foreach ($phonenumber as $obj) {
											print "<div class='row'>
									<div class='large-3 columns'>
									PhoneNo:
									</div>
									<div class='large-9 columns'>".$obj['$t']."</div>
									</div>";
										}
									}
									print "</div>
									</div>
									</div>
									</div>
									</li>";
								}
								?>
							</ul>
						</div>
						<!-- End Thumbnails -->	
				  </div>
				  <div class="content" id="EventList">
				    <!-- Thumbnails -->
						<div class="row" style="height: 500px; width:100%; overflow-y:scroll; margin-bottom: 10px;">
							
							<ul class="tweet-user-list" >
								<?php
								foreach ($events as $entry) {
									print "<li>
									<div class='large-12 columns'>
									<div class='panel radius'>
									<div class='row'>
									<div class='large-6 columns'>
									<h4>".$entry['summary'] ."</h4>
									<hr>
									<h6>Creator: ".$entry['creator']['displayName'] ."</h6>
									<h6>Organizer: ".$entry['organizer']['displayName'] ."</h6>
									<h6>Start : ".$entry['start']['date'] ."</h6>
									<h6>End: ".$entry['end']['date'] ."</h6>
									</div>
									<div class='large-6 columns'><h4>Attendees List</h4><hr><ul>";
									if (isset($entry['attendees'])) {
										$attendeesentry = $entry['attendees'];
										foreach ($attendeesentry as $attendobj) {
											print "<li style='text-align: left;padding: 5px;margin: 5px;'>
										".$attendobj['displayName']."
										</li>";
										}
									}
									print "</ul></div>
									</div>
									</div>
									</div>
									</li>";
								}
								?>
							</ul>
							
						</div>
						<!-- End Thumbnails -->	
				  </div>
				  <div class="content" id="panel2-3">
				    <p>Third panel content goes here...</p>
				  </div>
				  <div class="content" id="panel2-4">
				    <p>Fourth panel content goes here...</p>
				  </div>
				</div>

				
			</div>
		</div>
		<?php
			}
		?>
		<!-- Footer -->
		<footer class="row">
			<div class="large-12 columns">
				<hr />
			</div>
		</footer>

		<script src="js/jquery.js"></script>
		<script src="js/foundation.min.js"></script>
		<script src="js/foundation/foundation.tab.js"></script>
		<script src="js/script.js"></script>
		<script>
			$(document).foundation();
			var doc = document.documentElement;
			doc.setAttribute('data-useragent', navigator.userAgent);
		</script>
	</body>
</html>