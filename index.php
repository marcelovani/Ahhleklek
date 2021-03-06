<?php

// This provides access to helper functions defined in 'utils.php'
require_once('utils.php');

require_once('image.php');

$question = '';
$answer = '';

if (isset($_REQUEST['question'])) {
	$question = clean($_REQUEST['question']);
}
if (isset($_REQUEST['answer'])) {
	$answer = clean($_REQUEST['answer']);
}

$params = request_path();
$args = explode('/', $params);

if (is_array($args)) {
	if ($args[0] == 'thumb') {
		$params = $args[1];
	}
}

if (!empty($params)) {
   $msgs = explode('_', $params);
   if (is_array($msgs)) {
      $question = clean(isset($msgs[0])?$msgs[0]:'');
      $answer = clean(isset($msgs[1])?$msgs[1]:'');
   }
}

$clean_filename = urlencode(clean_filename($question . '_' . $answer));
$output_file = 'output/' . $clean_filename . '.jpg';

$question = sentence($question);
$answer = sentence($answer);

if ($args[0] == 'thumb') {
  output_image($question, $answer, $output_file, 'thumb');	
}

$app_name = 'Ahh Lek Lek';

/**
 * This sample app is provided to kickstart your experience using Facebook's
 * resources for developers.  This sample app provides examples of several
 * key concepts, including authentication, the Graph API, and FQL (Facebook
 * Query Language). Please visit the docs at 'developers.facebook.com/docs'
 * to learn more about the resources available to you
 */

// Provides access to app specific values such as your app id and app secret.
// Defined in 'AppInfo.php'
require_once('AppInfo.php');


// Enforce https on production
//if (substr(AppInfo::getUrl(), 0, 8) != 'https://' && $_SERVER['REMOTE_ADDR'] != '127.0.0.1') {
//  header('Location: https://'. $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
//  exit();
//}


/*****************************************************************************
 *
 * The content below provides examples of how to fetch Facebook data using the
 * Graph API and FQL.  It uses the helper functions defined in 'utils.php' to
 * do so.  You should change this section so that it prepares all of the
 * information that you want to display to the user.
 *
 ****************************************************************************/

if (file_exists('sdk/src/facebook.php')) {
    include_once('sdk/src/facebook.php');
}

$user_id = 0;
if (class_exists('Facebook')) {
	$facebook = new Facebook(array(
	  'appId'  => AppInfo::appID(),
	  'secret' => AppInfo::appSecret(),
	  'sharedSession' => true,
	  'trustForwarded' => true,
	));
	 
	$user_id = $facebook->getUser();
}

$basic = array();
if ($user_id) {
  try {
    // Fetch the viewer's basic information
    $basic = $facebook->api('/me');
  } catch (FacebookApiException $e) {
    // If the call fails we check if we still have a user. The user will be
    // cleared if the error is because of an invalid accesstoken
    if (!$facebook->getUser()) {
      header('Location: '. AppInfo::getUrl($_SERVER['REQUEST_URI']));
      exit();
    }
  }

  // This fetches some things that you like . 'limit=*" only returns * values.
  // To see the format of the data you are retrieving, use the "Graph API
  // Explorer" which is at https://developers.facebook.com/tools/explorer/
  $likes = idx($facebook->api('/me/likes?limit=4'), 'data', array());

  // This fetches 4 of your friends.
  $friends = idx($facebook->api('/me/friends?limit=4'), 'data', array());

  // And this returns 16 of your photos.
  $photos = idx($facebook->api('/me/photos?limit=16'), 'data', array());

  // Here is an example of a FQL call that fetches all of your friends that are
  // using this app
  $app_using_friends = $facebook->api(array(
    'method' => 'fql.query',
    'query' => 'SELECT uid, name FROM user WHERE uid IN(SELECT uid2 FROM friend WHERE uid1 = me()) AND is_app_user = 1'
  ));
}

if (class_exists('Facebook')) {
	// Fetch the basic info of the app that they are using
	$app_info = $facebook->api('/'. AppInfo::appID());
	
	$app_name = idx($app_info, 'name', '');
}

if (empty($question)) {
	$question = "Where are you going?";
}
$who = he(idx($basic, 'name'));
if (empty($who)) {
	$who = 'you';
}
if (empty($answer)) {
	$answer = "Going to visit " . $who . "!";
}

