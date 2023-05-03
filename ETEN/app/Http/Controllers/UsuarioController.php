<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\Usuario;
use Exception;
use Tymon\JWTAuth\Facades\JWTAuth;

class UsuarioController extends Controller
{

    public function CrearUsuario(Request $request)
    {
        $usuario = new Usuario();
        $usuario->nombre = $request->nombre;
        $usuario->apellidos = $request->apellidos;
        $usuario->email = $request->email;
        $usuario->password = $request->password;
        $usuario->save();
        return "Usuario creado";
    }



    public function login(Request $request)
    {
        $usuario = new Usuario();
        $usuarioEncontrado = Usuario::where('email', $request->email)->first();
        if (is_null($usuarioEncontrado)) {
            $usuario->nombre = "Usuario no encontrado";
        } else {

            if (sha1($request->password) == $usuarioEncontrado->password) {
                $token = auth()->login($usuarioEncontrado);
            } else {
                $usuario->nombre = "Contrasenia incorrecta";
            }
        }
        return json_encode($usuario);
    }



    public function Registro(Request $request)
    {

        $usuario = new Usuario();
        // Variable que comprueba si existe el email
        $usuarioEncontrado = Usuario::where('email', $request->email)->first();
        // Si no se ha encontrado un usuario, guarda al usuario en la base de datos
        if (is_null($usuarioEncontrado)) {

            $usuario->nombre = $request->nombre;
            $usuario->img = null;
            $usuario->email = $request->email;
            $usuario->password = sha1($request->password);
            $usuario->subscripcion = $request->subscripcion;
            $usuario->es_administrador = $request->es_administrador;
            $usuario->email = $request->email;
            $usuario->save();
        } else {
            //Si el usuario existe, el email se sustituye por este mensaje para luego comprobarlo en front
            $usuario->email = "Existente";
        }
        return json_encode($usuario);
    }



    public function ActualizarDatosUsuario(Request $request)
    {

        $usuarioEncontrado = Usuario::find($request->id);

        $usuarioEncontrado->nombre = $request->nombre;
        $usuarioEncontrado->email = $request->email;
        $usuarioEncontrado->password = sha1($request->password);
        $usuarioEncontrado->subscripcion = $request->subscripcion;
        $usuarioEncontrado->img = $request->img;
        $usuarioEncontrado->es_administrador = $request->es_administrador;
        $usuarioEncontrado->save();

        return json_encode($usuarioEncontrado);
    }


    public function RecetasUsuario($id)
    {
        $usuario = Usuario::findOrFail($id);
        $recetas = $usuario->recetas;
        return "Recetas del usuario: $recetas";
    }


    public function obtenerUsuarios()
    {
        $usuarios = Usuario::get();
        return json_encode($usuarios);
    }


    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }


    public function ObtenerUnUsuario(Request $request)
    {
        $usuario = Usuario::find($request->id);
        return json_encode($usuario);
    }


    public function ComprobarContrasena(Request $request)
    {
        $mensaje = 'mensaje';
        $usuarioEncontrado = Usuario::find($request->id);
        if (!is_null($usuarioEncontrado)) {
            if (sha1($request->password) == $usuarioEncontrado->password) {
                $mensaje = $usuarioEncontrado->password;
            } else {
                $mensaje = 'incorrecto';
            }
        } else {
            $mensaje = "Usuario no encontrado";
        }
        return json_encode($mensaje);
    }

    protected function verificacionConToken(Request $request)
    {

        if ($request->hasHeader('Authorization')) {
            // La cabecera de autorización no está presente
            $token = $request->bearerToken();
            try {
                if ($token = JWTAuth::parseToken()->authenticate()) {
                    return response()->json(['Verificado' => 'Autorizado'], 200);
                }
            } catch (Exception $e) {
                if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
                    return response()->json(['error' => 'TokenInvalidException'], 401);
                } else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
                    return response()->json(['error' => 'TokenExpiredException'], 401);
                } else if ($e instanceof \Tymon\JWTAuth\Exceptions\JWTException) {
                    return response()->json(['error' => 'JWTException'], 401);
                } else {
                    return response()->json(['error' => 'error'], 401);
                }
            }
        }
}
