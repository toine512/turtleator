<?php
$time_start = microtime(true);
require('bookmarks.array.php'); //Loads $bookmarks
?>
<!DOCTYPE html>

<html lang="en">
<head>
	<meta charset="utf-8" />
	<title>toine512 bookmark list</title>
	<link rel="icon" href="mineturtle.png" sizes="32x32" type="image/png" />
	<style>
	</style>
</head>
<body>
	<h1>toine512 TROLOLOL Youtube shortener (aka Turtleator)</h1>
	<h2>(**with awesome fullpage crap!)</h2>
	<h3>Play any Youtube video fullpage, without controls.</h3>
	<p>http://<b>turtle</b>.toine512.fr/<b>&lt;video_id&gt;</b></p>
	<p>http://<b>v</b>.toine512.fr/<b>&lt;video_id&gt;</b></p>
	<p>See below for manual looping control and controls re-enabling.</p>
	<h3>List of bookmarks.</h3>
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
	</table>
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
</body>
<!-- Powered by GLaDOS. -->
<?php
$time_end = microtime(true);
echo '<!-- Processing time : ' . round(($time_end - $time_start) * 1000, 4) . " ms -->\n";
?>
</html>