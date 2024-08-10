<?php

# get correct id for plugin
$thisfile = basename(__FILE__, ".php");

# register plugin
register_plugin(
	$thisfile, //Plugin id
	'AdminRenamer ☔', 	//Plugin name
	'1.0', 		//Plugin version
	'multicolor',  //Plugin author
	'https://ko-fi.com/multicolorplugins', //author website
	'Rename admin panel for better security without gsconfig.', //Plugin description
	'plugins', //page type - on which admin tab to display
	'adminRenamer'  //main function (administration)
);


# add a link in the admin tab 'theme'
add_action('plugins-sidebar', 'createSideMenu', array($thisfile, 'AdminRenamer ☔'));



function adminRenamer()
{
	global $GSADMIN;

	$html = "
	
	<h3>AdminRenamer ☔</h3>

	
	<p>Now yours name to login panel <b>$GSADMIN</b></p>


<form method='post'>
<label style='margin-bottom:5px;'>Yours new Admin url name:</label>
<input type='hidden' name='newAdminNameOld' value='$GSADMIN'>
<input type='text' name='newAdminName' style='width:100%;padding:10px;box-sizing:border-box;margin-bottom:5px;' value='$GSADMIN'>
 
<input type='submit' value='save new url' name='changeUrl' style='border: solid 1px;
  padding: 10px 25px;
  background: #333;
  color: #fff;
  display: inline-block;
  border-radius: 5px;
  text-decoration: none;
  margin-bottom: 20px;margin-top:10px;'>
	</form>

<a href='https://ko-fi.com/I3I2RHQZS' target='_blank'><img height='36' style='border:0px;height:36px;' src='https://storage.ko-fi.com/cdn/kofi3.png?v=3' border='0' alt='Buy Me a Coffee at ko-fi.com' /></a>
	";




	echo $html;


	//function



	function copyFolderWithPermissions($src, $dst)
	{
		if (!is_dir($src)) {
			throw new InvalidArgumentException("Źródłowy folder nie istnieje: $src");
		}

		// Upewnij się, że folder docelowy istnieje, jeśli nie, utwórz go
		if (!is_dir($dst)) {
			mkdir($dst, 0755, true);
		}

		// Pobierz uprawnienia źródłowego folderu
		$permissions = is_dir($src) ? (fileperms($src) & 0777) : 0755;
		chmod($dst, $permissions);

		// Otwórz źródłowy folder
		$dir = opendir($src);

		while (($file = readdir($dir)) !== false) {
			if ($file == '.' || $file == '..') {
				continue; // Pomiń '.' i '..'
			}

			$srcPath = $src . DIRECTORY_SEPARATOR . $file;
			$dstPath = $dst . DIRECTORY_SEPARATOR . $file;

			if (is_dir($srcPath)) {
				// Rekursywnie kopiuj podfoldery
				copyFolderWithPermissions($srcPath, $dstPath);
			} else {
				// Kopiuj pliki
				if (file_exists($srcPath)) {
					copy($srcPath, $dstPath);
				} 
			}

			// Skopiuj uprawnienia pliku
			if (file_exists($srcPath)) {
				$permissions = fileperms($srcPath) & 0777;
				chmod($dstPath, $permissions);
			}
		}

		closedir($dir);

		// Usuń źródłowy folder po skopiowaniu jego zawartości
		removeDirectory($src);
	}

	function removeDirectory($dir)
	{
		if (!is_dir($dir)) {
			return;
		}

		$items = array_diff(scandir($dir), ['.', '..']);
		foreach ($items as $item) {
			$path = $dir . DIRECTORY_SEPARATOR . $item;
			if (is_dir($path)) {
				removeDirectory($path);
			} else {
				unlink($path);
			}
		}
	};


	function removeDir($dir)
	{
		// Sprawdź, czy folder istnieje
		if (!is_dir($dir)) {
			throw new InvalidArgumentException("");
		}

		// Pobierz wszystkie elementy w folderze, pomijając '.' i '..'
		$items = array_diff(scandir($dir), ['.', '..']);

		foreach ($items as $item) {
			$path = $dir . DIRECTORY_SEPARATOR . $item;

			if (is_dir($path)) {
				// Rekursywnie usuń podfoldery
				removeDirectory($path);
			} else {
				// Usuń pliki
				unlink($path);
			}
		}

		// Usuń pusty folder
		rmdir($dir);
	}

	function removeEmptyDirectories($dir)
	{
		// Sprawdź, czy folder istnieje
		if (!is_dir($dir)) {
			throw new InvalidArgumentException("Folder nie istnieje: $dir");
		}

		// Pobierz wszystkie elementy w folderze, pomijając '.' i '..'
		$items = array_diff(scandir($dir), ['.', '..']);

		// Rekursyjne usuwanie pustych folderów
		foreach ($items as $item) {
			$path = $dir . DIRECTORY_SEPARATOR . $item;
			if (is_dir($path)) {
				// Rekursywnie sprawdź podfoldery
				removeEmptyDirectories($path);
			}
		}

		// Po usunięciu zawartości podfolderów, sprawdź, czy obecny folder jest pusty
		$items = array_diff(scandir($dir), ['.', '..']);
		if (empty($items)) {
			// Usuń pusty folder
			rmdir($dir);
			
		}
	}

	//


	if (isset($_POST['changeUrl'])) {

		if ($_POST['newAdminNameOld'] !== $_POST['newAdminName']) {

			$dir = GSROOTPATH . 'gsconfig.php';
			$GSDEBUG = file_get_contents($dir);

			if ($GSADMIN !== 'admin') {
				$GSDEBUGNEW = str_replace("define('GSADMIN', '" . $_POST['newAdminNameOld'] . "')", "define('GSADMIN', '" . $_POST['newAdminName'] . "')", $GSDEBUG);

				copyFolderWithPermissions(GSROOTPATH . $_POST['newAdminNameOld'], GSROOTPATH . $_POST['newAdminName']);
				removeEmptyDirectories(GSROOTPATH . $_POST['newAdminNameOld']);
				rmdir(GSROOTPATH . $_POST['newAdminNameOld']);
			} else {
				$search = [
					"# define('GSADMIN', 'admin')",
					"define('GSADMIN', 'admin')"
				];
				$GSDEBUGNEW = str_replace($search, "define('GSADMIN', '" . $_POST['newAdminName'] . "')", $GSDEBUG);

				copyFolderWithPermissions(GSADMINPATH, GSROOTPATH . $_POST['newAdminName']);

				removeEmptyDirectories(GSADMINPATH);
				rmdir(GSROOTPATH . $_POST['newAdminNameOld']);
			};

			file_put_contents($dir, $GSDEBUGNEW);

global $SITEURL;
			echo '<meta http-equiv="refresh" content="0;url='.$SITEURL .$_POST['newAdminName'].'">';
		}
	}
}
