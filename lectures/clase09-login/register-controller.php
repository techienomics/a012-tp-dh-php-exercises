<?php
	require_once 'config.php';
	session_start();

	// $_SESSION['usuario'] = 'Pepito'; 

	// function myDebug
	function myDebug($dato, $type = NULL) {
		echo "<pre>";
		switch ($type) {
			case 'print_r':
				print_r($dato);
				break;
			case 'echo':
				echo $dato;
				break;
			default:
				var_dump($dato);
				break;
		}
		echo "</pre>";
		exit;
	}

	// Validar el Register
	function registerValidate($formData, $files) {
		$errors = [];

		// Sanitización de Datos
		$name = trim($formData['userFullName']);
		$email = trim($formData['userEmail']);
		$password = trim($formData['userPassword']);
		$rePassword = trim($formData['userRePassword']);
		$country = trim($formData['userCountry']);

		$avatar = $files['userAvatar'];

		// Validación de cada campo
		if ( empty($name) ) {
			$errors['fullName'] = 'Escribí tu nombre completo';
		}

		if ( empty($email) ) {
			$errors['email'] = 'Escribí tu correo electrónico';
		} else if ( !filter_var($email, FILTER_VALIDATE_EMAIL) ) {
			$errors['email'] = 'Escribí un correo válido';
		} else if ( emailExist($email) ) {
			$errors['email'] = 'Ese email ya fue registrado';
		}

		if ( empty($password) || empty($rePassword) ) {
			$errors['password'] = 'La contraseña no puede estar vacía';
		} elseif ( $password != $rePassword) {
			$errors['password'] = 'Las contraseñas no coinciden';
		} elseif ( strlen($password) < 4 || strlen($rePassword) < 4 ) {
			$errors['password'] = 'La contraseña debe tener más de 4 caracteres';
		}

		if ( empty($country) ) {
			$errors['country'] = 'Elegí un país';
		}
		
		if ( $avatar['error'] !== UPLOAD_ERR_OK ) {
			$errors['image'] = 'Ché subite una imagen';
		} else {
			$ext = pathinfo($avatar['name'], PATHINFO_EXTENSION);
			if ( !in_array($ext, ALLOWED_IMAGE_TYPES) ) {
				$errors['image'] = 'Formato de imagen no permitido';
			}
		}

		return $errors;
	}

	// Función Crear Usuarios
	function userCreator($data){
		$user = [
			'id' => setId(),
			'name' => $data['userFullName'],
			'email' => $data['userEmail'],
			'password' => password_hash($data['userPassword'], PASSWORD_DEFAULT),
			'country' => $data['userCountry'],
			'avatar' => $data['avatar'],
		];
		return $user;
	}

	// Función Guardar Usuario
	function saveUser($dataDePost){
		$finalUser = userCreator($dataDePost);
		$userInJsonFormat = json_encode($finalUser);
		file_put_contents('data/users.json', $userInJsonFormat . PHP_EOL, FILE_APPEND);
	}

	// Función traer todos los Usuarios
	function getAllUsers() {
		$allUsersString = file_get_contents('data/users.json');
		$usersInArray = explode(PHP_EOL, $allUsersString);
		array_pop($usersInArray);
		$finalUsersArray = [];
		foreach ($usersInArray as $oneUser) {
			$finalUsersArray[] = json_decode($oneUser, true);
		}
		return $finalUsersArray;
	}

	// Función Generar ID
	function setId(){
		$allUsers = getAllUsers();
		if( count($allUsers) == 0 ) {
			return 1;
		}
		$lastUser = array_pop($allUsers);
		return $lastUser['id'] + 1;
	}

	// Corrobora si existe o no un usuario con este email
	function emailExist($email) {
		$allUsers = getAllUsers();
		foreach ($allUsers as $oneUser) {
			if ($email == $oneUser['email']) {
				return true;
			}
		}
		return false;
	}

	// Función para subir la imagen
	function saveImage($image) {
		$imgName = $image['name'];
		$ext = pathinfo($imgName, PATHINFO_EXTENSION);
		$theOriginalFile = $image['tmp_name'];
		$finalName = uniqid('user_img_') .  '.' . $ext;
		$theFinalFile = USER_IMAGE_PATH . $finalName;
		move_uploaded_file($theOriginalFile, $theFinalFile);
		return $finalName;
	}

?>
