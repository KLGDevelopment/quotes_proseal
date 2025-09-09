<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;

class OdooAPIController extends Controller
{
    private string $url;
    private string $db;
    private string $username;
    private string $password;

    public function __construct()
    {
        $this->url = "http://test-proseal.odoo.com";
        $this->db = "bmya-proseal-test-21360033";
        $this->username = "mauricio.marchant@klgtechnology.com";
        $this->password = "mauricio123";
    }

    /**
     * Autenticación y guardado del UID en sesión
     */
    public function start()
    {
        if (Session::has('uid')) {
            return response()->json(['status' => 'already_authenticated', 'uid' => Session::get('uid')]);
        }

        $response = $this->callOdoo("common", "login", [$this->db, $this->username, $this->password]);

        if (isset($response['result'])) {
            Session::put('uid', $response['result']);
            return response()->json(['status' => 'authenticated', 'uid' => $response['result']]);
        }

        return response()->json(['error' => 'Autenticación fallida', 'details' => $response]);
    }

    /**
     * Cargar categorías de productos
     */
    public function loadCategories()
    {
        return $this->odooSearchRead(
            "product.category",
            ["id", "name", "parent_id"]
        );
    }

    /**
     * Cargar productos
     */
    public function loadProducts()
    {
        return $this->odooSearchRead(
            "product.product",
            ["id", "name", "default_code", "type", "categ_id"]
        );
    }

    /**
     * Método genérico para llamadas execute_kw
     */
    private function odooSearchRead(string $model, array $fields, array $domain = [], array $options = [])
    {
        if (!Session::has('uid')) {
            return response()->json(['error' => 'No autenticado. Ejecuta /start primero.']);
        }

        $uid = Session::get('uid');

        $params = [
            $this->db,
            $uid,
            $this->password,
            $model,
            "search_read",
            [$domain],
            array_merge(["fields" => $fields, "limit" => 1000], $options)
        ];

        $response = $this->callOdoo("object", "execute_kw", $params);

        return response()->json($response['result'] ?? ['error' => 'Sin resultados']);
    }

    /**
     * Método base para llamar cualquier método Odoo JSON-RPC
     */
    private function callOdoo(string $service, string $method, array $args)
    {
        $payload = [
            "jsonrpc" => "2.0",
            "method" => "call",
            "params" => [
                "service" => $service,
                "method" => $method,
                "args" => $args
            ],
            "id" => rand(0, 1000000000)
        ];

        $ch = curl_init("{$this->url}/jsonrpc");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    public function syncOdooProducts()
    {
        if (!Session::has('uid')) {
            // Intenta autenticarse automáticamente
            $auth = $this->callOdoo("common", "login", [$this->db, $this->username, $this->password]);
            if (isset($auth['result'])) {
                Session::put('uid', $auth['result']);
            } else {
                return response()->json(['error' => 'No autenticado y no se pudo iniciar sesión.']);
            }
        }

        $uid = Session::get('uid');

        $params = [
            $this->db,
            $uid,
            $this->password,
            "product.product",
            "search_read",
            [[]], // sin filtros
            [
                "fields" => ["id", "name", "default_code", "type", "categ_id"],
                "limit" => 1000
            ]
        ];

        $response = $this->callOdoo("object", "execute_kw", $params);

        if (!isset($response['result']) || !is_array($response['result'])) {
            return response()->json(['error' => 'Error al obtener productos desde Odoo']);
        }

        $odooProducts = $response['result'];
        $imported = 0;

        foreach ($odooProducts as $product) {

            Product::updateOrCreate(
                ['code' => $product['default_code'] ?? null], // criterios de búsqueda
                [
                    'name'        => $product['name'],
                    'type'        => $product['type'],
                    
                    'id'     => $product['id'],
                    'odoo_category_id' => $product['categ_id'][0],
                ]
            );
            $imported++;
        }

        return response()->json([
            'status' => 'ok',
            'message' => "Productos sincronizados correctamente.",
            'total' => $imported
        ]);
    }
}
