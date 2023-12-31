<?php

namespace App\Http\Controllers;

use App\Models\UsuarioReceta;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Usuario;
use App\Models\UsuarioOferta;
use Exception;
use Tymon\JWTAuth\Facades\JWTAuth;

class UsuarioController extends Controller
{


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
            $usuario->password = hash('sha256', $request->password);
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

    public function login(Request $request)
    {

        $usuarioEncontrado = Usuario::where('email', $request->email)->first();
        if (is_null($usuarioEncontrado)) {
            //return response()->json(['error' => 'Not found'], 401);
            return json_encode("no encontrado");
        } else {

            if (hash('sha256', $request->password) == $usuarioEncontrado->password) {
                //para modificar el timeout del token al crearse podria usarse
                //$token = auth()->setTTL(7200)->login($usuarioEncontrado);
                $token = auth()->login($usuarioEncontrado);
                return $this->respondWithToken($token);
            } else {
                //return response()->json(['error' => 'Unauthorized'], 401);
                return json_encode("incorrecto");
            }
        }
    }

    public function refresh()
    {
        $usuario = JWTAuth::user();
        $token = auth()->login($usuario);
        return $this->respondWithToken($token);
    }

    public function ObtenerUnUsuario()
    {
        $usuario = JWTAuth::user();
        return json_encode($usuario);
    }

    public function ActualizarDatosUsuario(Request $request)
    {

        $usuario = JWTAuth::user();
        $usuarioEncontrado = Usuario::find($usuario->id);

        $usuarioEncontrado->nombre = $request->nombre;
        $usuarioEncontrado->email = $request->email;
        $usuarioEncontrado->password = hash('sha256', $request->password);
        $usuarioEncontrado->subscripcion = $request->subscripcion;
        $usuarioEncontrado->img = $request->img;
        $usuarioEncontrado->es_administrador = $request->es_administrador;
        $usuarioEncontrado->save();

        $token = auth()->login($usuarioEncontrado);
        return $this->respondWithToken($token);
    }

    public function ComprobarContrasena(Request $request)
    {
        $mensaje = 'mensaje';
        $usuario = JWTAuth::user();
        $usuarioEncontrado = Usuario::find($usuario->id);
        if (!is_null($usuarioEncontrado)) {
            if (hash('sha256', $request->password) == $usuarioEncontrado->password) {
                $mensaje = $usuarioEncontrado->password;
            } else {
                $mensaje = 'incorrecto';
            }
        } else {
            $mensaje = "Usuario no encontrado";
        }
        return json_encode($mensaje);
    }

    public function obtenerUsuarios() // Funcion para obtener los usuarios de la tabla usuarios es admin
    {
        $usuarios = Usuario::get();

        $ofertasVisualziaciones = UsuarioOferta::get();

        $idsUsuarios = $ofertasVisualziaciones->groupBy('id_usuario');
        $visitas = 0;
        $visitasPorUsuario = [];

        foreach ($idsUsuarios as $idUsuario => $ofertasUsuario) {
            foreach ($ofertasUsuario as $oferta) {
                $visitas += $oferta->visitas;
            }
            $visitasPorUsuario[$idUsuario] = $visitas;
            $visitas = 0;
        }

        $recetasFavoritas = UsuarioReceta::get();
        $idsUsuariosFavoritos = $recetasFavoritas->groupBy('id_usuario');
        $favoritos = 0;
        $favoritosPorUsuario = [];

        foreach ($idsUsuariosFavoritos as $idFavorita => $idUsuario) {
            foreach ($idUsuario as $recetaFav) {
                $favoritos++;
            }
            $favoritosPorUsuario[$idFavorita] = $favoritos;
            $favoritos = 0;
        }


        return [$usuarios, $visitasPorUsuario, $favoritosPorUsuario];
    }

    public function obtenerTiposUsuarios() {

        $numUsuariosRegistrados = count(Usuario::where('es_administrador', 0)->where('subscripcion', 0)->get());
        $numUsuariosSubscriptos = count(Usuario::where('es_administrador', 0)->where('subscripcion', 1)->get());

        return [$numUsuariosRegistrados, $numUsuariosSubscriptos];
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
            //'expires_in' => auth()->factory()->getTTL() * 60
            'expires_in' => auth()->factory()->getTTL() * 1
        ]);
    }
}
