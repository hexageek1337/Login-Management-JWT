<?php
namespace HJWTManagement;
use Firebase\JWT\JWT;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
/**
 * SESSION MANAGEMENT
 * v1.0
 */
class HJWTManagement {
	// Connection Library
	private $koneksiSQL = '';
	// Mailer Library
	private $mail = '';

	/* Settings of Connection Database */
	private $host = 'localhost';
	private $user = 'root';
	private $pass = '';
	private $database = 'endorsmenew';

	/* Settings of Mailer */
	private $hostMail = 'smtp.gmail.com';
	private $nameMail = '';
	private $usernameMail = '';
	private $passwordMail = '';
	private $secureMail = 'ssl';
	private $portMail = 465;

	// JWT Secret
	private $SECRET_KEY = 'fitri';

	public function __construct(){
		// Set Library
		$this->mail = new PHPMailer(true);
	}

	public function Koneksi($value=''){
		$this->koneksiSQL = new \mysqli($this->host, $this->user, $this->pass, $this->database);

		return $this->koneksiSQL;
	}

	public function sendEmail($emailID, $hashID){
		if ($emailID != '' AND $hashID != '') {
			$nameEmail = explode('@', $emailID)[0];

			$this->mail->SMTPDebug = 0;
			$this->mail->isSMTP();
			$this->mail->Host = $this->hostMail;
			$this->mail->SMTPAuth = true;
			//ganti dengan email dan password yang akan di gunakan sebagai email pengirim
			$this->mail->Username = $this->usernameMail;
			$this->mail->Password = $this->passwordMail;
			$this->mail->SMTPSecure = $this->secureMail;
			$this->mail->Port = $this->portMail;
			//ganti dengan email yg akan di gunakan sebagai email pengirim
			$this->mail->setFrom($this->usernameMail, $this->nameMail);
			$this->mail->addAddress($emailID, $nameEmail);
			$this->mail->isHTML(true);
			$this->mail->Subject = "Silahkan Aktivasi Untuk Layanan Endorseme";
			$this->mail->Body = "<body style='margin: 10px;'>
					<div style='width: 640px; font-family: Helvetica, sans-serif; font-size: 13px; padding:10px; line-height:150%; border:#eaeaea solid 10px;'>
					<img src='https://hmp.me/dp9o' />
					<p>Terima kasih telah mendaftar di Layanan Endorseme. Silahkan Aktivasi Akun Anda dengan klik tombol <b>Aktivasi</b> di bawah ini.</p>
					<center><a href='".$hashID."' target='_blank' style='background-color:#ED2939;padding: 8px 12px; border: 1px solid #ED2939;border-radius: 2px;font-family: Helvetica, Arial, sans-serif;font-size: 14px; color: #ffffff;text-decoration: none;font-weight:bold;display: inline-block;'>Aktivasi</a></center><br>
					<br>
					<center>atau salin link aktivasi dibawah :<br>
					<b>".$hashID."</b></center>
					</div>
					</body>";
			
			if ($this->mail->send()) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function insertSession($uniqID, $tokenID, $timeID, $expiredID){
		if ($uniqID != '' AND $tokenID != '' AND $timeID != '') {
			$uniqTxt = intval($uniqID);
			$tokenTxt = $this->Koneksi()->real_escape_string($tokenID);
			$timeTxt = $this->Koneksi()->real_escape_string($timeID);
			$expiredTxt = $this->Koneksi()->real_escape_string($expiredID);

			$sqlSession = "INSERT INTO endorsme_session (id_session,unique_session,jwt_session,created_session,expired_session) VALUES (NULL, ".$uniqTxt.", '".$tokenTxt."', '".$timeTxt."', '".$expiredTxt."')";
			$runSessionDatabase = $this->Koneksi()->query($sqlSession);

			if ($runSessionDatabase) {
				return true;
			} else {
				return false;
			}
		} else {
			throw new Exception("Parameter Kosong!");
			
		}
	}

	public function deleteSession($uniqID){
		if ($uniqID != '') {
			$uniqTxt = intval($uniqID);

			$sqlSession = "DELETE FROM endorsme_session WHERE unique_session = ".$uniqTxt;
			$runSessionDatabase = $this->Koneksi()->query($sqlSession);

			if ($runSessionDatabase) {
				return true;
			} else {
				return false;
			}
		} else {
			throw new Exception("Parameter Kosong!");
			
		}
	}

	public function Login($username, $password){
		$errorLogin = '';
	    $namaLogin = '';
	    $successLogin = '';
	    $redirectLink = '';

	    if (isset($username) AND isset($password)) {
	        if ($username != '' AND $password != '') {
	            $emailPost = addslashes($username);
	            $emailPost = $this->Koneksi()->real_escape_string($emailPost);
	            $passPost = addslashes($password);
	            $passPost = $this->Koneksi()->real_escape_string($passPost);

	            $qryDataLog = "SELECT * FROM endorsme_user WHERE email_user = '".$emailPost."'";

	            $qryDataLogin = $this->Koneksi()->query($qryDataLog);
	            $jumlahDataLogin = $qryDataLogin->num_rows;

	            if ($jumlahDataLogin > 0) {
	                $dataLogin = $qryDataLogin->fetch_all(MYSQLI_ASSOC)[0];

	                $dataPassword = $dataLogin['password_user'];

	                if (password_verify($passPost, $dataPassword)) {
	                    if ($dataLogin['status_user'] === 'Aktif') {
	                        if ($dataLogin['level_user'] === 'Admin') {
	                            $namaLogin = 'Administrator';

	                            $successLogin = 'Berhasil login ke <strong>'.$namaLogin.'</strong>';
	                            $redirectLink = $GLOBALS['base_url'].'dashboard/admin';
	                        } elseif ($dataLogin['level_user'] === 'Bisnis') {
	                            $namaLogin = 'Bisnis';

	                            $successLogin = 'Berhasil login ke <strong>'.$namaLogin.'</strong>!';

	                            $redirectLink = $GLOBALS['base_url'].'dashboard/bisnis';
	                        } elseif ($dataLogin['level_user'] === 'Influencer') {
	                            $namaLogin = 'Influencer';

	                            $successLogin = 'Berhasil login ke <strong>'.$namaLogin.'</strong>!';
                            
                              // SET Redirect to your dashboard influencer
	                            $redirectLink = $GLOBALS['base_url'].'dashboard/influencer';
	                        }

	                        $sessionID = rand(1,9999);

	                        $payloadJWT = array(
	                        	'uniqueID' => $sessionID,
	                        	'nama_login' => $namaLogin,
	                        	'user_id' => intval($dataLogin['id_user']),
	                        	'role' => $dataLogin['level_user']
	                        );

	                        $jwtResult = \Firebase\JWT\JWT::encode($payloadJWT, $this->SECRET_KEY, 'HS256');
	                        $dateCookie = date('Y-m-d h:i:s');
	                        $expiredDate = date('Y-m-d h:i:s', strtotime($dateCookie." +7 day"));

	                        // Set Cookie on Database
	                        $runSimpanCookie = $this->insertSession($sessionID, $jwtResult, $dateCookie, $expiredDate);

	                        if ($runSimpanCookie) {
	                        	// Set Cookie
		                        setcookie('X-ENDRS-SESSION', $jwtResult);

		                        $resultArray = array(
		                        	'successLogin' => $successLogin,
		                        	'redirectLink' => $redirectLink
		                        );

		                        return $resultArray;
	                        } else {
	                        	$errorLogin = 'Gagal menyimpan cookie ke dalam database!';

	                        	return $errorLogin;
	                        }
	                    } else {
	                        $errorLogin = 'Anda belum memverifikasi email!';

	                        return $errorLogin;
	                    }
	                } else {
	                    $errorLogin = 'Password Salah!';

	                    return $errorLogin;
	                }
	            } else {
	                $errorLogin = 'Email belum terdaftar!';

	                return $errorLogin;
	            }
	        } else {
	            $errorLogin = 'Email atau Password Belum Diisi!';

	            return $errorLogin;
	        }
	    } else {
	        $errorLogin = 'Email atau Password Belum Diisi!';

	        return $errorLogin;
	    }
	}

	public function getDataCookie(){
		$jwtCookies = $_COOKIE['X-ENDRS-SESSION'];

		$dataCookie = array(
			'name' => $jwtCookies,
			'value' => \Firebase\JWT\JWT::decode($jwtCookies, $this->SECRET_KEY, ['HS256'])
		);

		return $dataCookie;
	}

	public function encryptJWT($string = null){
		if (!is_null($string)) {
			$payloadJWT = array(
				'ip' => $_SERVER['REMOTE_ADDR'],
				'email' => $string
			);

			$jwtResult = \Firebase\JWT\JWT::encode($payloadJWT, $this->SECRET_KEY, 'HS256');
		} else {
			$jwtResult = '';
		}

		return $jwtResult;
	}

	public function decryptJWT($string = null){
		if (!is_null($string)) {
			$jwtResult = \Firebase\JWT\JWT::decode($string, $this->SECRET_KEY, ['HS256']);
		} else {
			$jwtResult = '';
		}

		return $jwtResult;
	}

	public function checkLogin($halaman){
		if (!isset($halaman) OR $halaman === 'utama') {
			if (isset($_COOKIE['X-ENDRS-SESSION'])) {
				$cookiesData = $this->getDataCookie();

				$whereSession = array('unique_session' => $cookiesData['value']->uniqueID);
				$databaseCookie = $this->koneksiSQL->ambilData('endorsme_session', $whereSession, 'get');

				$jumlahDataCookie = $databaseCookie['jumlahData'];

				if ($jumlahDataCookie > 0) {
					$cekCookie = $cookiesData['value'];

					if ($cekCookie->role === 'Admin') {
						echo '<script>window.location.href= "'.$GLOBALS['base_url'].'dashboard/admin'.'";</script>';
					} elseif ($cekCookie->role === 'Bisnis') {
						echo '<script>window.location.href= "'.$GLOBALS['base_url'].'dashboard/bisnis'.'";</script>';
					} elseif ($cekCookie->role === 'Influencer') {
						echo '<script>window.location.href= "'.$GLOBALS['base_url'].'dashboard/influencer'.'";</script>';
					}
				} else {
					return true;
				}
			} else {
				return true;
			}
		} else {
			if (isset($_COOKIE['X-ENDRS-SESSION'])) {
				$cookiesData = $this->getDataCookie();

				$whereSession = array('unique_session' => $cookiesData['value']->uniqueID);
				$databaseCookie = $this->koneksiSQL->ambilData('endorsme_session', $whereSession, 'get');

				$jumlahDataCookie = $databaseCookie['jumlahData'];

				if ($jumlahDataCookie > 0) {
					$resultCookie = $databaseCookie['result'][0];
					if ($resultCookie['jwt_session'] === $cookiesData['name']) {
						$cekCookie = $cookiesData['value'];
			  			if (isset($halaman) AND $halaman === 'non_dashboard') {
			  				if ($cekCookie->role === 'Admin') {
			  					echo '<script>window.location.href= "'.$GLOBALS['base_url'].'dashboard/admin'.'";</script>';
			  				} elseif ($cekCookie->role === 'Bisnis') {
			  					echo '<script>window.location.href= "'.$GLOBALS['base_url'].'dashboard/bisnis'.'";</script>';
			  				} elseif ($cekCookie->role === 'Influencer') {
			  					echo '<script>window.location.href= "'.$GLOBALS['base_url'].'dashboard/influencer'.'";</script>';
			  				}
			  			} elseif (isset($halaman) AND $halaman === 'admin') {
			  				if ($cekCookie->role === 'Bisnis') {
			  					echo '<script>alert("Anda sudah login sebagai Bisnis");window.location.href= "'.$GLOBALS['base_url'].'dashboard/bisnis'.'";</script>';
			  				} elseif ($cekCookie->role === 'Influencer') {
			  					echo '<script>alert("Anda sudah login sebagai Influencer");window.location.href= "'.$GLOBALS['base_url'].'dashboard/influencer'.'";</script>';
			  				}
			  			} elseif (isset($halaman) AND $halaman === 'bisnis') {
			  				if ($cekCookie->role === 'Admin') {
			  					echo '<script>alert("Anda sudah login sebagai Admin");window.location.href= "'.$GLOBALS['base_url'].'dashboard/admin'.'";</script>';
			  				} elseif ($cekCookie->role === 'Influencer') {
			  					echo '<script>alert("Anda sudah login sebagai Influencer");window.location.href= "'.$GLOBALS['base_url'].'dashboard/influencer'.'";</script>';
			  				}
			  			} elseif (isset($halaman) AND $halaman === 'influencer') {
			  				if ($cekCookie->role === 'Admin') {
			  					echo '<script>alert("Anda sudah login sebagai Admin");window.location.href= "'.$GLOBALS['base_url'].'dashboard/admin'.'";</script>';
			  				} elseif ($cekCookie->role === 'Bisnis') {
			  					echo '<script>alert("Anda sudah login sebagai Bisnis");window.location.href= "'.$GLOBALS['base_url'].'dashboard/bisnis'.'";</script>';
			  				}
			  			}
			  		} else {
			  			echo '<script>alert("Anda belum login!");window.location.href= "'.$GLOBALS['base_url'].'";</script>';
			  		}
			  	} else {
			  		echo '<script>alert("Anda belum login!");window.location.href= "'.$GLOBALS['base_url'].'";</script>';
			  	}
			} else {
				echo '<script>alert("Anda belum login!");window.location.href= "'.$GLOBALS['base_url'].'";</script>';
			}
		}
	}

	public function checkApi($halaman){
		if (!isset($halaman) OR $halaman === 'utama') {
			exit();
		} else {
			if (isset($_COOKIE['X-ENDRS-SESSION'])) {
				$cookiesData = $this->getDataCookie();

				$whereSession = array('unique_session' => $cookiesData['value']->uniqueID);
				$databaseCookie = $this->koneksiSQL->ambilData('endorsme_session', $whereSession, 'get');

				$jumlahDataCookie = $databaseCookie['jumlahData'];

				if ($jumlahDataCookie > 0) {
					$resultCookie = $databaseCookie['result'][0];
					if ($resultCookie['jwt_session'] === $cookiesData['name']) {
						$cekCookie = $cookiesData['value'];
			  			if (isset($halaman) AND $halaman === 'admin') {
			  				if ($cekCookie->role === 'Bisnis') {
			  					exit();
			  				} elseif ($cekCookie->role === 'Influencer') {
			  					exit();
			  				} else {
			  					return true;
			  				}
			  			} elseif (isset($halaman) AND $halaman === 'bisnis') {
			  				if ($cekCookie->role === 'Admin') {
			  					exit();
			  				} elseif ($cekCookie->role === 'Influencer') {
			  					exit();
			  				} else {
			  					return true;
			  				}
			  			} elseif (isset($halaman) AND $halaman === 'influencer') {
			  				if ($cekCookie->role === 'Admin') {
			  					exit();
			  				} elseif ($cekCookie->role === 'Bisnis') {
			  					exit();
			  				} else {
			  					return true;
			  				}
			  			}
			  		} else {
			  			exit();
			  		}
			  	} else {
			  		exit();
			  	}
			} else {
				exit();
			}
		}
	}

	public function checkNotif($checkRoles){
		if (isset($_COOKIE['X-ENDRS-SESSION'])) {
			$cookiesData = $this->getDataCookie();

			$whereSession = array('unique_session' => $cookiesData['value']->uniqueID);
			$databaseCookie = $this->koneksiSQL->ambilData('endorsme_session', $whereSession, 'get');

			$jumlahDataCookie = $databaseCookie['jumlahData'];

			if ($jumlahDataCookie > 0) {
				$resultCookie = $databaseCookie['result'][0];

				if ($resultCookie['jwt_session'] === $cookiesData['name']) {
					$cekCookie = $cookiesData['value'];

			  		if (isset($checkRoles) AND $checkRoles === 'admin') {
			  			if ($cekCookie->role === 'Bisnis') {
			  				return false;
			  			} elseif ($cekCookie->role === 'Influencer') {
			  				return false;
			  			} else {
			  				return true;
			  			}
			  		} elseif (isset($checkRoles) AND $checkRoles === 'bisnis') {
			  			if ($cekCookie->role === 'Admin') {
			  				return false;
			  			} elseif ($cekCookie->role === 'Influencer') {
			  				return false;
			  			} else {
			  				return true;
			  			}
			  		} elseif (isset($checkRoles) AND $checkRoles === 'influencer') {
			  			if ($cekCookie->role === 'Admin') {
			  				return false;
			  			} elseif ($cekCookie->role === 'Bisnis') {
			  				return false;
			  			} else {
			  				return true;
			  			}
			  		}
			  	} else {
			  		return false;
		  		}
		  	} else {
		  		return false;
		  	}
		} else {
			return false;
		}
	}
}
