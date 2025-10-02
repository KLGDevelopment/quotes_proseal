<?php

namespace App\Http\Controllers;

use App\Models\BranchOffice;
use App\Models\Contact;
use App\Models\Customer;
use App\Models\Division;
use App\Models\Product;
use App\Models\Quote;
use App\Services\OdooConnectorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class OdooSyncController extends Controller
{
    public function __construct(private OdooConnectorService $odoo, private OdooReadController $odooReader) {}
    

    public function syncBranchOffices(): JsonResponse
    {
       
        $odooItems = $this->odooReader->readBranchOffices();
 
        if (empty($odooItems)) {
            return response()->json(['error' => 'No se pudieron obtener sucursales desde Odoo.']);
        }
        
        $syncedOdooIds = [];
        $imported = 0;
        $updated = 0;
        
        foreach ($odooItems as $item) {
            if (!isset($item['name'])) continue;
            
            $itemModel = BranchOffice::updateOrCreate(
                ['name' => $item['name']],
                [
                    'odoo_id'          => $item['id']
                    ]
                );
                
                $itemModel->wasRecentlyCreated ? $imported++ : $updated++;
                
                $syncedOdooIds[] = $item['id'];
            }
            
            // 🧹 Eliminar productos locales que ya no existen en Odoo
            $deleted = BranchOffice::whereNotIn('odoo_id', $syncedOdooIds)
            ->whereNotNull('odoo_id')
            ->delete();
            
            return response()->json([
                'status'  => 'ok',
                'message' => 'Sincronización completa',
                'imported' => $imported,
                'updated'  => $updated,
                'deleted'  => $deleted,
                'total'    => count($odooItems),
            ]);
    }

    public function syncDivisions(): JsonResponse
    {

        $odooModelSPA = "Divisiones";
        $function = "readDivisions";
        $odooItems = $this->odooReader->{$function}();
 
        if (empty($odooItems)) {
            return response()->json(['error' => "No se pudieron obtener $odooModelSPA desde Odoo."]);
        }

        $syncedOdooIds = [];
        $imported = 0;
        $updated = 0;
        
        foreach ($odooItems as $item) {
            if (!isset($item['name'])) continue;
            
            $itemModel = Division::updateOrCreate(
                ['name' => $item['name']],
                [
                    'odoo_id'          => $item['id']
                    ]
                );
                
                $itemModel->wasRecentlyCreated ? $imported++ : $updated++;
                
                $syncedOdooIds[] = $item['id'];
            }
                 
            // 🧹 Eliminar productos locales que ya no existen en Odoo
            $deleted = Division::whereNotIn('odoo_id', $syncedOdooIds)
            ->whereNotNull('odoo_id')
            ->delete();
            
            return response()->json([
                'status'  => 'ok',
                'message' => 'Sincronización completa',
                'imported' => $imported,
                'updated'  => $updated,
                'deleted'  => $deleted,
                'total'    => count($odooItems),
            ]);
    }

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
                    'name'    => strtoupper($customerData['name']),
                    'vat'     => strtoupper($customerData['vat']),
                    'email'   => is_string($customerData['email']) ? strtolower($customerData['email']) : null,
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
                            'email' => is_string($contactData['email']) ? strtolower($contactData['email']) : null,
                        ],
                        [
                            'odoo_customer_id' => $customer->odoo_id,
                            'name'               => strtoupper($contactData['name']),
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
        
    public function syncSaleOrder(Quote $quote): JsonResponse
    {
        $partnerId = $quote->customer->odoo_id;

        if (!$partnerId) {
            return response()->json([
                'status' => 'error',
                'message' => 'El cliente no tiene odoo_id asignado.',
            ], 400);
        }

        $orderLines = [];

        foreach ($quote->details as $detail) {
            // Línea tipo sección
            $orderLines[] = [0, 0, [
                'display_type' => 'line_section',
                'name' => $detail->item ?? 'SECCIÓN',
            ]];

            foreach ($detail->lines as $line) {
                if (!$line->product || !$line->product->odoo_id) {
                    continue;
                }

                // Construye el nombre de la línea según lo esperado por Odoo (sale.order.line no admite 'description')
                $lineName = (string) ($line->product->name ?? '');
                if (!empty($line->description)) {
                    $lineName = trim($lineName !== '' ? ($lineName . ' - ' . $line->description) : (string) $line->description);
                }
                if ($lineName === '') {
                    $lineName = 'ITEM';
                }

                $orderLine = [
                    'product_id'      => $line->product->odoo_id,
                    'product_uom_qty' => $line->quantity ?? 1,
                    'price_unit'      => $line->sale_value ?? 0,
                    'name'            => $lineName,
                ];

                /**
                 * --- SUCURSAL / DIVISIÓN como CUENTAS ANALÍTICAS ---
                 * Asumo que $quote->branchOffice->odoo_id y $quote->division->odoo_id
                 * son IDs de account.analytic.account.
                 * Si tienes solo tags, ver bloque “Etiquetas analíticas” más abajo.
                 */
                $distribution = [];

                if (!empty($quote->branchOffice) && !empty($quote->branchOffice->odoo_id)) {
                    // clave como string
                    $distribution[(string) $quote->branchOffice->odoo_id] = 50.0;
                }
                if (!empty($quote->division) && !empty($quote->division->odoo_id)) {
                    $distribution[(string) $quote->division->odoo_id] = isset($distribution) ? 50.0 : 100.0;
                }

                // Si sólo hay una dimensión, que reciba el 100%
                if (count($distribution) === 1) {
                    $k = array_key_first($distribution);
                    $distribution[$k] = 100.0;
                }

                // Normaliza para que sume 100
                if (!empty($distribution)) {
                    $sum = array_sum($distribution);
                    if ($sum > 0) {
                        foreach ($distribution as $k => $v) {
                            $distribution[$k] = round(($v / $sum) * 100.0, 6);
                        }
                        $orderLine['analytic_distribution'] = $distribution; // <- Odoo 18
                    }
                }

                /**
                 * --- ETIQUETAS ANALÍTICAS (opcional) ---
                 * Si branch/division son etiquetas y ya tienes sus IDs de account.analytic.tag,
                 * agrégalas aquí. (Si no las tienes, podemos añadir helpers para buscarlas/crearlas.)
                 */
                $tagIds = [];
                if (!empty($quote->branchOffice_tag_odoo_id)) {
                    $tagIds[] = (int) $quote->branchOffice_tag_odoo_id;
                }
                if (!empty($quote->division_tag_odoo_id)) {
                    $tagIds[] = (int) $quote->division_tag_odoo_id;
                }
                if (!empty($tagIds)) {
                    $orderLine['analytic_tag_ids'] = [[6, 0, array_values(array_unique($tagIds))]];
                }

                $orderLines[] = [0, 0, $orderLine];
            }
        }

        if (empty($orderLines)) {
            return response()->json([
                'status' => 'error',
                'message' => 'No hay líneas válidas para inyectar en Odoo.',
            ], 400);
        }

        $orderData = [
            'partner_id'       => $partnerId,
            'client_order_ref' => $quote->code ?? 'COT-' . $quote->id,
            'date_order'       => now()->toDateString(),
            'order_line'       => $orderLines,
        ];

        try {
            Log::debug('Enviando orden a Odoo', [
                'quote_id'   => $quote->id,
                'order_data' => $orderData,
            ]);
            $saleOrderId = $this->odoo->callModelMethod('sale.order', 'create', [$orderData]);

            return response()->json([
                'status'  => 'ok',
                'message' => 'Orden de venta creada en Odoo',
                'order_id'=> $saleOrderId,
            ]);
        } catch (\Exception $e) {
            Log::error('Error al crear orden en Odoo', [
                'quote_id'   => $quote->id,
                'order_data' => $orderData,
                'exception'  => $e,
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }



}
