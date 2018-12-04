<?php 

	session_start();
	error_reporting(NONE);
	
	isset($_GET['utm_campaign']) ? $_SESSION['utm_ca'] = trim($_GET['utm_campaign']) : $_SESSION['utm_ca'] = '' ;
	isset($_GET['utm_content']) ? $_SESSION['utm_co'] = trim($_GET['utm_content']) : $_SESSION['utm_co'] = '' ;
	isset($_GET['utm_source']) ? $_SESSION['utm_so'] = trim($_GET['utm_source']) : $_SESSION['utm_so'] = '' ;
	isset($_GET['utm_medium']) ? $_SESSION['utm_me'] = trim($_GET['utm_medium']) : $_SESSION['utm_me'] = '' ;
	isset($_GET['utm_term']) ? $_SESSION['utm_te'] = trim($_GET['utm_term']) : $_SESSION['utm_te'] = '' ;
	isset($_GET['utm_keyword']) ? $_SESSION['utm_kw'] = trim($_GET['utm_keyword']) : $_SESSION['utm_kw'] = '' ;
	isset($_GET['utm_adgroup']) ? $_SESSION['utm_ag'] = trim($_GET['utm_adgroup']) : $_SESSION['utm_ag'] = '' ;
	
	$full_nameErr = $phoneErr = $emailErr = "";
	$full_name = $phone = $email = "";

	if ($_SERVER["REQUEST_METHOD"] == "POST") {

		if (empty($_POST["full_name"])) {

			$full_nameErr = "Name is required";

		} else {

			$full_name = test_input($_POST["full_name"]);

			if (!preg_match("/^[a-zA-Z ]*$/",$full_name)) {

				$full_nameErr = "Only letters and white space allowed";
			}
			
			else {
			
				$parts 		  = explode(" ", $full_name);
				$lastname 	  = array_pop($parts);
				$firstname 	  = implode(" ", $parts);
				$full_nameErr = '';
				
			}
		}

		if (empty($_POST["phone"])) {
		
			$phoneErr = "Phone is required";
		
		} else {
		
			$phone = test_input($_POST["phone"]);
			
			if (!preg_match("/\A[(]?[0-9]{3}[)]?[ ,-]?[0-9]{3}[ ,-]?[0-9]{4}\z/",$phone)) {

				$phoneErr = "Invalid phone number"; 
			}
			
			else {
			
				$phoneErr = '';
				
			}
		
		}
	
		if (empty($_POST["email"])) {
		
			$emailErr = "Email is required";
		
		} else {
		
			$email = test_input($_POST["email"]);
		
			if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		
				$emailErr = "Invalid email format"; 
			}
			
			else {
			
				$emailErr = '';
				
			}
		
		}
		
		if(empty($full_nameErr) && empty($phoneErr) && empty($emailErr)) {
		
		##############################  Post to NCH DB  ###############################
				
		$curl_post_arr = array
			(
				'fname' 		=> $firstname,
				'lname' 		=> $lastname,
				'email' 		=> $email,
				'phone' 		=> $phone,
				'phone2' 		=> '',
				'biz_name' 		=> '',
				'comments' 		=> '',
				'ipr' 			=> getRealIpAddr(),
				'formwebpage' 	=> 'https://nchinc.com/free/nevada-edge/download',
				'utm_ca' 		=> $_SESSION['utm_ca'],
				'utm_co' 		=> $_SESSION['utm_co'],
				'utm_so' 		=> $_SESSION['utm_so'],
				'utm_me' 		=> $_SESSION['utm_me'],
				'utm_te' 		=> $_SESSION['utm_te'],
				'utm_kw' 		=> $_SESSION['utm_kw'],
				'utm_ag' 		=> $_SESSION['utm_ag'],
				'leadsource' 	=> 'www - Nevada Edge - 2018 Download',
			);
			
		$fields = "";
		foreach( $curl_post_arr as $key => $value ) $fields .= "$key=" . urlencode( $value ) . "&";
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,"https://secure.nchinc.com/_landing/_post/googleads.php");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, rtrim( $fields, "& " ));
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$resp = curl_exec ($ch);
		curl_close ($ch);				
		
		##############################  end: Post to NCH CRM  ###########################
		
		// auto-responder
		
		require '../_includes/src/Mandrill.php'; 	
		
		try {
		
		    $mandrill = new Mandrill('4Y0-WoQFNIIZw1NqxQYvfw');
		    $template_name = 'autoresponder-lead';
		    $message = array(
		        'subject' => 'NCH - Incorporate in Nevada Overview for '.$firstname.'',
		        'from_email' => 'incorporate@nchinc.com',
		        'from_name' => 'Nevada Corporate Headquarters Inc',
		        'to' => array(
		            array('email' => $email,'name' => $firstname.' '.$lastname,'type' => 'to')
		        ),
		        'headers' => array('Reply-To' => 'info@nchinc.com'),
		        'important' => false,
		        'track_opens' => true,
		        'track_clicks' => true,
		        'auto_text' => true,
		        'preserve_recipients' => true,
		        'view_content_link' => true,
		        'merge_language' => 'mailchimp',
		        'global_merge_vars' => array(
					array('name' => 'fname','content' => $firstname),
					array('name' => 'lname','content' => $lastname),
					array('name' => 'email','content' => $email)
		        ),	
		        'tags' => array('autoresponder-lead-nvedge')
		    );
		    $result = $mandrill->messages->sendTemplate($template_name, $template_content, $message, $async, $ip_pool, $send_at);
		  	} catch(Mandrill_Error $e) {
		    	echo 'A mandrill error occurred: ' . get_class($e) . ' - ' . $e->getMessage();
		    	throw $e;
		  	}
		
		$_SESSION['name'] = $firstname;
		$_SESSION['success'] = 'yes';

		header ("Location: https://nchinc.com/free/nevada-edge/success");
		
		}

	}



