<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Customer;
use App\Models\Product;
use App\Services\OdooConnectorService;
use Illuminate\Http\JsonResponse;

class OdooSyncController extends Controller
{
    public function __construct(private OdooConnectorService $odoo, private OdooReadController $odooReader) {}
    
    public function syncProducts(): JsonResponse
    {
        /*
        $odooProducts = $this->odoo->searchRead('product.product', [
            'id', 'name', 'default_code', 'type', 'categ_id'
        ]);
        */
        
        $odooProducts = $this->odooReader->readProducts();
        
        if (empty($odooProducts)) {
            return response()->json(['error' => 'No se pudieron obtener productos desde Odoo.']);
        }
        
        $syncedOdooIds = [];
        $imported = 0;
        $updated = 0;
        
        foreach ($odooProducts as $product) {
            if (!isset($product['default_code'])) continue;
            
            $productModel = Product::updateOrCreate(
                ['code' => $product['default_code']],
                [
                    'name'             => $product['name'],
                    'type'             => $product['type'],
                    'odoo_id'          => $product['id'],
                    'odoo_category_id' => $product['categ_id'][0] ?? null,
                    ]
                );
                
                $productModel->wasRecentlyCreated ? $imported++ : $updated++;
                
                $syncedOdooIds[] = $product['id'];
            }
            
            // 🧹 Eliminar productos locales que ya no existen en Odoo
            $deleted = Product::whereNotIn('odoo_id', $syncedOdooIds)
            ->whereNotNull('odoo_id')
            ->delete();
            
            return response()->json([
                'status'  => 'ok',
                'message' => 'Sincronización completa',
                'imported' => $imported,
                'updated'  => $updated,
                'deleted'  => $deleted,
                'total'    => count($odooProducts),
            ]);
    }

    public function syncCustomers(): \Illuminate\Http\JsonResponse
    {
        $odooCustomers = $this->odooReader->readCustomers();

        if (empty($odooCustomers)) {
            return response()->json(['error' => 'No se pudieron obtener clientes desde Odoo.']);
        }

        $syncedCustomerOdooIds = [];
        $syncedContactOdooIds  = [];

        $customersCreated = $customersUpdated = 0;
        $contactsCreated  = $contactsUpdated  = 0;

        foreach ($odooCustomers as $customerData) {
            $customer = Customer::updateOrCreate(
                ['odoo_id' => $customerData['id']],
                [
                    'name'    => $customerData['name'],
                    'vat'    => $customerData['vat'],
                    'email'   => is_string($customerData['email']) ? $customerData['email'] : null,
                    'phone'   => is_string($customerData['phone']) ? $customerData['phone'] : null,
                ]
            );

            $customer->wasRecentlyCreated ? $customersCreated++ : $customersUpdated++;
            $syncedCustomerOdooIds[] = $customerData['id'];

            // Procesar contactos internos
            if (!empty($customerData['contacts']) && is_array($customerData['contacts'])) {
                foreach ($customerData['contacts'] as $contactData) {
                    $contact = Contact::updateOrCreate(
                        [
                            'customer_id'        => $customer->id,
                            'email' => is_string($contactData['email']) ? $contactData['email'] : null,
                        ],
                        [
                            'odoo_customer_id' => $customer->odoo_id,
                            'name'               => $contactData['name'],
                            'phone'              => is_string($contactData['phone']) ? $contactData['phone'] : null,
                            
                            'odoo_customer_id'   => $customer->odoo_id,
                        ]
                    );

                    $contact->wasRecentlyCreated ? $contactsCreated++ : $contactsUpdated++;
                    $syncedContactOdooIds[] = $contactData['id'];
                }
            }
        }

        // 🧹 Eliminar los clientes y contactos que ya no están en Odoo
        $customersDeleted = Customer::whereNotIn('odoo_id', $syncedCustomerOdooIds)->delete();
        $contactsDeleted  = Contact::whereNotIn('odoo_customer_id', $syncedCustomerOdooIds)->delete();

        return response()->json([
            'status'            => 'ok',
            'message'           => 'Sincronización de clientes y contactos completada.',
            'customers_created' => $customersCreated,
            'customers_updated' => $customersUpdated,
            'customers_deleted' => $customersDeleted,
            'contacts_created'  => $contactsCreated,
            'contacts_updated'  => $contactsUpdated,
            'contacts_deleted'  => $contactsDeleted,
            'total_customers'   => count($odooCustomers),
        ]);
    }
        
}
