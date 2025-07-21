<?php

namespace App\Http\Controllers;

use App\Services\OdooConnectorService;
use Illuminate\Http\JsonResponse;

class OdooReadController extends Controller
{
    public function __construct(private OdooConnectorService $odoo) {}

    /**
     * Lector genérico de modelos desde Odoo.
     */
    public function readModel(string $model, array $fields, array $domain = []): array
    {
        return $this->odoo->searchRead($model, $fields, $domain);
    }

    /**
     * Lectura de productos (usa el método genérico).
     */
    public function readProducts(): array
    {
        return $this->readModel(
            'product.product', 
            ['id', 'name', 'default_code', 'type', 'categ_id'],
        );
    }

    /**
     * Lectura de clientes y contactos (con postprocesamiento).
     */
    public function readCustomers(): array
    {
        $partners = $this->readModel(
            'res.partner',
            ['id', 'vat', 'name', 'email', 'phone', 'is_company', 'parent_id', 'customer_rank'],
            [['customer_rank', '>', 0]]
        );

        $companies = [];
        $contacts = [];

        foreach ($partners as $partner) {
            if ($partner['is_company']) {
                $companies[$partner['id']] = [
                    'id' => $partner['id'],
                    'vat' => $partner['vat'],
                    'name' => $partner['name'],
                    'email' => $partner['email'] ?? null,
                    'phone' => $partner['phone'] ?? null,
                    'contacts' => [],
                ];
            } else {
                $parentId = $partner['parent_id'][0] ?? null;
                if ($parentId) {
                    $contacts[] = [
                        'id' => $partner['id'],
                        'name' => $partner['name'],
                        'email' => $partner['email'] ?? null,
                        'phone' => $partner['phone'] ?? null,
                        'company_id' => $parentId,
                    ];
                }
            }
        }

        // Agrupa contactos por empresa
        foreach ($contacts as $contact) {
            if (isset($companies[$contact['company_id']])) {
                $companies[$contact['company_id']]['contacts'][] = $contact;
            }
        }

        return array_values($companies);
    }
}