function test_input($data) {

	$data = trim($data);

	$data = stripslashes($data);

	$data = htmlspecialchars($data);

	return $data;

}


function getRealIpAddr(){
    
	if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
  
		$ip=$_SERVER['HTTP_CLIENT_IP'];

	}

    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
      
      $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
    
    }
    
    else {

		$ip=$_SERVER['REMOTE_ADDR'];
    
    }

	return $ip;

}


?>
<!DOCTYPE html>
<html dir="ltr" lang="en-US">

<head>
	<!-- Document Meta
    ============================================= -->
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<!--IE Compatibility Meta-->
	<meta name="author" content="zytheme"/>
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<meta name="description" content="Multi-purpose Business html5 landing page">
	<link href="assets/images/favicon/favicon.png" rel="icon">

	<!-- Fonts
    ============================================= -->
	<link href='//fonts.googleapis.com/css?family=Ubuntu:300,300i,400,400i,500,500i,700,700ii%7CMerriweather:300,300i,400,400i,700,700i,900,900i' rel='stylesheet' type='text/css'>

	<!-- Stylesheets
    ============================================= -->
	<link href="assets/css/external.css" rel="stylesheet">
	<link href="assets/css/bootstrap.min.css" rel="stylesheet">	
	<link href="assets/css/style.css" rel="stylesheet">
	<link href="assets/css/nch.css" rel="stylesheet">



	<!-- HTML5 shim, for IE6-8 support of HTML5 elements. All other JS at the end of file. -->
	<!--[if lt IE 9]>
      <script src="assets/js/html5shiv.js"></script>
      <script src="assets/js/respond.min.js"></script>
    <![endif]-->

	<!-- Document Title
    ============================================= -->
	<title>Download The Nevada Edge E Book for Free Today!</title>
	
    <meta property="og:title" content="Download The Nevada Edge E Book for Free Today!">
    <meta property="og:url" content="https://nchinc.com/free/nevada-edge/download">
    <meta property="og:description" content="Free Incorporating Guide. Incorporate Your Business in NV. Over 100,000 Entities Formed. In Business Since 1989.">
    
    <!-- icons -->
	<link rel="shortcut icon" href="https://nchinc.com/img/favicon.ico?v=3">
	<link href="https://nchinc.com/img/apple-touch-icon.png" rel="apple-touch-icon" />
	<link href="https://nchinc.com/img/apple-touch-icon-76x76.png" rel="apple-touch-icon" sizes="76x76" />
	<link href="https://nchinc.com/img/apple-touch-icon-120x120.png" rel="apple-touch-icon" sizes="120x120" />
	<link href="https://nchinc.com/img/apple-touch-icon-152x152.png" rel="apple-touch-icon" sizes="152x152" />
	
	<!-- social media tags -->
	<meta property="og:type" content="website">
	<meta property="og:image" content="https://nchinc.com/img/Nevada-Corporate-Headquarters-Inc.jpg">
	
	
	<script type="text/javascript" src="//widget.trustpilot.com/bootstrap/v5/tp.widget.sync.bootstrap.min.js"></script>
	
	<?php include 'ga-global.php' ?>
	<?php include 'bing.php' ?>
	
	
