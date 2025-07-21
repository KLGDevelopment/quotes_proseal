<?php

namespace App\Services;

use Illuminate\Support\Facades\Session;

class OdooConnectorService
{
    private string $url;
    private string $db;
    private string $username;
    private string $password;

    public function __construct()
    {
        $this->url = config('services.odoo.url', 'http://test-proseal.odoo.com');
        $this->db = config('services.odoo.db', 'bmya-proseal-test-21360033');
        $this->username = config('services.odoo.username', 'mauricio.marchant@klgtechnology.com');
        $this->password = config('services.odoo.password', 'mauricio123');
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
}
