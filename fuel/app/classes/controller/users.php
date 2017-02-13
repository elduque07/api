<?php

use \Firebase\JWT\JWT;

class Controller_users extends Controller_Rest
{
	private $key = 'sebas007';
	private $algorithm = array('HS256');

	private function error($code = 500, $mensaje = 'Error del servidor', $descripcion = 'Error espontaneo')
	{
		return [
					'code' => $code, 
					'mensaje' => $mensaje,
					'descripcion' => $descripcion,
				];
	}

	private function errorAuth()
	{
		return [
					'code' => 401, 
					'mensaje' => 'Error de autenticación',
					'descripcion' => 'Usuario o contraseña incorrectos',
				];
	}

		private function exito($code = 200, $mensaje = 'Exito', $descripcion = "Tarea realizada con exito")
	{
		return [
					'code' => $code, 
					'mensaje' => $mensaje,
					'descripcion' => $descripcion
				];
	}

    public function post_crear()
    {
        //instancia del modelo users
        $user = new Model_users();

        $input = Input::all();
        $username2 = $input['username'];
        $email2 = $input['email'];
        $password2 = $input['password'];
        $foto2 = $input['foto'];


        $user->username = Input::post('username');
        $user->email = Input::post('email');
        $user->password = Input::post('password');
        $user->foto = Input::post('foto');

        if (empty($username2) or empty($email2) or empty($password2)){

            return $this->error(404, 'Error', 'klnkj.');
        }

        if ($username2 == Input::post('username')){

            return $this->error(404, 'Error', 'Usuario ya creado.');

        }
            //control de errores
            //si alguno de los campos username, password o email viene vacio....
                try
                {
                    $user->save();

                    return $this->exito(200, 'Exito', 'Usuario creado.');
                }
                catch(exception $e)
                {

                    return $this->error(404, 'Error', 'Email ya registrado.');
                    print('Email ya registrado');
                }

    
            } 



	// FUNCION QUE COMPRUEBA DATOS DE ACCESO CON LOS DE LA BBDD Y GENERA TOKEN
    public function post_login() {

        //Variable que guarda el username extraido de la BBDD, en base al modelo
        $user = Model_users::find('all', array(
                'where' => array(
                    array('username', Input::post('username')),
                    )
                ));

        //VERIFICACION DE DE ENVIO DE DATOS: Si mediante post mandamos usuario, con foreach recorremos los campoos de id, username y pass y los guardamos en variables.
        if ( ! empty($user) ) 
        {

            foreach ($user as $verif => $value)
            {
                $id = $user[$verif]->id;
                $username = $user[$verif]->username;
                $password = $user[$verif]->password;
            }

        }
        //Si no enviamos ningun dato de usuario, generamos error
        else
        {
        	return $this->error(404, 'Página no encontrada', 'Rellene los campos vacios.');
            //return $this->errorAuth();
        }

        /////////////

        //VERIFICACION DE DATOS VALIDOS:

        //Si user y pass son iguales a los introducidos por el cliente mediante post
        if ($username == Input::post('username') and $password == Input::post('password'))
        {
            //Generamos el token que sera un array con los 3 datos
            $token = array(
                "id" => $id,
                "username" => $username,
                "password" => $password
            ); 

            //Codificacion del token utilizando dependencia JWT
            $jwt = JWT::encode($token, $this->key);

            //Return de codigo 200 y del token generado con el encode:
            return [

                'code' => 200,
                'token' => $jwt

            ];
        }

        //Si user y pass enviados por el cliente no coinciden con BBDD:
        else {

            return $this->errorAuth();
        }
    }

    private function verificarUser(){

        $header = apache_request_headers();
        $jwt = $header["auth"]; 
      
        if ( $jwt != null ) {

            try{
            $decoded = JWT::decode($jwt, $this->key, $this->algorithm); 
            }catch(exception $e){
                print('fallo del token');
                return false; 
            }
            $token = (array)$decoded;

            $user = Model_users::find('all', array(
                'where'   => array(
                    array('username', $token["username"]),

                ),
            ));
            return true;

        } 
        else
        {
            echo "No se encuentra el token.";
            return false;
        }

    }

    public function get_users()
	{

		if ($this->verificarUser()) 
		{
			$users = Model_users::find('all');
			return $users;
		}
		else
		{
			return $this->errorAuth();
		}
	}

	public function get_userInfo() {

        $verificacion = $this->verificarUser();
        //var_dump($verificacion);

        if ($verificacion == true ) {

            $header = apache_request_headers();
            //var_dump($header);


            $guardarID = $header["id"];

            //var_dump($guardarID);

            $user = Model_users::find('all', array(
            'where' => array(
            array('id', $guardarID))));

            if($user!=null){
                return $user; 
            } else{
                return $this->error(404, 'Error', 'Usuario no encontrtado con dicho ID.');
              
            }
        }
        else{

            return $this->errorAuth();
        }

    }


  public function post_editUser() {

        $verificacion = $this->verificarUser();
        //var_dump($verificacion);

        if ($verificacion == true ) {

            //$header = apache_request_headers();
            //var_dump($header);

            $guardarID = Input::post("id");         
            $guardarUser = Input::post("username");
            $guardarEmail = Input::post("email");
            $guardarPass = Input::post("password");
            $guardarFoto = Input::post("foto");

            //var_dump($guardarID);

            //$user = new Model_users2();
            $user = Model_users::find('all', array(
            'where' => array(
                array('id', $guardarID))));

            if($user!=null){

                $user[$guardarID]->set(array(        // POR QUE EL EL INDEX DEL ARRAY ES EL 4?
                'username'  => $guardarUser,
                'password' => $guardarPass,
                'email' => $guardarEmail,
                'foto' => $guardarFoto
            ));

            $user[$guardarID]->save();
                

                return $user; 

            } else{
                return $this->error(404, 'Error', 'Usuario no encontrtado con dicho ID.');
            }
        }
        else{

            return $this->errorAuth();
        }

    }

 public function post_deleteUser() {

        $verificacion = $this->verificarUser();
        //var_dump($verificacion);

        if ($verificacion == true ) {

            $guardarID = Input::post("id");
           
            $user = Model_users::find('all', array(
            'where' => array(
                array('id', $guardarID))));


            if($user!=null){

                $user[$guardarID]->delete();

                //$user[$guardarID]->save();
                

                return $user; 
                
            } else{
                return  [

                'No existe usuario con ese ID'

                ];
            }
        }
        else{

            return $this->errorAuth();
        }

    }
	

	     	

    }


    /*
    //Funcion para registrar usuarios
    public function post_registro() {

        $user = new Model_users();

        $user->username = Input::post('username');
        $user->email = Input::post('email');
        $user->password = Input::post('password');
        $user->foto = Input::post('foto');
        $user->save();

        return $this->response(
        [
            'Usuario' => 'creado',
        ]);

    }
	*/