</head>

<body class="body-scroll">
	<!-- Document Wrapper
	============================================= -->
	<div id="wrapper" class="wrapper clearfix">
		
		<!-- Header
        ============================================= -->
		<header id="navbar-spy" class="header header-1 header-transparent header-bordered header-fixed">

			<nav id="primary-menu" class="navbar navbar-fixed-top">
				<div class="container">
					<div class="navbar-header">
					
					<a class="logo" href="index.html">
						<img class="logo-dark logo-header" src="assets/images/logo/logo-dark.png" alt="appy Logo">
						<img class="logo-light logo-header" src="assets/images/logo/logo-light.png" alt="appy Logo">
					</a>
					
					
					
					
					<div class="pull-right tplogos">
						<img class="logo-dark tp-dark" src="assets/images/logo/tp-black.png" alt="appy Logo">
						<img class="logo-light tp-light" src="assets/images/logo/tp-white.png" alt="appy Logo">
					</div>

					</div>
					
					<!--/.nav-collapse -->
				</div>
			</nav>

		</header>

		<!-- Slider #1
============================================= -->
		<section id="slider" class="section slider slider-2 bg-dark pt-70">
			<div class="slide--item bg-dark">
				<div class="container">
					
					<div class="row row-content">
						<div class="col-xs-12 col-sm-5 col-md-5 col-lg-5 wow fadeInUp" data-wow-duration="1s">
							<div class="slide--holder">
								<img class="img-responsive" src="assets/images/mockup/book.png" alt="screens">
							</div>
						</div>
						<div class="col-xs-12 col-sm-7 col-md-6 col-md-offset-1 col-lg-6 col-lg-offset-1 wow fadeInUp toppart" data-wow-duration="1s">
							<div class="slide--headline">Get Your <span class="text-theme">FREE</span> Guide to Nevada Corporations</div>
							<div class="slide--bio">This is the newly released 5<sup>th</sup> Edition, authored by world famous entrepreneur Cort Christie, packed with everything you need to know about the benefits of forming your Corporation in Nevada&hellip;regardless of where you live.</div>
							<div class="slide--action">
								<form class="mb-0 form-action" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
						
									
									<div class="row">
									
									<!-- name -->
										<div class="col-sm-6 mb-20">
									
											<div class="input-group block <?php if (!empty($full_nameErr)) { echo 'having-error'; } ?>">
										
												<input type="text" class="form-control" id="full_name" name="full_name" placeholder="Your Name" value="<?php echo $full_name;?>">
											
											</div>
									  
											<label class="help-block text-danger" for="full_name"><?php echo $full_nameErr;?></label>
									  
										</div>
									
										<!-- phone -->
										<div class="col-sm-6 mb-20">
									
											<div class="input-group block <?php if (!empty($phoneErr)) { echo 'having-error'; } ?>">
												
												<input type="text" id="phone" name="phone" class="form-control" placeholder="Phone Number" value="<?php echo $phone;?>">
											
											</div>
											
											<label class="help-block text-danger" for="phone"><?php echo $phoneErr;?></label>
									
										</div>
									
									</div>
									
									<!-- email -->
									<div class="input-group <?php if (!empty($emailErr)) { echo 'having-error'; } ?>">
										
										<input type="email" class="form-control" name="email" id="email" placeholder="E-mail address" value="<?php echo $email;?>">
										
										<span class="input-group-btn">
									
											<input type="submit" class="btn btn--primary btn--rounded" value="download" name="submit">
								  
										</span>
									
									</div>
									
									<label class="help-block text-danger" for="email"><?php echo $emailErr;?></label>

								</form>
							
							</div>
						
						</div>
					
					</div>

				</div>

			</div>


		</section>
		<!-- #slider end -->
			<div class="clearfix bg-white m-0 p-0"></div>
		<!-- Feature #2
