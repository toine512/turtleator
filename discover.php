<?php
$time_start = microtime(true);

$BASE_URL = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] && $_SERVER['HTTPS'] != 'off') ? 'https' : 'http') . '://' . $_SERVER['SERVER_NAME'] . '/';
function domainCat($lnk, $display = false)
{
	global $BASE_URL;
	return $display ? htmlspecialchars($BASE_URL . $lnk) : $BASE_URL . rawurlencode($lnk);
}
function youtubeCat($id, $display = false)
{
	return $display ? htmlspecialchars('http://youtu.be/' . $id) : 'http://youtu.be/' . rawurlencode($id);
}

/*** Initializing cache engine ***/
require_once('php-cache512/cache.php');
$cache = new Cache512('file');
$cache_msgs = array();

/*** Load bookmarks ***/
//Cache read
if($cache->fetch('bookmarks-array'))
{
	$cache_msgs[] = 'Reading bookmarks from cache.';
	$bookmarks = $cache->data;
}
else
{
	$cache_msgs[] = $cache->last_error();
	//Downloading bookmarks
	$bookmarks = json_decode(file_get_contents('http://raw.githubusercontent.com/toine512/turtleator/master/bookmarks.json'), true);
	if($bookmarks === null) {
		exit('Failed downloading bookmarks from GitHub !  (http://raw.githubusercontent.com/toine512/turtleator/master/bookmarks.json)');
	}
	else {
		//Cache write
		$cache->data = $bookmarks;
		if(!$cache->store('bookmarks-array', 3600)) {
			$cache_msgs[] = $cache->last_error();
			echo 'Cache error: ' . $cache->last_error();
		}
	}
}

//Transform $bookmarks structure in order to handle aliases and create the checklist of video ids for online check
$availability_check_list = array();
foreach($bookmarks as $key => $line)
{
	//Alias handling
	if(array_key_exists('alias', $line)) {
		//Change structure: extract aliases and put them as an "attribute" of the main bookmark
		$bookmarks[$line['alias']]['aliases'][] = $key;
		unset($bookmarks[$key]);
	}
	
	//YouTube checklist init
	//just YTvid -> false
	else {
		$availability_check_list[$line['id']] = false;
	}
}

/*** Youtube availability checking ***/
$must_revalidate = true;
$availability_check_success = false;
//Cache read
if($cache->fetch('bookmarks-checked'))
{
	$cache_msgs[] = 'Reading YouTube data from cache.';
	//Bookmark list may have changed while the cache file is still valid
	if(empty(array_diff_key($availability_check_list, $cache->data)))
	{
		$availability_check_list = $cache->data;
		$must_revalidate = false;
		$availability_check_success = true;
	}
}
else {
	$cache_msgs[] = $cache->last_error();
}

if($must_revalidate)
{
	$cache_msgs[] = 'Must revalidate YouTube data.';
	require('_conf/yt_api_key.php'); //Loads YouTube API server private key

	$availability_check_success = true; //Set to false upon any critical error
	foreach(array_chunk(array_keys($availability_check_list), 50, true) as $chunk) //API limits videos.list query length to 50 items
	{
		//YouTube API v3 call
		$res = file_get_contents('https://www.googleapis.com/youtube/v3/videos?part=status%2CcontentDetails&id=' . implode('%2C', $chunk) . '&fields=items(id%2CcontentDetails(regionRestriction)%2Cstatus(uploadStatus))&key=' . $YT_API_KEY);
		//Fail
		if($res === false) {
			$availability_check_success = false;
			break;
		}
		//Success
		else {
			//Decode JSON response
			$json = json_decode($res, true);
			//Fail
			if($json === null) {
				$availability_check_success = false;
				break;
			}
			//Success
			else {
				if(isset($json['items'])) {
					//Availability status parsing
					$list = array();
					foreach($json['items'] as $item) {
						if($item['status']['uploadStatus'] == 'processed') {
							$list[$item['id']] = (isset($item['contentDetails']['regionRestriction']['blocked']) ? $item['contentDetails']['regionRestriction']['blocked'] : true);
						}
					}
					//Merge results into the checklist
					if(!empty($list)) {
						$availability_check_list = array_merge($availability_check_list, $list);
						
						//Cache write
						$cache->data = $availability_check_list;
						if(!$cache->store('bookmarks-checked', 86400)) {
							$cache_msgs[] = $cache->last_error();
							echo 'Cache error: ' . $cache->last_error();
						}
					}
				}
				//Something went wrong with the request/answer
				else {
					$availability_check_success = false;
					break;
				}
			}	
		}
	}
}
?>
<!DOCTYPE html>

<html lang="en">
<head>
	<meta charset="UTF-8">
	<!--[if IE]><meta http-equiv="X-UA-Compatible" content="IE=edge"><![endif]-->
	<title>toine512 bookmark list</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="icon" href="_img/mineturtle.png" sizes="32x32" type="image/png" />
	<!--[if lt IE 9]>
	<script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
	<link href="http://fonts.googleapis.com/css?family=Clicker+Script|Titillium+Web:600" rel="stylesheet">
	<link rel="stylesheet" href="_css/styles.css" media="all" />
</head>
<body>
	<script type="text/javascript" src="_js/ZeroClipboard.min.js"></script>
		<h1>Turtleator</h1>
	<section>
		<!--<h2>(**with awesome fullpage crap!)</h2>-->
		<h3>Play any YouTube video fullpage, without controls.</h3>
		<p>http://<b>turtle</b>.toine512.fr/<b>&lt;video_id&gt;</b></p>
		<p>http://<b>v</b>.toine512.fr/<b>&lt;video_id&gt;</b></p>
		<p>See below for manual looping control and controls re-enabling.</p>
	</section>
	<section>
		<h3>List of bookmarks</h3>
