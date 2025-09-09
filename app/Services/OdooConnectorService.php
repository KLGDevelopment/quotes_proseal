<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class OdooConnectorService
{
    private string $url;
    private string $db;
    private string $username;
    private string $password;

    public function __construct()
    {
        $this->url = config('services.odoo.url', env('ODOO_URL'));
        $this->db = config('services.odoo.db', env('ODOO_DB'));
        $this->username = config('services.odoo.username', env('ODOO_USERNAME'));
        $this->password = config('services.odoo.password', env('ODOO_PASSWORD'));
    }

    public function authenticate(): bool
    {
        if (Session::has('uid')) return true;

        $response = $this->callOdoo('common', 'login', [$this->db, $this->username, $this->password]);

        if (isset($response['result'])) {
            Session::put('uid', $response['result']);
            return true;
        }

        return false;
    }

    public function searchRead(string $model, array $fields, array $domain = [], array $options = []): array
    {
        if (!$this->authenticate()) {
            return [];
        }

        $uid = Session::get('uid');

        $params = [
            $this->db,
            $uid,
            $this->password,
            $model,
            "search_read",
            [$domain],
            array_merge(["fields" => $fields], $options)
        ];

        $response = $this->callOdoo("object", "execute_kw", $params);

        return $response['result'] ?? [];
    }

    private function callOdoo(string $service, string $method, array $args): array
    {
        $payload = [
            "jsonrpc" => "2.0",
            "method" => "call",
            "params" => compact('service', 'method', 'args'),
            "id" => rand(0, 1000000000)
        ];

        $ch = curl_init("{$this->url}/jsonrpc");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true) ?? [];
    }

    public function callModelMethod(string $model, string $method, array $args = [])
    {
        if (!$this->authenticate()) {
            throw new \Exception("No se pudo autenticar con Odoo.");
        }

        $uid = Session::get('uid');

        $params = [
            $this->db,
            $uid,
            $this->password,
            $model,
            $method,
            $args,
        ];

        $response = $this->callOdoo("object", "execute_kw", $params);
        Log::error($response);
        if (isset($response['error'])) {
            throw new \Exception($response['error']['message'] ?? 'Error desconocido al llamar a Odoo.');
        }

        return $response['result'] ?? null;
    }

}
