<?php

namespace App\Http\Controllers\Apiv2;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Responses\ApiResponse;
use App\Models\Apiv2\Producto;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProductoController extends Controller
{
    public function index()
    {
        try {
            $producto = Producto::with('categoria', 'marca')->get();
             return ApiResponse::success($producto);
        }
        catch (Exception $e) {
            return ApiResponse::error($e->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'nombre' => 'required|unique:productos|string|max:255',
                'descripcion' => 'required|string|max:255',
                'precio' => 'required|numeric|between:0,999999.99',
                'cantidad_disponible' => 'required|integer',
                'categoria_id' => 'required|exists:categorias,id',
                'marca_id' => 'required|exists:marcas,id'
            ]);
            $producto = Producto::create($request->all());
            return ApiResponse::success($producto, 201);
        }
        catch (ValidationException $e) {#Excepciones de validación
            $errors = $e->validator->errors()->toArray();
            //Podemos usar esta forma para borrar los campos y enviarlo a manera general, para evitar que vulneren nuestra base de datos.
            //Siempre es recomendable no enviar nunca los datos de los campos si no variables o nombre globales
            if (isset($errors['categoria_id'])) {
                $errors['categoria'] = 'La categoría seleccionada no existe';
                unset($errors['categoria_id']);
            }
            return ApiResponse::error("Error en la validación", 422, $errors); #TODO: 422 CONTENIDO NO PROCESADO
        };
        
    }

    public function show($id)
    {
        try {
            $producto = Producto::findOrfail($id);
            return ApiResponse::success($producto);
        } catch (ModelNotFoundException $e) { //Excepciones de modelo no encontrado
            return ApiResponse::error('Producto no encontrado', 404); #TODO: 404 CONTENIDO O RECURSO NO ENCONTRADO
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $producto = Producto::findOrfail($id);
            $request->validate([
                'nombre' => 'required|unique:productos,nombre,'.$producto->id.'|string|max:255',
                'descripcion' => 'required|string|max:255',
                'precio' => 'required|numeric|between:0,999999.99',
                'cantidad_disponible' => 'required|integer',
                'categoria_id' => 'required|exists:categorias,id',
                'marca_id' => 'required|exists:marcas,id'
            ]);
            $producto->update($request->all());
            return ApiResponse::success($producto);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Producto no encontrado', 404);
        } catch (Exception $e){
            return ApiResponse::error($e->getMessage(), 422);
        }
    }

    public function destroy($id)
    {
        try {
            $producto = Producto::findOrfail($id);
            $producto->delete();
            return ApiResponse::success($producto, 200, 'Producto eliminado');
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Producto no encontrado', 404);
        } catch (Exception $e){
            return ApiResponse::error($e->getMessage(), 422);
        }
    }
}
