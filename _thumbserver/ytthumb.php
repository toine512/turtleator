<?php
if(strlen($_GET['id'] = trim($_GET['id'])) == 11)
{
	$im = @imagecreatefromjpeg('http://img.youtube.com/vi/' . $_GET['id'] . '/mqdefault.jpg');
	if($im === false) { //if loading the thumbnail failed (wrong video id returns 404 along with the default thumbnail)
		//Send the default YouTube thumbnail
		$im = imagecreatefromstring(base64_decode('/9j/4AAQSkZJRgABAQAAAQABAAD/2wCEAAUDBAcFBwUFBQUGBQgFBgUFBQUIBQUHBQgFBQUJBggJBQUTChwLBwgaCQgFDiEYGh0RHxMfEwsiGCIeGBwSExIBBQUFBwYHBQgIBRIIBQgSEhISEhISEhISEhISEhISEhISEhISEhISEhISEhISEhISEhISEhISEhISEhISEhISEv/AABEIAFoAeAMBIgACEQEDEQH/xAAbAAEAAgMBAQAAAAAAAAAAAAAAAQQCAwcGBf/EAD0QAAIBAgMDBwYNBQAAAAAAAAACAQMEBRESBhMhByIxMkFSYRRCcXKS0hVVgYSRlLHBwsPR0/AXUWJks//EABQBAQAAAAAAAAAAAAAAAAAAAAD/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwDvQAAAAACYgnSBiDLQNAGIMtA0gYgmYIAAAAAAAAAQZqpipYoqASkbloHyNoNq7HBWWjcb2tWaFdrejEM6I3Vms8zCp4cc/A+RPKhar1cKum9Na3X9QPYbgbg8XPKnT83B3+W9SPyzH+qi/E0/Xl/aA9tuDFqB41eVOj52D1f8tN7Sn8uDbHKfaN04ZeJ6Ktq33wB6d6ZpZTTgG0VljUP5Izo9KNT29RVStCdGpeMwy+iS3WUCuAwAAAAAAMkLtouqVKSF+znioHCseuJuLzELhm1TVu7ltXTzVrMi/JpVIKZNSec7d56je08lvBLCcQurWyR4pTdVVpa24qurpnT28IYCmD023mysYFNpKXU3CXW9XnoqVVejplubE5SuTKXLrYbdYT8MeW6qi21O9a33S7ndOsTpWrnnryZfDMDxoPV7C7IRjqXVardzarRqLRVUpK7tVZNebcYyXKVPN4jbTaV7i1aYdrWrVt2deqzUnlJlfDgB9bk/rzRxTDW1aYq1Wt38VrIyZfTKnYLmDiey7acQwyV6VvLT/sp26784Ci5BLkAAAAAAEoXbRuMekowb6T6QOF3KSlWsjdKVaqN60VZifsMabykq6NKMkrKOrZSrLxiVbsnM+3tthNSxu7p2SdzdValxb1oXmStV5eVaexomWg+FqjvQBaxC/uL1lq3t1WunWNCPUqs7KnTkv9oJfErlqC2U3ddrdZ1LazVfcxpbOOZ0ZZ8SpmSBaw/Ermy1+SXde13saau7qsmpezV9JVmf5PFpbtlm7ZIzGqO9AH1Nkk14jhi/7ls3svDz9h2m4Y5dya4VUrXdK+ZJWjabx97K5K9aUlESl3pjVnOX3nS6jAamIEgAAAAAAErJAA2atXNZYaO7KrMeya2trd+va28+tb0Z/CBmBqbDLFuth9m3zWj7pj8EWHxZZ/VqP6G/MZga1wyyXq4fZr80o+6bVt6CdS1t09W3or+EjMZgbJfs7F6qxwWPVXsMJkgAAAAAAAAAAAAAAAAAAAAAAAAAAAB//9k='));
	}

	imagesetinterpolation($im, IMG_SINC);
	$im = imagescale($im, 256, 144);
}
else
{
	$im = imagecreate(1, 1);
}

//Will cache for 1 month
header('Cache-Control: public, max-age=2592000');
header('Content-Type: image/jpeg');
imagejpeg($im, null, 81);
imagedestroy($im);
?>
