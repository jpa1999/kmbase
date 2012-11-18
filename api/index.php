<?PHP
	session_start();
	
	require_once "database.class.php";
	require_once "class.upload_0.30.php";
	
	//Vote
	if( $_GET["q"] == "vote" ){
		
		$user_id 		= cleanString( $_GET['user_id'] );
		$facebook_id 	= cleanString( $_GET['facebook_id'] );
		
		$name 	= cleanString( $_GET['name'] );
		$email 	= cleanString( $_GET['email'] );
		
		$phone 	= cleanString( $_GET['phone'] );
		$address = cleanString( $_GET['address'] );
		$zip 	= cleanString( $_GET['zip'] );
		$city 	= cleanString( $_GET['city'] );
		
		
		$_SESSION['vote_user_id']	= $user_id;
		$_SESSION['vote_facebook_id'] = $facebook_id;
		$_SESSION['vote_name'] 		= urldecode( $name );
		$_SESSION['vote_email']		= urldecode( $email );
		$_SESSION['vote_phone'] 	= $phone;
		$_SESSION['vote_address'] 	= urldecode( $address );
		$_SESSION['vote_zip'] 		= $zip;
		$_SESSION['vote_city']		= urldecode( $city );
		
		
		if( empty($user_id)  	){ forward( "?id=" . $user_id . "#kilpailutyo-error-vote_no_user_id");  return; }
		if( empty($name)  		){ forward( "?id=" . $user_id . "#kilpailutyo-error-vote_no_name");  return; }
		
		if( empty($facebook_id)  		){ 
			 if( empty($email) && empty($phone) ){ forward( "?id=" . $user_id . "#kilpailutyo-error-vote_no_email_or_phone");  return; }
			 if( empty($address) ){ forward( "?id=" . $user_id . "#kilpailutyo-error-vote_no_address");  return; }
			 if( empty($zip) ){ forward( "?id=" . $user_id . "#kilpailutyo-error-vote_no_zip");  return; }
			 if( empty($city) ){ forward( "?id=" . $user_id . "#kilpailutyo-error-vote_no_city");  return; }
		}else{
			 if( empty($email) ){ forward( "?id=" . $user_id . "#kilpailutyo-error-vote_no_email");  return; }
		}
		
		//Check for duplicate email
		if( ! empty($email) ){
			$CHECK_EMAIL_SQL = "SELECT COUNT(*) FROM `visio_2042_votes` WHERE email = '{$email}' and user_id='{$user_id}'";
			$check_email_query = new database( $CHECK_EMAIL_SQL );
			$result = mysql_fetch_row( $check_email_query->tulos );
			if( $result[0] > 0){ forward( "?id=" . $user_id . "#kilpailutyo-error-vote_duplicate_email" ); return; }
		}
		//Check for duplicate phone
		if( ! empty($phone) ){
			$CHECK_PHONE_SQL = "SELECT COUNT(*) FROM `visio_2042_votes` WHERE phone = '{$phone}' and user_id='{$user_id}'";
			$check_phone_query = new database( $CHECK_PHONE_SQL );
			$result = mysql_fetch_row( $check_phone_query->tulos );
			if( $result[0] > 0){ forward( "?id=" . $user_id . "#kilpailutyo-error-vote_duplicate_phone" ); return; }
		}
		
		//All good save Vote
		$VOTE_SQL = "INSERT INTO visio_2042_votes ( user_id, facebook_id, name,email,phone,address,zip,city,date ) VALUES ( '{$user_id}', '{$facebook_id}' ,'{$name}','{$email}','{$phone}','{$address}','{$zip}','{$city}', NOW() )";
		
		$vote_query = new database( $VOTE_SQL );
		
		forward("?id=" . $user_id . "#kilpailutyo-vote_kiitos");
		
	}
	
	//Register
	if( $_GET["q"] == "register" ){
		
		
		$_SESSION["email"] =  $_GET['email'];
		$_SESSION["username"] =  $_GET['username'];
		$_SESSION["password"] =  $_GET['password'];
		$_SESSION["password_again"] =  $_GET['password_again'];
		
		$email 		= cleanString( $_GET['email'] );
		$username 	= cleanString( $_GET['username'] );
		$password 	= cleanString( $_GET['password'] );
		$password_again 	= cleanString( $_GET['password_again'] );
		
		
		
		//Check for empties
		if( empty($username)  		){ forward( "#register-error-no_username");  return; }
		if( empty($email)			){ forward( "#register-error-no_email" 	);  return;	}
		if( empty($password)  		){ forward( "#register-error-no_password");  return; }
		if( empty($password_again)  	){ forward( "#register-error-no_password_again");  return; }
		
		if( $password != $password_again ) { forward( "#register-error-no_password_again_match");  return; } 
		
		$password 	= md5( $password );
		
		//Check for duplicate user- and nickname
		$CHECK_USERNAME_SQL = "SELECT COUNT(*) FROM `visio_2042_users` WHERE username = '{$username}'";
	    $check_username_query = new database( $CHECK_USERNAME_SQL );
		$result = mysql_fetch_row( $check_username_query->tulos );
		
		if( $result[0] > 0){ forward( "#register-error-duplicate_username" ); return; }

		//All good save registration
		$REGISTER_SQL = "INSERT INTO visio_2042_users ( email, password, username,date ) VALUES ( '{$email}', '{$password}', '{$username}', NOW() )";
		$register_query = new database( $REGISTER_SQL );
		
		$new_user_id = $register_query->insert_id;
		
		$ADMISSION_INIT_SQL = "INSERT INTO visio_2042_admission_details ( user_id,image,title ) VALUES ( '{$new_user_id}','img/default_image.png','Työllä ei ole vielä nimeä' )";
		$admission_init_query = new database( $ADMISSION_INIT_SQL );
		
		//Start Session and send to thanks
		initUserSession( $new_user_id, $username );
		forward("#register_kiitos");
		
	}
	
	
	if( $_POST["q"] == "update" ){
		
		$user_id = cleanString( $_POST["user_id"] );
	
		 
		if( ( $user_id != $_SESSION['user_id'] || $_SESSION['logged_in'] != true ) &&  $_SESSION['user_id'] != -1  ){
			forward();
			return;	
		}
		
		$target = cleanString( $_POST["target"] );
		if( $target != "title" && $target != "description" && $target != "team" && $target != "team_name" && $target != "url" && $target != "image" && $target != "contact_details"  && $target != "published"){
			forward();
			return;	
		}
		
		$update_value = cleanString( $_POST["update_value"] );
		
		$contact_name = cleanString( $_POST["contact_name"] );
		$contact_phone = cleanString( $_POST["contact_phone"] );
		$contact_email = cleanString( $_POST["contact_email"] );
		$contact_school = cleanString( $_POST["contact_school"] );
		$contact_school_line = cleanString( $_POST["contact_school_line"] );
		$team = cleanString( $_POST["team"] );
		
		if( $target == "contact_details" ){
			updateItem( "contact_name", $contact_name, $user_id );
			updateItem( "contact_phone", $contact_phone, $user_id );
			updateItem( "contact_email", $contact_email, $user_id );
			updateItem( "contact_school", $contact_school, $user_id );
			updateItem( "contact_school_line", $contact_school_line, $user_id );
			updateItem( "team", $team, $user_id );
		}else{
			updateItem( $target, $update_value, $user_id );
		}
		
		
		forward("?id=" . $user_id . "#kilpailutyo");
		
	}
	
	function updateItem( $target, $update_value, $user_id ){
		
		$UPDATE_SQL = "UPDATE visio_2042_admission_details SET " . $target . "='". $update_value . "' WHERE user_id =" . $user_id ;
		$update_query = new database( $UPDATE_SQL );
	}
	

	
	if( $_POST["q"] == "upload" ){
		
		$target = cleanString( $_POST["target"] );
		if( $target != "image" ){
			forward();
			return;	
		}
		
		$user_id = cleanString( $_POST["user_id"] );
		
		
		$foo = new Upload( $_FILES['image_field'] );
		if ($foo->uploaded) {
			 // save uploaded image with no changes
		 	$foo->file_new_name_body = 'image_resized_';
		  	$foo->image_resize = true;
		  	$foo->image_convert = "jpg";
		  	$foo->image_x = 200;
		  	$foo->image_ratio_y = true;
			$foo->image_unsharp = true;
			$foo->jpeg_quality = 90;
			
		  	$foo->Process('../uploads/');
		  	if ($foo->processed) {
				$foo->Clean();
		  	} else {
				echo 'error : ' . $foo->error;
		  	}
			
			
			if ($foo->processed) {
				updateItem( "image", "uploads/".$foo->file_dst_name, $user_id );
			} else {
				echo 'error : ' . $foo->error;
			}
		}
		
		forward("?id=" . $user_id . "#kilpailutyo");
		
	}
	// LOGIN AND LOGOUT
	if( $_GET["q"] == "login" ){
		
		$username 	= cleanString( $_GET['username'] );
		$password 	= md5( cleanString( $_GET['password'] ) );
		
		if( empty($username)  		){ forward( "#etusivu-error-no_login_username");  return; }
		if( empty($password)  		){ forward( "#etusivu-error-no_login_password");  return; }
		
		if( $username=="Rakennustieto" && $password="Visio2012!" ){
			initUserSession( -1,"Pääkäyttäjä" );
			forward("#etusivu-login-ready");
		}
		
		$LOGIN_SQL = "SELECT user_id, username, password FROM visio_2042_users WHERE username='{$username}'";
		$login_query = new database( $LOGIN_SQL );
		$row_count = mysql_num_rows( $login_query->tulos  );
		if( $row_count == 0 ){ forward( "#etusivu-error-wrong_username");  return; }
		
		$result = mysql_fetch_object( $login_query->tulos );
		
		if( $password == $result->password ){
			initUserSession( $result->user_id, $result->username );
			forward("#etusivu-login-ready");
		}else{
			forward( "#etusivu-error-wrong_password"); 
		}
		
	}
	
	if( $_GET["q"] == "logout" ){
		session_start();
		session_unset();
		session_destroy();
		$_SESSION = array();
		
		forward();
	}
	
	
	
	function initUserSession( $user_id, $username){
		$_SESSION['logged_in'] = true;
		$_SESSION['user_id'] = $user_id;
		$_SESSION['username'] = $username;
	}
	
	
	function forward( $url_fragment = "" ){
		header("Location: ../" .  $url_fragment );	
	}
	
	
	
	//----------------------------
	// OUTPUT
	//----------------------------
	
	echo $result_string;
	

	//----------------------------
	// FUNCTIONS
	//----------------------------
	function getObjectItem( $name, $value, $delimiter ){
		return '"' . $name . '":"' . $value . '"' . $delimiter;
	}
	
	function cleanString( $string ){
		$database = new database();
		return mysql_real_escape_string($string, $database->yhteysnumero );	
	}
	
	function mysql2json($mysql_result,$name){
    
		 $json="{\n\"$name\": [\n";
		 $field_names = array();
		 $fields = mysql_num_fields($mysql_result);
		 for($x=0;$x<$fields;$x++){
			  $field_name = mysql_fetch_field($mysql_result, $x);
			  if($field_name){
				   $field_names[$x]=$field_name->name;
			  }
		 }
		 $rows = mysql_num_rows($mysql_result);
		 for($x=0;$x<$rows;$x++){
			  $row = mysql_fetch_array($mysql_result);
			  $json.="{\n";
			  for($y=0;$y<count($field_names);$y++) {
				   $json.="\"$field_names[$y]\" :	\"$row[$y]\"";
				   if($y==count($field_names)-1){
						$json.="\n";
				   }
				   else{
						$json.=",\n";
				   }
			  }
			  if($x==$rows-1){
				   $json.="\n}\n";
			  }
			  else{
				   $json.="\n},\n";
			  }
		 }
		 //$json.="]\n};";
		 $json.="]\n}";
		 return($json);
	}

?>