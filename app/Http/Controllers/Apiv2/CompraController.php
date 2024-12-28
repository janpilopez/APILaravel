<?php

namespace App\Http\Controllers\Apiv2;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Responses\ApiResponse;
use App\Models\Apiv2\Compra;
use App\Models\Apiv2\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CompraController extends Controller
{
    public function index(){

    }

    public function store(Request $request)
    {
        try{
            $productos = $request->productos;
            if (empty($productos)) {
                return ApiResponse::error("No se proporcionaron productos", 422);
            }
            $validator = Validator::make($request->all(), [
                'productos' => 'required|array',
                'productos.*.producto_id' => 'required|integer|exists:productos,id',//verifica que no se duplique de la tabla productos el id
                'productos.*.cantidad' => 'required|integer|min:1|max:1000',
            ]);
            if ($validator->fails()) {
                return ApiResponse::error("Datos invalidos en la lista del producto", 400, $validator->errors());
            }
            $productosIds = array_column($productos, 'producto_id');
            if (\count($productosIds) !== count(array_unique($productosIds))) {
                #example: [1, 2, 3, 4] = 4 count
                //        [1, 2, 3, 1] = 4 count    NO INGRESA A LA CONDICION
                #example: [1, 1, 3, 4] = 4 count
                //        [1, 1, 3, 1] = 4 count    INGRESA A LA CONDICION, SIEMPRE ES EL MISMO ARRAY TANTO IDS COMO UNIQUE
                #example2: [1, 1, 3, 4] = 4 count
                //         [1, 1, 3, 4] = 3 count   INGRESA A LA CONDICION, POR TANTO HAY DUPLICADOS
                return ApiResponse::error("No se permiten productos duplicados", 400);  
            }
            $totalPagar = 0;
            $compraItems = [];

            foreach ($productos as $producto) {
                //traemos la informacion porque necesitamos saber cantidad y precio, ya que desde el front solo nos envian el id del producto y la cantidad
                $infoProducto = Producto::find($producto['producto_id']);
                #existe el producto
                if (!$infoProducto) {
                    return ApiResponse::error("Producto no encontrado", 404);
                }
                #verificar disponibilidad del producto
                if ($infoProducto->cantidad_disponible < $producto['cantidad']) {
                    return ApiResponse::error("No hay suficiente stock para el producto {$infoProducto->nombre}", 400);
                }
                
                #Actualizacion de la cantidad disponible
                $infoProducto->cantidad_disponible -= $producto['cantidad'];
                $infoProducto->save();
                #Calculos
                $subtotal = $infoProducto->precio * $producto['cantidad'];
                $totalPagar += $subtotal;
                #Items de la compra
                $compraItems[] = [
                    'producto_id' => $infoProducto->id,
                    'precio' => $infoProducto->precio,
                    'cantidad' => $producto['cantidad'],
                    'subtotal' => $subtotal
                ];
                }
                #Registro en la tabla compras
                $compra = Compra::create([
                    'subtotal' => $totalPagar,
                    'total' => $totalPagar //si fuera iva mas el 12*
                ]);

                //Asociar los productos a la compra con sus cantidades y subotales
                #TODO: CON ATTACH MANEJAMOS LA TABLA PIVOTE O TABLAS INTERMEDIAS E INGRESAMOS LOS DATOS EN LA TABLA PIVOTE
                $compra->productos()->attach($compraItems);//no necesita el compra_id porque desde compra estamos agregando los productos
                #LA OTRA MANERA ES CREARLA MANUALMENTE Y RECORRER UN CICLO FOREACH CON la  $compra->id asignada a la compra_producto(tabla_pivote) -> compra->id(compra_id) y producto->id (producto_id)
                return ApiResponse::success($compra, 201, 'Compra realizada con éxito');
        }
        catch(ValidationException $e)
        {
            return ApiResponse::error("Error en la validación", 422);
        }
    }

    public function show($id)
    {
        # code
    }
}