============================================= -->
		<section id="feature2" class="section feature feature-2 text-center bg-white">
			<div class="container">
				<div class="row clearfix">
					<div class="col-xs-12 col-sm-12 col-md-6 col-md-offset-3">
						<div class="heading heading-1 mb-70 text--center wow fadeInUp" data-wow-duration="1s">
							<h2 class="heading--title">Learn</h2>
							<p class="heading--desc">In this eBook, you’ll learn why Nevada registers more entities than any other of the 50 states!</p>
						</div>
					</div>
					<!-- .col-md-6 end -->
				</div>
				<!-- .row end -->
				<div class="row">
					<!-- Panel #1 -->
					<div class="col-xs-12 col-sm-4 col-md-4">
						<div class="feature-panel wow fadeInUp" data-wow-duration="1s">
							<div class="feature--icon">
								<i class="lnr">10</i>
							</div>
							<div class="feature--content">
								<h3>Why Nevada</h3>
								<p>Top ten reasons why you need a Nevada business</p>
							</div>
						</div>
						<!-- .feature-panel end -->
					</div>
					<!-- .col-md-4 end -->

					<!-- Panel #2 -->
					<div class="col-xs-12 col-sm-4 col-md-4">
						<div class="feature-panel wow fadeInUp" data-wow-duration="1s">
							<div class="feature--icon">
								<i class="lnr">6</i>
							</div>
							<div class="feature--content">
								<h3>NV Tax Tips for Start-Ups</h3>
								<p>Six Essential business start-up tax savings strategies</p>
							</div>
						</div>
						<!-- .feature-panel end -->
					</div>
					<!-- .col-md-4 end -->

					<!-- Panel #3 -->
					<div class="col-xs-12 col-sm-4 col-md-4">
						<div class="feature-panel wow fadeInUp" data-wow-duration="1s">
							<div class="feature--icon">
								<i class="lnr">12</i>
							</div>
							<div class="feature--content">
								<h3>Asset Protection in Nevada</h3>
								<p>12 Ways to protect your assets with a Nevada entity</p>
							</div>
						</div>
						<!-- .feature-panel end -->
					</div>
					<!-- .col-md-4 end -->
				</div>
				<!-- .row end -->
				
				<div class="row">
					<div class="col-xs-12 col-sm-12">
						<div class="heading heading-1 mb-0 mt-80 text--center wow fadeInUp" data-wow-duration="1s">
							<p class="lead text-theme font-20">Plus how to lawsuit proof your business with a Nevada Corporation!</p>
						</div>
					</div>
					<!-- .col-md-6 end -->
				</div>
				
				
				
				
				
			</div>
			<!-- .container end -->
		</section>
		<!-- #feature2 end -->
		<section class="pt-0 pb-0 bg-white">
			<div class="container">
				<div class="row">
					<div class="col-xs-12 col-sm-12 col-md-12">
						<hr class="feature-divider">
					</div>
				</div><!-- .row end -->
			</div><!-- .container end -->
		</section>
		<!-- Feature #2