?>
<!DOCTYPE html>
<html xmlns:fb="http://ogp.me/ns/fb#" lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=2.0, user-scalable=yes" />

    <title><?php echo he($app_name); ?></title>
    <link rel="stylesheet" href="stylesheets/screen.css" media="Screen" type="text/css" />
    <link rel="stylesheet" href="stylesheets/mobile.css" media="handheld, only screen and (max-width: 480px), only screen and (max-device-width: 480px)" type="text/css" />

    <!--[if IEMobile]>
    <link rel="stylesheet" href="mobile.css" media="screen" type="text/css"  />
    <![endif]-->

    <!-- These are Open Graph tags.  They add meta data to your  -->
    <!-- site that facebook uses when your content is shared     -->
    <!-- over facebook.  You should fill these tags in with      -->
    <!-- your data.  To learn more about Open Graph, visit       -->
    <!-- 'https://developers.facebook.com/docs/opengraph/'       -->
    <meta property="og:title" content="<?php echo $question; ?>" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="<?php echo AppInfo::getUrl('/' . $clean_filename); ?>" />
    <meta property="og:image" content="<?php echo AppInfo::getUrl('/thumb/' . $clean_filename); ?>" />
    <meta property="og:site_name" content="ahhleklek" />
    <meta property="og:description" content="<?php echo $answer; ?>" />
    <?php if (class_exists('Facebook')) { ?>
      <meta property="fb:app_id" content="<?php echo AppInfo::appID(); ?>" />
    <?php } ?>
    
    <script type="text/javascript" src="/javascript/jquery-1.7.1.min.js"></script>
    <script type="text/javascript" src="/javascript/app.js"></script>
	<script type="text/javascript" src="http://static.ak.fbcdn.net/connect.php/js/FB.Share"></script>

    <script type="text/javascript">
      function logResponse(response) {
        if (console && console.log) {
          console.log('The response was', response);
        }
      }

      $(function(){
        // Set up so we handle click on the buttons
        $('#postToWall').click(function() {
          FB.ui(
            {
              method : 'feed',
              link   : $(this).attr('data-url')
            },
            function (response) {
              // If response is null the user canceled the dialog
              if (response != null) {
                logResponse(response);
              }
            }
          );
        });

        $('#sendToFriends').click(function() {
          FB.ui(
            {
              method : 'send',
              link   : $(this).attr('data-url')
            },
            function (response) {
              // If response is null the user canceled the dialog
              if (response != null) {
                logResponse(response);
              }
            }
          );
        });

        $('#sendRequest').click(function() {
          FB.ui(
            {
              method  : 'apprequests',
              message : $(this).attr('data-message')
            },
            function (response) {
              // If response is null the user canceled the dialog
              if (response != null) {
                logResponse(response);
              }
            }
          );
        });
      });
    </script>

    <!--[if IE]>
      <script type="text/javascript">
        var tags = ['header', 'section'];
        while(tags.length)
          document.createElement(tags.pop());
      </script>
    <![endif]-->
  </head>
  <body>
    <div id="fb-root"></div>
    <script type="text/javascript">
      window.fbAsyncInit = function() {
        FB.init({
          appId      : '<?php echo AppInfo::appID(); ?>', // App ID
          channelUrl : '//<?php echo $_SERVER["HTTP_HOST"]; ?>/channel.html', // Channel File
          status     : true, // check login status
          cookie     : true, // enable cookies to allow the server to access the session
          xfbml      : true // parse XFBML
        });

        // Listen to the auth.login which will be called when the user logs in
        // using the Login button
        FB.Event.subscribe('auth.login', function(response) {
          // We want to reload the page now so PHP can read the cookie that the
          // Javascript SDK sat. But we don't want to use
          // window.location.reload() because if this is in a canvas there was a
          // post made to this page and a reload will trigger a message to the
          // user asking if they want to send data again.
          window.location = window.location;
        });

        FB.Canvas.setAutoGrow();
      };

      // Load the SDK Asynchronously
      (function(d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s); js.id = id;
        js.src = "//connect.facebook.net/en_US/all.js";
        fjs.parentNode.insertBefore(js, fjs);
      }(document, 'script', 'facebook-jssdk'));

    </script>

    <header class="clearfix">
      <?php if (isset($basic)) { ?>
      
      <div>
        <h1><?php echo he($app_name); ?><strong></strong></h1>
        <p class="tagline">
          Customize your messages
        </p>

      </div>
      <?php } else { ?>
      <div>
        <h1><?php echo he($app_name); ?></h1>
        <div class="fb-login-button" data-scope="user_likes,user_photos"></div>
      </div>
      <?php } ?>
    </header>

    <section id="main">
      <form class="form" action="<?php echo AppInfo::getUrl(); ?>" method="GET">
        <p>Question: <input type="text" class="question" name="question" size="30" maxlength="30" value="<?php echo $question; ?>"></p>
        <p>Answer: <input type="text" class="answer" name="answer" size="30" maxlength="50" value="<?php echo $answer; ?>"></p>
        <input class="button preview" type="submit" value="Preview">
      </form>
      
      <div id="preview">
        <div class="question"><?php echo $question; ?></div>
        <div class="answer"><?php echo $answer; ?></div>
        <div class="censored"></div>
      </div>
    </section>
    
    <section id="share">
       <div id="share-app">
          <p>Send to your friends:</p>
          <ul>
            <li>
				<div><a name="fb_share" type="button" share_url="<?php echo AppInfo::getUrl('/' . $clean_filename); ?>"></a></div>
	        </li>
            <li>
            	<div class="fb-login-button" data-show-faces="true" data-width="200" data-max-rows="1"></div>
            </li>
            <li>
              <div class="fb-follow" data-href="<?php echo AppInfo::getUrl(); ?>" data-layout="box_count" data-show-faces="true" data-width="450"></div>
          
            </li>
          </ul>
        </div>
    </section>

    <?php
      if ($user_id) {
    ?>

    <section id="samples" class="clearfix">
      <div class="list">
        <h3>Friends using this app</h3>
        <ul class="friends">
          <?php
            foreach ($app_using_friends as $auf) {
              // Extract the pieces of info we need from the requests above
              $id = idx($auf, 'uid');
              $name = idx($auf, 'name');
          ?>
          <li>
            <a href="http://www.facebook.com/<?php echo he($id); ?>" target="_top">
              <img src="http://graph.facebook.com/<?php echo he($id) ?>/picture?type=square" alt="<?php echo he($name); ?>">
              <?php echo he($name); ?>
            </a>
          </li>
          <?php
            }
          ?>
        </ul>
      </div>
    </section>

    <?php
      }
    ?>
  </body>
</html>

