<?php
$time_start = microtime(true);
//Default Tutleator
$title = 'Mine Turtle';
$vid = 'DI5_sQ8O-7Y';
$loop = false;
$start = 0;
$end = 0;

//Loop control, in auto mode the default setting is to loop the video
//See below for loop forcing
if(isset($_GET['l']))
{
	if($_GET['l'] == 'auto')
	{
		$loop = true;
	}
}

//Does GET v exist ?
if(isset($_GET['v']))
{
	if(!empty($_GET['v']))
	{
		if($_GET['v'] != 'turtle' && $_GET['v'] != 'mineturtle' && $_GET['v'] != 'mine turtle') //You explicitly want to be turtle'd here! So we'll keep default Turtleator values.
		{
			require('_conf/bookmarks.array.php'); //Loads $bookmarks
			function resolve_bookmark($v, $bookmarks)
			{
				if(array_key_exists($v, $bookmarks)) {
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

			if($bm = resolve_bookmark($_GET['v'], $bookmarks)) //Is it a bookmark ?
			{
				$title = $bm['title'];
				$vid = $bm['id'];
				if(array_key_exists('loop', $bm))
				{
					$loop = true;
				}
				if(array_key_exists('start', $bm))
				{
					$start = $bm['start'];
				}
				if(array_key_exists('start', $bm))
				{
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
}

//Loop forcing
//Overrides bookmark settings if not auto, if l == 'auto', see at the top of this file
if(isset($_GET['l']))
{
	if($_GET['l'] != 'auto')
	{
		if($_GET['l'] == 'false' || $_GET['l'] == '0')
		{
			$loop = false;
		}
		else
		{
			$loop = true;
		}
	}
}
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
var ytplayer;
function onYouTubeIframeAPIReady() {
    ytplayer = new YT.Player('ytplayer', { events: {'onStateChange': onPlayerStateChange} });
}

function onPlayerStateChange(event) {
    if(event.data == YT.PlayerState.ENDED) {
        event.target.playVideo();
    }
}
	</script>
<?php endif; ?>
</head>
<body>
	<iframe id="ytplayer" seamless src="https://www.youtube.com/embed/<?php echo $vid; ?>?modestbranding=1&amp;autoplay=1&amp;rel=0&amp;<?php echo /*Player controls forcing*/ (isset($_GET['c'])) ? 'controls=1&amp;showinfo=1' : 'controls=0&amp;showinfo=0'; /***/ /*Video start time*/ if($start > 0) {echo '&amp;start=' . $start;} /***/ /*Video end time*/ if($end > 0) {echo '&amp;end=' . $end;} /***/ /*Loop control*/ if($loop) {echo '&amp;enablejsapi=1&amp;origin=http://' . $_SERVER['HTTP_HOST'];} /***/?>"></iframe>
</body>
<!-- Powered by GLaDOS. -->
<?php
$time_end = microtime(true);
echo '<!-- Processing time : ' . round(($time_end - $time_start) * 1000, 4) . " ms -->\n";
?>
</html>