<?php
session_start();
require_once 'lib/google-api-php-client/src/Google_Client.php';

$client = new Google_Client();
$client -> setApplicationName('GoogleContactBook');
$client -> setScopes(array("http://www.google.com/m8/feeds/","https://www.googleapis.com/auth/userinfo.profile","https://www.googleapis.com/auth/userinfo.email"));
// Documentation: http://code.google.com/apis/gdata/docs/2.0/basics.html
// Visit https://code.google.com/apis/console?api=contacts to generate your
// oauth2_client_id, oauth2_client_secret, and register your oauth2_redirect_uri.
// $client->setClientId('insert_your_oauth2_client_id');
// $client->setClientSecret('insert_your_oauth2_client_secret');
// $client->setRedirectUri('insert_your_redirect_uri');
// $client->setDeveloperKey('insert_your_developer_key');

if (isset($_GET['code'])) {
	$client -> authenticate();
	$_SESSION['token'] = $client -> getAccessToken();
	$redirect = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
	header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
}

if (isset($_SESSION['token'])) {
	$client -> setAccessToken($_SESSION['token']);
}

if (isset($_REQUEST['logout'])) {
	unset($_SESSION['token']);
	$client -> revokeToken();
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
		print "<a class=login style='background-color:#E32B1D;color:#FFFFFF;padding: 10px;' href='$auth'>Connect Using Gmail</a>";
		} else {
		print "<a class=logout style='background-color:#E32B1D;color:#FFFFFF;padding: 10px;' href='?logout'>Disconnect Gmail</a>";
		}
		if ($client -> getAccessToken()) {
		$req = new Google_HttpRequest("https://www.googleapis.com/oauth2/v1/userinfo?access_token=".$client -> getAccessToken());
		$val = $client -> getIo() -> authenticatedRequest($req);
		$response = $val -> getResponseBody();
		$profile = json_decode($response, true);
		
		$max_results = 25;
		$req = new Google_HttpRequest("https://www.google.com/m8/feeds/contacts/default/full?max-results=" . $max_results . "&alt=json");
		$val = $client -> getIo() -> authenticatedRequest($req);
		
		// The contacts api only returns XML responses.
		//$response = json_encode(simplexml_load_string($val -> getResponseBody()));
		$response = $val -> getResponseBody();
		
		$response_as_array = json_decode($response, true);
		
		$feed = $response_as_array['feed'];
		$entries = $feed['entry'];
		
		$id = $feed['id'];
		$author = $feed['author'];

		foreach ($author as $obj) {
			$name = $obj['name']['$t'];
			$email = $obj['email']['$t'];
		}	

		?>

		<div class="row">
			<div class="large-3 panel columns">
				<img height="500px" width="500px" src="<?php echo $profile["picture"]; ?>">
				<div class="panel" style="text-align: center">
					<h6><?php echo $profile["name"]; ?></h6>
				</div>
				<hr>
				<div class="row">
					<div class="button-bar">
						<ul class="button-group radius" style="width: 100%;">
							<li style="width: 100%;">
								<a href="<?php echo $profile["link"]; ?>" style="font-size: 13px;width: 100%" id="logout" class="button tiny">Google +</a>
							</li>
						</ul>
					</div>
				</div>
			</div>

			<div class="large-9 columns">

				<!-- Search Bar -->
				<div class="row">
					<div class="large-12 columns">
						<div class="radius panel">
							<div class="row">
								<div class="columns">
									<input type="text" placeholder="Search followers" id="filter" />
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
		</div>
		<?php $_SESSION['token'] = $client -> getAccessToken();
		 } ?>
		<!-- Footer -->
		<footer class="row">
			<div class="large-12 columns">
				<hr />
			</div>
		</footer>

		<script src="js/jquery.js"></script>
		<script src="js/foundation.min.js"></script>
		<script src="js/script.js"></script>

	</body>
</html>