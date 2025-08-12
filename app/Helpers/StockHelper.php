<?php

namespace App\Helpers;

use App\Models\Invoice;

class StockHelper
{
    /**
     * Valida la disponibilidad de stock para todos los productos de una factura.
     *
     * @param Invoice $invoice Factura a validar.
     * @return array Resultado con éxito y productos sin stock suficiente.
     */
    public static function validateStockAvailability(Invoice $invoice): array
    {
        $unavailableProducts = [];
        $allStockAvailable = true;

        foreach ($invoice->items as $item) {
            $product = $item->product;
            if (!$product) {
                $unavailableProducts[] = [
                    'product_id' => $item->product_id,
                    'product_name' => 'Product not found',
                    'product_sku' => null,
                    'required_quantity' => (int) $item->quantity,
                    'available_stock' => 0,
                    'missing_stock' => (int) $item->quantity
                ];
                $allStockAvailable = false;
            } elseif ($product->stock < $item->quantity) {
                $unavailableProducts[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name ?? 'Product without name',
                    'product_sku' => $product->sku ?? null,
                    'required_quantity' => (int) $item->quantity,
                    'available_stock' => (int) $product->stock,
                    'missing_stock' => (int) ($item->quantity - $product->stock)
                ];
                $allStockAvailable = false;
            }
        }
        return [
            'success' => $allStockAvailable,
            'unavailable_products' => $unavailableProducts
        ];
    }

    /**
     * Formatea el mensaje de error de stock insuficiente para mostrarlo en la UI.
     *
     * @param array $errorData Datos estructurados del error de stock.
     * @return string Mensaje legible para el usuario.
     */
    public static function formatStockErrorMessage(array $errorData): string
    {
        $message = "Cannot change invoice #{$errorData['invoice_id']} status for customer {$errorData['customer']} due to insufficient stock:\n\n";

        foreach ($errorData['unavailable_products'] as $product) {
            $message .= "• {$product['product_name']}: ";
            $message .= "Required {$product['required_quantity']}, ";
            $message .= "available {$product['available_stock']}, ";
            $message .= "missing {$product['missing_stock']}\n";
        }

        return $message;
    }
}