============================================= -->
		<section id="feature2" class="section feature feature-2 feature-left bg-white">
			<div class="container">
				<div class="row">
					<div class="col-xs-12 col-sm-5 col-md-6">
						<div class="heading heading-1 mb-60 wow fadeInUp" data-wow-duration="1s">
							<h2 class="heading--title pt-20">Introducing Nevada</h2>
							<p class="heading--desc pl-0">Where more business entities, small and large, are registered than any other state.</p>
						</div>
						<div class="feature-panel mb-50 wow fadeInUp hidden-sm" data-wow-duration="1s">
							<div class="feature--icon">
								<i class="lnr lnr-book"></i>
							</div>
							<div class="feature--content">
								<h3>Almost 100 Pages</h3>
								<p>Answering all your questions about how Nevada offers the best in asset protection, even if you operate your business in another state.</p>
							</div>
						</div>
						<!-- .feature-panel end -->
						<div class="feature-panel wow fadeInUp hidden-sm" data-wow-duration="1s">
							<div class="feature--icon">
								<i class="lnr lnr-star"></i>
							</div>
							<div class="feature--content">
								<h3>5<sup>th</sup> Edition</h3>
								<p>Cort has revised <em>The Nevada Edge</em> for a fifth time to reflect todays laws, regulations and strategies!</p>
							</div>
						</div>
						<!-- .feature-panel end -->
					</div><!-- .col-md-6 end -->
					<div class="col-xs-12 col-sm-7 col-md-6 wow fadeInUp">
						<img class="img-responsive center-block" src="assets/images/mockup/ipad.jpg" alt="ipad"/>
					</div><!-- .col-md-6 end -->
					
					
					<!-- .col-md-6 end -->
					
					
				</div><!-- .row end -->
				
				<div class="row mt-40">
					<div class="col-xs-6 wow fadeInUp visible-sm">
						<div class="feature-panel mb-50 wow fadeInUp" data-wow-duration="1s">
							<div class="feature--icon">
								<i class="lnr lnr-book"></i>
							</div>
							<div class="feature--content">
								<h3>Almost 100 Pages</h3>
								<p>Answering all your questions about how Nevada offers the best in asset protection, even if you operate your business in another state.</p>

							</div>
						</div>
						
					</div>

					<div class="col-xs-6 wow fadeInUp visible-sm">
						<!-- .feature-panel end -->
						<div class="feature-panel wow fadeInUp" data-wow-duration="1s">
							<div class="feature--icon">
								<i class="lnr lnr-star"></i>
							</div>
							<div class="feature--content">
								<h3>5<sup>th</sup> Edition</h3>
								<p>Cort has revised <em>The Nevada Edge</em> for a fifth time to reflect todays laws, regulations and strategies!</p>
							</div>
						</div>
					</div>
				</div>
				
				
			</div>
			<!-- .container end -->
		</section>
		<!-- #feature2 end -->

		<!-- Banner 
