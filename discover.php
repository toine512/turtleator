<?php
$time_start = microtime(true);

require('_conf/bookmarks.array.php'); //Loads $bookmarks

//Transform $bookmarks structure in order to handle aliases and create the checklist of video ids for online check
$check = array();
foreach($bookmarks as $key => $line)
{
	//Alias handling
	if(array_key_exists('alias', $line)) {
		$bookmarks[$line['alias']]['aliases'][] = $key;
		unset($bookmarks[$key]);
	}
	//YouTube checklist construction
	else {
		$check[$line['id']] = false;
	}
}

//Youtube availability checking
require('_conf/yt_api_key.php'); //Loads YouTube API server private key

$online_check_success = true; //Set to false upon any critical error
foreach(array_chunk(array_keys($check), 50, true) as $chunk) //API limits videos.list query length to 50 items
{
	//YouTube API v3 call
	$res = file_get_contents('https://www.googleapis.com/youtube/v3/videos?part=status%2CcontentDetails&id=' . implode('%2C', $chunk) . '&fields=items(id%2CcontentDetails(regionRestriction)%2Cstatus(uploadStatus))&key=' . $YT_API_KEY);
	if($res === false) {
		$online_check_success = false;
		break;
	}
	else {
		//Decode JSON response
		$json = json_decode($res, true);
		if($json === null) {
			$online_check_success = false;
			break;
		}
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
				}
			}
			else {
				$online_check_success = false;
				break;
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
?>
			<figure<?php if(!empty($classes)) { echo ' class="' . implode(' ', $classes) . '"'; } ?>>
<?php if(in_array('blocked', $classes)) : ?>
				<p>Blocked : <?php echo htmlspecialchars(implode(' ', $check[$video['id']])); ?></p>
<?php endif; ?>
				<img src="https://img.youtube.com/vi/<?php echo $video['id']; ?>/mqdefault.jpg" alt="video thumbnail" />
<?php if(in_array('dead', $classes)) : ?>
				<p>OFFLINE</p>
<?php else : ?>
				<div class="more"><pre><?php echo $key . (array_key_exists('aliases', $video) ? implode('', $video['aliases']) : ''); ?></pre></div>
<?php endif; ?>
				<figcaption title="<?php echo htmlspecialchars($video['title']); ?>"><a href="http://v.toine512.fr/<?php echo $key ?>" target="_blank"><span><?php echo htmlspecialchars($video['title']); ?></span></a></figcaption>
			</figure>
<?php
endforeach;
?>
		</div>
	</section>
<?php /*	
	<table>
		<caption>∞ table of happiness:</caption>
		<tr>
			<th>http://turtle.toine512.fr/<br />http://v.toine512.fr/</th>
			<th>What is it?</th>
			<th>Looping behaviour:</th>
			<th>Start (sec)</th>
			<th>End (sec)</th>
			<th>GO!</th>
		</tr>
<?php
foreach($bookmarks as $key => $value)
{
	echo "\t\t<tr>\n\t\t\t<td><pre>" . htmlspecialchars($key) . "</pre></td>\n\t\t\t<td>" . htmlspecialchars($value['title']) . "</td>\n";
	if(array_key_exists('loop', $value))
	{
		echo "\t\t\t<td>Loops.</td>\n";
	}
	else
	{
		echo "\t\t\t<td>Feels alone.</td>\n";
	}
	if(array_key_exists('start', $value))
	{
		echo "\t\t\t<td>" . $value['start'] . "</td>\n";
	}
	else
	{
		echo "\t\t\t<td>∞</td>\n";
	}
	if(array_key_exists('end', $value))
	{
		echo "\t\t\t<td>" . $value['end'] . "</td>\n";
	}
	else
	{
		echo "\t\t\t<td>∞</td>\n";
	}
	echo "\t\t\t<td><a href=\"http://v.toine512.fr/" . htmlspecialchars($key) . '">http://v.toine512.fr/' . htmlspecialchars($key) . "</a></td>\n\t\t</tr>\n";
}
?>
	</table> */?>
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
</body>
<!-- Powered by GLaDOS. -->
<?php
$time_end = microtime(true);
echo '<!-- Processing time : ' . round(($time_end - $time_start) * 1000, 4) . " ms -->\n";
?>
</html>