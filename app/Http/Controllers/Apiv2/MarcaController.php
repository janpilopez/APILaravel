<?php

namespace App\Http\Controllers\Apiv2;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Responses\ApiResponse;
use App\Models\Apiv2\Marca;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MarcaController extends Controller
{
    public function index()
    {
        try {
             return ApiResponse::success(Marca::all());
        }
        catch (Exception $e) {
            return ApiResponse::error($e->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'nombre' => 'required|unique:marcas|string|max:255',
                'descripcion' => 'required|string|max:255'
            ]);
            $marca = Marca::create($request->all());
            return ApiResponse::success($marca, 201);
        }
        catch (ValidationException $e) {#Excepciones de validación
            return ApiResponse::error("Error en la validación", 422); #TODO: 422 CONTENIDO NO PROCESADO
        }
    }

    public function show($id)
    {
        try {
            $marca = Marca::findOrfail($id);
            return ApiResponse::success($marca);
        } catch (ModelNotFoundException $e) { //Excepciones de modelo no encontrado
            return ApiResponse::error('Marca no encontrada', 404); #TODO: 404 CONTENIDO O RECURSO NO ENCONTRADO
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $marca = Marca::findOrfail($id);
            $request->validate([
                'nombre' => ['required', 'string', 'max:255', Rule::unique('marcas')->ignore($marca)],//ignoremos el propio nombre de la marca, ya que al actualizar puede habre error de actualizacion si solo actuaizamos descripcion porque verificara errorneamente el nuevo dato ingresado o demas
                'descripcion' => 'required|string|max:255'
            ]);
            $marca->update($request->all());
            return ApiResponse::success($marca);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Marca no encontrada', 404);
        } catch (Exception $e){
            return ApiResponse::error($e->getMessage(), 422);
        }
    }

    public function destroy($id)
    {
        try {
            $marca = Marca::findOrfail($id);
            $marca->delete();
            return ApiResponse::success($marca, 200, 'Marca eliminada');
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Marca no encontrada', 404);
        } catch (Exception $e){
            return ApiResponse::error($e->getMessage(), 422);
        }
    }

    public function productosPorMarca($id)
    {
        try {
            $marca = Marca::with('productos')->findOrfail($id);
            return ApiResponse::success($marca);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Marca no encontrada', 404);
        }
    }
}