============================================= -->
		<section id="banner" class="section banner pt-0 pb-0">
			<div class="container-fluid">
				<div class="row">
					<div class="col-xs-12 col-sm-6 col-md-6 pr-0 pl-0">
						<div class="col-img">
							<div class="bg-section">
								<img src="assets/images/background/bg-1.jpg" alt="Background"/>
							</div>
						</div>
					</div>
					<!-- .col-md-6 end-->
					<div class="col-xs-12 col-sm-6 col-md-6 col-content pl-100">
						<h3>About author</h3>
						<p>Cort W. Christie is the Founder and President of Nevada Corporate Headquarters, Inc. Mr. Christie is a skilled entrepreneur, executive, author, and speaker who has garnered widespread recognition for his expertise in small business start-ups and finance. A national expert on entrepreneurship, he has an extensive background in business formation, small business tax strategies and fundraising for start-ups. Mr. Christie has been called "The Entrepreneur's Entrepreneur".</p>
						<img src="assets/images/background/seen-on.png" class="img-responsive" alt="As Seen On">
					</div>
				</div>
				<!-- .row end -->
			</div>
			<!-- .container end -->
		</section>
		<!-- #banner2 end -->

		<!-- reviews
        ============================================= -->
		<section id="reviews" class="section reviews bg-white">
			<div class="container">
				<div class="row clearfix">
					<div class="col-xs-12 col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3">
						<div class="heading heading-1 mb-80 text--center wow fadeInUp" data-wow-duration="1s">
							<h2 class="heading--title">Testimonials</h2>
						</div>
					</div>
					<!-- .col-md-6 end -->
				</div>
				<!-- .row end -->

				<div class="row">
					<div class="col-xs-12 col-sm-12 col-md-12">
						<div class="carousel " data-slide="2" data-slide-res="1" data-autoplay="true" data-nav="false" data-dots="false" data-space="30" data-loop="true" data-speed="800">
							<!--  Testimonial #1  -->
							<div class="testimonial-panel wow fadeInUp" data-wow-duration="1s">
								<div class="testimonial-body">
									<div class="testimonial--body">
										<p>“You make the process very easy and simple. It takes the worry of doing something wrong out of the picture!”</p>
									</div>
									<div class="testimonial--meta">
										<div class="testimonial--author">
											<img src="assets/images/testimonials/3.png" alt="author">
											<h5>R.F.</h5>
											<p>Logandale, NV</p>
										</div>
									</div>
								</div>
							</div>

							<!--  Testimonial #2  -->
							<div class="testimonial-panel wow fadeInUp" data-wow-duration="1s">
								<div class="testimonial-body">
									<div class="testimonial--body">
										<p>“A very useful tool. Everyone who needs to incorporate would love it! It has been a part of my library for years!”</p>
									</div>
									<div class="testimonial--meta">
										<div class="testimonial--author">
											<img src="assets/images/testimonials/1.png" alt="author">
											<h5>Kelen F Taylor</h5>
											<p>Verified Purchase</p>
										</div>
									</div>
								</div>
							</div>

							<!--  Testimonial #3  -->
							<div class="testimonial-panel wow fadeInUp" data-wow-duration="1s">
								<div class="testimonial-body">
									<div class="testimonial--body">
										<p>“I have learned so much and still have much to learn. Wouldn't be able to move as quickly and confidently without them.”</p>
									</div>
									<div class="testimonial--meta">
										<div class="testimonial--author">
											<img src="assets/images/testimonials/2.png" alt="author">
											<h5>Sally</h5>
											<p>Client Review</p>
										</div>
									</div>
								</div>
							</div>

							<!--  Testimonial #4  -->
							<div class="testimonial-panel wow fadeInUp" data-wow-duration="1s">
								<div class="testimonial-body">
									<div class="testimonial--body">
										<p>“I have property in Nevada and wanted information. This delivered all the information that I needed.”</p>
									</div>
									<div class="testimonial--meta">
										<div class="testimonial--author">
											<img src="assets/images/testimonials/1.png" alt="author">
											<h5>Deni O</h5>
											<p>Verified Purchase</p>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!-- .container End -->
		</section>
	
		<section id="cta" class="section cta text-center pb-0 bg-dark">
			<div class="container">
				<div class="row">
					<div class="col-xs-12 col-sm-12 col-md-6 col-md-offset-3 mb-100 wow fadeInUp" data-wow-duration="1s">
						<h3>Your partner in business formation.</h3>
						<p>Trusted by our clients with a solid reputation for building successful businesses with excellent asset protection strategies.</p>
						<a class="btn btn--primary btn--rounded" href="#">download now</a>
					</div>
					<!-- .col-md-12 end -->
				</div>
				<!-- .row end -->
			</div>
			<!-- .container end -->
		</section>
		<!-- #cta1 end -->

		<!-- Footer #5
============================================= -->
		<footer id="footer" class="footer footer-5">
			<!-- Copyrights
	============================================= -->
			<div class="container">
				<div class="row">
					<div class="col-xs-12 col-sm-12 col-md-12 text--center">
						<div class="footer--copyright">
							<span>&copy; 2018 Nevada Corporate Headquarters, Inc.</span> 
							<a style="color: #aaa; text-decoration: underline;" href="https://nchinc.com/about-us/privacy-policy" target="_blank">privacy policy</a> | 
							<a style="color: #aaa; text-decoration: underline" href="https://nchinc.com/contact-nch" target="_blank">contact us</a>
						</div>
					</div>

				</div>
			</div>
			<!-- .container end -->
		</footer>
		</div>
		<!-- #wrapper end -->

		<!-- Footer Scripts
============================================= -->
		<script src="assets/js/jquery-2.2.4.min.js"></script>
		<script src="assets/js/plugins.js"></script>
		<script src="assets/js/functions.js"></script>
</body>
</html>