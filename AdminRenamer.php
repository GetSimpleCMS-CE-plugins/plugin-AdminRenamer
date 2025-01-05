<?php

# get correct id for plugin
$thisfile = basename(__FILE__, ".php");

# register plugin
register_plugin(
	$thisfile, //Plugin id
	'AdminRenamer ☔', 	//Plugin name
	'1.1', 		//Plugin version
	'multicolor',  //Plugin author
	'https://ko-fi.com/multicolorplugins', //author website
	'Rename admin panel for better security without gsconfig.', //Plugin description
	'settings', //page type - on which admin tab to display
	'adminRenamer'  //main function (administration)
);

# add a link in the admin tab 'theme'
add_action('settings-sidebar', 'createSideMenu', array($thisfile, 'AdminRenamer ☔'));

function adminRenamer()
{
	global $GSADMIN;
	global $SITEURL;

	$html = '
	<h3>AdminRenamer ☔</h3>

	<p>Your current Admin page: <span style="color:red!important;">'.$SITEURL.'<b>'.$GSADMIN.'</b></span></p>

	<form method="post">
		<label style="margin-bottom:5px;">Your new Admin URL name:</label>
		<input type="hidden" name="newAdminNameOld" value="'.$GSADMIN.'">
		<input type="text" name="newAdminName" style="width:100%;padding:10px;box-sizing:border-box;margin-bottom:5px;" value="'.$GSADMIN.'">

		<input type="submit" value="Save New URL" name="changeUrl" style="border: solid 1px;
		padding: 10px 25px;
		background: #333;
		color: #fff;
		display: inline-block;
		border-radius: 5px;
		text-decoration: none;
		margin-bottom: 20px;margin-top:10px;">
	</form>
	
	<p><svg xmlns="http://www.w3.org/2000/svg" style="vertical-align:middle" width="24" height="24" viewBox="0 0 24 24"><rect width="24" height="24" fill="none"/><path fill="#e06500" fill-rule="evenodd" d="m3.517 17l7.058-11.783a1.667 1.667 0 0 1 2.85 0L20.483 17a1.667 1.667 0 0 1-1.425 2.5H4.942A1.666 1.666 0 0 1 3.517 17M12 9a1 1 0 0 1 1 1v3a1 1 0 1 1-2 0v-3a1 1 0 0 1 1-1m-1 7a1 1 0 0 1 1-1h.008a1 1 0 1 1 0 2H12a1 1 0 0 1-1-1" clip-rule="evenodd"/></svg> Before upgrading your CMS, you will need to revert the folder name back to "admin".</p>
	
	<a href="https://ko-fi.com/I3I2RHQZS" target="_blank"><img height="36" style="border:0px;height:36px;" src="https://storage.ko-fi.com/cdn/kofi3.png?v=3" border="0" alt="Buy Me a Coffee at ko-fi.com" /></a>
	';

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