<?php if(!$availability_check_success) : ?>
		<p>Availability checking of YouTube videos failed!</p>
<?php endif; ?>
		<div id="gallery">
<?php
foreach($bookmarks as $key => $video) :

	//Determines which classes need to be applied to the element
	$classes = array();
	if(array_key_exists('loop', $video)) {
		$classes[] = 'loops'; }

	if($availability_check_success)
	{
		if($availability_check_list[$video['id']] === false) {
			$classes[] = 'dead'; }
		elseif($availability_check_list[$video['id']] !== true) {
			$classes[] = 'blocked'; }
	}
	
	$title = htmlspecialchars($video['title']);
?>
			<figure<?php if(!empty($classes)) { echo ' class="' . implode(' ', $classes) . '"'; } ?>>
<?php if(in_array('blocked', $classes)) : ?>
				<p>Blocked : <?php echo htmlspecialchars(implode(' ', $availability_check_list[$video['id']])); ?></p>
<?php endif; ?>
				<img src="_thumbserver/<?php echo $video['id']; ?>.jpg" alt="<?php echo $title; ?> thumbnail" />
<?php if(in_array('dead', $classes)) : ?>
				<p>OFFLINE</p>
<?php else :
		$link = domainCat($key);
		$ytlink = youtubeCat($video['id']); ?>
				<p class="more"><a class="zeroclipboard-btn" data-clipboard-text="<?php echo $link; ?>" href="<?php echo $link; ?>"><span><?php echo domainCat($key, true); ?></span><?php if(array_key_exists('aliases', $video)) { echo '<span>' . implode('', $video['aliases']) . '</span>'; } ?></a><a class="zeroclipboard-btn" data-clipboard-text="<?php echo $ytlink; ?>" href="<?php echo $ytlink; ?>"><?php echo youtubeCat($video['id'], true); ?></a></p>
<?php endif; ?>
				<figcaption title="<?php echo $title; ?>"><a href="<?php echo $link; ?>" target="_blank"><span><?php echo $title; ?></span></a></figcaption>
			</figure>
<?php
endforeach;
?>
			<div class="flex-filler"></div><div class="flex-filler"></div><div class="flex-filler"></div><div class="flex-filler"></div><div class="flex-filler"></div><div class="flex-filler"></div><div class="flex-filler"></div><div class="flex-filler"></div><div class="flex-filler"></div><div class="flex-filler"></div><div class="flex-filler"></div><div class="flex-filler"></div><div class="flex-filler"></div><div class="flex-filler"></div><div class="flex-filler"></div><div class="flex-filler"></div><div class="flex-filler"></div><div class="flex-filler"></div>
		</div>
	</section>
	<section>
		<h3>Now with ∞ loop of pain!</h3>
		<table>
			<caption>Manual parameters:</caption>
			<tr>
				<th>Action</th>
				<th colspan="2">Parameter</th>
			</tr>
			<tr>
				<td>Always loop</td>
				<td><pre>?l</pre></td>
				<td><pre>?l=&lt;anything&gt;</pre></td>
			</tr>
			<tr>
				<td>Never loop</td>
				<td><pre>?l=0</pre></td>
				<td><pre>?l=false</pre></td>
			</tr>
		</table>
		<h3>Re-enabling player controls.</h3>
		<p>Append "c" parameter to the URL.</p>
		<p>Examples:</p>
		<pre>http://turtle.toine512.fr/yuki?c</pre>
		<pre>http://v.toine512.fr/yuki?c</pre>
		<pre>http://v.toine512.fr/yuki?l=0&amp;c</pre>
		<h3>Player keyboard controls</h3>
		<p>Spacebar: Play / Pause<br />Arrow Left: Jump back 10% in the current video<br />Arrow Right: Jump ahead 10% in the current video<br />Arrow Up: Volume up<br />Arrow Down: Volume Down</p>
	</section>
	<script type="text/javascript">
var btns = document.getElementsByClassName('zeroclipboard-btn');

//We have to simulate :hover and :active
var hoverSimulator = new MutationObserver(function(mut) {
	for(var i=0 ; i < mut.length ; i++) {
		if(mut[i].type == 'attributes' && mut[i].attributeName == 'class') {
			// :hover emulation
			if(mut[i].target.classList.contains('zeroclipboard-is-hover')) {
				mut[i].target.parentNode.classList.add('more_hover');
			}
			else {
				mut[i].target.parentNode.classList.remove('more_hover');
			}
			
			// :active emulation
			if(mut[i].target.classList.contains('zeroclipboard-is-active')) {
				mut[i].target.parentNode.parentNode.classList.add('figure_active');
			}
			else {
				mut[i].target.parentNode.parentNode.classList.remove('figure_active');
			}
		}
	}
} );

var client = new ZeroClipboard(btns);

client.on('ready', function(e) {
	for(var i=0 ; i < btns.length ; i++) {
		//Disable links
		btns[i].addEventListener('click', function(e) { e.preventDefault(); });
		//Set observer for event emulation
		hoverSimulator.observe(btns[i], { attributes: true });
	}
} );

client.on('error', function(event) { ZeroClipboard.destroy(); });
	</script>
</body>
<!-- Powered by GLaDOS. -->
<!-- Logs:
<?php echo implode("\n", $cache_msgs); ?> -->
<!-- Processing time : <?php
$time_end = microtime(true);
echo round(($time_end - $time_start) * 1000, 4); ?> ms -->
</html>