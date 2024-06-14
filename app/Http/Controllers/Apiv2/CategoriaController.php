<?php

namespace App\Http\Controllers\Apiv2;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Responses\ApiResponse;
use App\Models\Apiv2\Categoria;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CategoriaController extends Controller
{
    #TODO: FORMATO DE RESPUESTA DE API SE MANEJA POR LO GENERAL EL FORMATO: MESSAGE, STATUSCODE, ERROR, DATA
    public function index()
    {
        try {
             return ApiResponse::success(Categoria::all());
        }
        catch (Exception $e) {
            return ApiResponse::error($e->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'nombre' => 'required|unique:categorias|string|max:255',
                'descripcion' => 'required|string|max:255'
            ]);
            $categoria = Categoria::create($request->all());
            return ApiResponse::success($categoria, 201);
        }
        catch (ValidationException $e) {#Excepciones de validación
            return ApiResponse::error("Error en la validación", 422); #TODO: 422 CONTENIDO NO PROCESADO
        }
    }

    public function show($id)
    {
        try {
            $categoria = Categoria::findOrfail($id);
            return ApiResponse::success($categoria);
        } catch (ModelNotFoundException $e) { //Excepciones de modelo no encontrado
            return ApiResponse::error('Categoria no encontrada', 404); #TODO: 404 CONTENIDO O RECURSO NO ENCONTRADO
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $categoria = Categoria::findOrfail($id);
            $request->validate([
                'nombre' => ['required', 'string', 'max:255', Rule::unique('categorias')->ignore($categoria)],//ignoremos el propio nombre de la categoria, ya que al actualizar puede habre error de actualizacion si solo actuaizamos descripcion porque verificara errorneamente el nuevo dato ingresado o demas
                'descripcion' => 'required|string|max:255'
            ]);
            $categoria->update($request->all());
            return ApiResponse::success($categoria);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Categoria no encontrada', 404);
        } catch (Exception $e){
            return ApiResponse::error($e->getMessage(), 422);
        }
    }

    public function destroy($id)
    {
        try {
            $categoria = Categoria::findOrfail($id);
            $categoria->delete();
            return ApiResponse::success($categoria, 200, 'Categoria eliminada');
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Categoria no encontrada', 404);
        } catch (Exception $e){
            return ApiResponse::error($e->getMessage(), 422);
        }
    }

    public function productosPorCategoria($id)
    {
        try {
            $categoria = Categoria::with('productos')->findOrfail($id);
            return ApiResponse::success($categoria);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Categoria no encontrada', 404);
        }
    }
}
