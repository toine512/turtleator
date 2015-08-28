<?php
$time_start = microtime(true);

$domain = 'http://v.toine512.fr/';
function domainCat($lnk, $display = false)
{
	global $domain;
	return $display ? htmlspecialchars($domain . $lnk) : $domain . rawurlencode($lnk);
}
function youtubeCat($id, $display = false)
{
	return $display ? htmlspecialchars('http://youtu.be/' . $id) : 'http://youtu.be/' . rawurlencode($id);
}

//Load bookmarks
if(apc_exists('bookmarks-array')) //Cache reading
{
	$bookmarks = apc_fetch('bookmarks-array');
	$check = apc_fetch('bookmarks-unchecked');
}
else
{
	//Downloading bookmarks
	$bookmarks = json_decode(file_get_contents('http://raw.githubusercontent.com/toine512/turtleator/master/bookmarks.json'), true);
	if($bookmarks === null) {
		exit('Failed downloading bookmarks from GitHub !  (http://raw.githubusercontent.com/toine512/turtleator/master/bookmarks.json)');
	}

	//Transform $bookmarks structure in order to handle aliases and create the checklist of video ids for online check
	$check = array();
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
			$check[$line['id']] = false;
		}
	}
	
	//Cache writing
	apc_store('bookmarks-array', $bookmarks, 3600);
	apc_store('bookmarks-unchecked', $check, 3600);
}

//Youtube availability checking
$must_revalidate = true;
$online_check_success = false;
if(apc_exists('bookmarks-checked'))
{
	//Cache read
	$cached = apc_fetch('bookmarks-checked');
	if(empty(array_diff_key($check, $cached)))
	{
		$check = $cached;
		$must_revalidate = false;
		$online_check_success = true;
	}
}

if($must_revalidate)
{
	require('_conf/yt_api_key.php'); //Loads YouTube API server private key

	$online_check_success = true; //Set to false upon any critical error
	foreach(array_chunk(array_keys($check), 50, true) as $chunk) //API limits videos.list query length to 50 items
	{
		//YouTube API v3 call
		$res = file_get_contents('https://www.googleapis.com/youtube/v3/videos?part=status%2CcontentDetails&id=' . implode('%2C', $chunk) . '&fields=items(id%2CcontentDetails(regionRestriction)%2Cstatus(uploadStatus))&key=' . $YT_API_KEY);
		//Fail
		if($res === false) {
			$online_check_success = false;
			break;
		}
		//Success
		else {
			//Decode JSON response
			$json = json_decode($res, true);
			//Fail
			if($json === null) {
				$online_check_success = false;
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
						$check = array_merge($check, $list);
						
						//Cache write
						apc_store('bookmarks-checked', $check, 86400);
					}
				}
				//Something went wrong with the request/answer
				else {
					$online_check_success = false;
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
	<link rel="stylesheet" href="_css/styles.css" media="all" />
</head>
<body>
	<script type="text/javascript" src="_js/ZeroClipboard.min.js"></script>
	<section>
		<h1>toine512 TROLOLOL YouTube shortener (aka Turtleator)</h1>
		<h2>(**with awesome fullpage crap!)</h2>
		<h3>Play any YouTube video fullpage, without controls.</h3>
		<p>http://<b>turtle</b>.toine512.fr/<b>&lt;video_id&gt;</b></p>
		<p>http://<b>v</b>.toine512.fr/<b>&lt;video_id&gt;</b></p>
		<p>See below for manual looping control and controls re-enabling.</p>
	</section>
	<section>
		<h3>List of bookmarks.</h3>
<?php if(!$online_check_success) : ?>
		<p>Availability checking of YouTube videos failed!</p>
<?php endif; ?>
		<div id="gallery">
<?php
foreach($bookmarks as $key => $video) :

	//Determines which classes need to be applied to the element
	$classes = array();
	if(array_key_exists('loop', $video)) {
		$classes[] = 'loops'; }

	if($online_check_success)
	{
		if($check[$video['id']] === false) {
			$classes[] = 'dead'; }
		elseif($check[$video['id']] !== true) {
			$classes[] = 'blocked'; }
	}
	
	$title = htmlspecialchars($video['title']);
?>
			<figure<?php if(!empty($classes)) { echo ' class="' . implode(' ', $classes) . '"'; } ?>>
<?php if(in_array('blocked', $classes)) : ?>
				<p>Blocked : <?php echo htmlspecialchars(implode(' ', $check[$video['id']])); ?></p>
<?php endif; ?>
				<img src="thumbcache/<?php echo $video['id']; ?>.jpg" alt="<?php echo $title; ?> thumbnail" />
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
<?php
$time_end = microtime(true);
echo '<!-- Processing time : ' . round(($time_end - $time_start) * 1000, 4) . " ms -->\n";
?>
</html>