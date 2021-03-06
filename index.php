<?php
$time_start = microtime(true);
/*** Default Tutleator ***/
$title = 'Mine Turtle';
$vid = 'DI5_sQ8O-7Y';
$loop = false;
$start = 0;
$end = 0;

/*** Loop control, in auto mode the default setting is to loop the video ***/
//See below for loop forcing
if(isset($_GET['l']) && $_GET['l'] == 'auto') {
	$loop = true;
}
/***   ***/

/*** Does GET v exist ? ***/
if(isset($_GET['v']) && !empty($_GET['v']))
{
	if($_GET['v'] != 'turtle' && $_GET['v'] != 'mineturtle' && $_GET['v'] != 'mine turtle') //You explicitly want to be turtle'd here! So we'll keep default Turtleator values.
	{
		/*** Loading bookmarks ***/
		require_once('php-cache512/cache.php');
		$cache = new Cache512('file');
		//Cache read
		if($cache->fetch('bookmarks-array')) {
			$bookmarks = $cache->data;
		}
		else
		{
			//Downloading bookmarks
			$bookmarks = json_decode(file_get_contents('http://raw.githubusercontent.com/toine512/turtleator/master/bookmarks.json'), true);
			if($bookmarks === null) {
				exit('Failed downloading bookmarks from GitHub !  (http://raw.githubusercontent.com/toine512/turtleator/master/bookmarks.json)');
			}

			//Cache write
			$cache->data = $bookmarks;
			$cache->store('bookmarks-array', 3600);
		}

		function resolve_bookmark($v, $bookmarks)
		{
			static $depth = 0; //Limit recursion
			if($depth < 50 && array_key_exists($v, $bookmarks)) {
				$depth++;
				$bm = $bookmarks[$v];
				
				if(array_key_exists('alias', $bm)) {
					return resolve_bookmark($bm['alias'], $bookmarks);
				}
				else {
					return $bm;
				}
			}
			else {
				return false;
			}
		}
		/***   ***/

		if($bm = resolve_bookmark($_GET['v'], $bookmarks)) //Is it a bookmark ?
		{
			$title = $bm['title'];
			$vid = $bm['id'];
			//Optional params
			if(array_key_exists('loop', $bm)) {
				$loop = true;
			}
			if(array_key_exists('start', $bm)) {
				$start = $bm['start'];
			}
			if(array_key_exists('end', $bm)) {
				$end = $bm['end'];
			}
		}
		else if(strlen($_GET['v'] = trim($_GET['v'])) > 10) //No, then it's Youtube video id. (11 chars)
		{
			$title = 'ಠ_ಠ';
			$vid = htmlspecialchars(substr($_GET['v'], 0, 11));
		}
		//Else you're being turtle'd anyway.
	}
}
/***   ***/

/*** Loop enforcement ***/
//Overrides bookmark settings if not auto, if l == 'auto', see at the top of this file
if(isset($_GET['l']) && $_GET['l'] != 'auto')
{
	if($_GET['l'] == 'false' || $_GET['l'] == '0') {
		$loop = false;
	}
	else {
		$loop = true;
	}
}
/***   ***/
?>
<!DOCTYPE html>

<html lang="en">
<!-- See discover.php for the list of bookmarks and some minimalistic instructions. -->
<head>
	<meta charset="utf-8" />
	<title><?php echo $title; ?></title>
<?php if($vid == 'DI5_sQ8O-7Y'): ?>
	<link rel="icon" href="_img/mineturtle.png" sizes="32x32" type="image/png" />
<?php endif; ?>
	<style>
html, body, iframe {
	position: absolute;
	overflow: hidden;
	margin: 0;
	padding: 0;
	width: 100%;
	height: 100%;
	border: none;
}
	</style>
<?php if($loop): ?>
	<script type="text/javascript" src="https://www.youtube.com/iframe_api"></script>
	<script type="text/javascript">
var start = <?php echo $start; ?>;

var ytplayer;
function onYouTubeIframeAPIReady() {
    ytplayer = new YT.Player('ytplayer', { events: {'onStateChange': onPlayerStateChange} });
}

function onPlayerStateChange(event) {
	if(event.data == YT.PlayerState.ENDED || (event.data == YT.PlayerState.PAUSED && event.target.getCurrentTime() == 0)) {
		if(start != 0 && event.target.getCurrentTime() == 0) {
			event.target.seekTo(start, true);
		}
		event.target.playVideo();
    }
}
	</script>
<?php endif; ?>
</head>
<body>
	<iframe id="ytplayer" seamless src="https://www.youtube.com/embed/<?php echo $vid; ?>?modestbranding=1&amp;autoplay=1&amp;rel=0&amp;<?php echo /*Player controls forcing*/ (isset($_GET['c'])) ? 'controls=1&amp;showinfo=1' : 'controls=0&amp;showinfo=0'; /***/ /*Video start time*/ if($start > 0) {echo '&amp;start=' . $start;} /***/ /*Video end time*/ if($end > 0) {echo '&amp;end=' . $end;} /***/ /*Loop control*/ if($loop) {echo '&amp;enablejsapi=1&amp;origin=' . ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] && $_SERVER['HTTPS'] != 'off') ? 'https' : 'http') . '://' . $_SERVER['SERVER_NAME'];} /***/?>"></iframe>
</body>
<!-- Powered by GLaDOS. -->
<!-- Processing time : <?php
$time_end = microtime(true);
echo round(($time_end - $time_start) * 1000, 4); ?> ms -->
</html>