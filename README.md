# Login-Management-JWT
Simple Login Management with JSON Web Token

# Example

~ Login
```
<?php
require 'YOUR_COMPOSER_FOLDER/autoload.php';
use HJWTManagement\HJWTManagement;

$indexSession = new HJWTManagement();

// utama = Check if your already login, redirect to dashboard roles
$indexSession->checkLogin('utama');

if ($_SERVER["REQUEST_METHOD"] == 'POST') {
    $loginS = $indexSession->Login($_POST['emailTxt'], $_POST['passTxt']);

    if (is_array($loginS)) {
        $loginX = 'SUCCESS';
    } else {
        $loginX = 'FAILED';
    }
}
?>
```

~ Logout
```
<?php
require 'YOUR_COMPOSER_FOLDER/autoload.php';
use HJWTManagement\HJWTManagement;

$indexSession = new HJWTManagement();

if (isset($_COOKIE['X-ENDRS-SESSION'])) {
	$dataSession = $indexSession->getDataCookie()['value'];

	$runDelCookie = $indexSession->deleteSession($dataSession->uniqueID);
	
	if ($runDelCookie) {
		setcookie('X-ENDRS-SESSION', '', time()-3600, '/');
        
        // Redirect to your index
	} else {
        // Back to your dashboard
	}
} else {
	// Back to your dashboard
}
?>
```

~ Dashboard Process
```
<?php
require 'YOUR_COMPOSER_FOLDER/autoload.php';
use HJWTManagement\HJWTManagement;

$indexSession = new HJWTManagement();

/*
* checkLogin($roles)
* $roles = admin, bisnis, influencer
*
* Check if you already login return boolean true
*/
$indexSession->checkLogin('admin');

if ($_SERVER["REQUEST_METHOD"] == 'POST') {
    // YOUR DASHBOARD PROCCESS
}
?>
```

~ API Process
```
<?php
require 'YOUR_COMPOSER_FOLDER/autoload.php';
use HJWTManagement\HJWTManagement;

$indexSession = new HJWTManagement();

/*
* checkApi($roles)
* $roles = admin, bisnis, influencer
*
* Check if you already login return boolean true
*/
$indexSession->checkApi('admin');

// Set your Request Method
if ($_SERVER["REQUEST_METHOD"] == 'POST') {
    // YOUR API PROCCESS
}
?>
```

~ Notification Process
```
<?php
require 'YOUR_COMPOSER_FOLDER/autoload.php';
use HJWTManagement\HJWTManagement;

$indexSession = new HJWTManagement();

/*
* checkNotif($roles)
* $roles = admin, bisnis, influencer
*
* Check if you already login return boolean true
*/
$indexSession->checkNotif('admin');

// Set your Request Method
if ($_SERVER["REQUEST_METHOD"] == 'POST') {
    // YOUR NOTIFICATION PROCCESS
}
?>
```

Sample
```
<?php
require 'YOUR_COMPOSER_FOLDER/autoload.php';
use HJWTManagement\HJWTManagement;

$indexSession = new HJWTManagement();

if ($indexSession->checkNotif('admin') === true) {
	$user = null;
	$whereNotifikasi = 'Approval';
} elseif ($indexSession->checkNotif('bisnis') === true) {
	$user = array('endorsme_bisnis.id_user' => intval($dataCookies->user_id));
	$whereNotifikasi = 'Progress';
} elseif ($indexSession->checkNotif('influencer') === true) {
	$user = array('endorsme_influencer.id_user' => intval($dataCookies->user_id));
	$whereNotifikasi = 'Pending';
} else {
	$user = null;
	$whereNotifikasi = null;
}

// Model get data promosi with requirement
$dataPromosiProduk = $koneksiSQL->ambilDataPromosi('endorsme_promosi', $user, 'get', $whereNotifikasi, 'endorsme_promosi.id_promosi', 5);
?>
```
