<?php

namespace App\DTOs;

/**
 * DTO para la edición de cotizaciones de facturas.
 */
class InvoiceEditDTO
{
    /** @var array Datos de la factura */
    public array $invoice;

    /** @var array Lista de productos */
    public array $products;

    /**
     * Constructor del DTO.
     *
     * @param array $invoice
     * @param array $customers
     * @param array $products
     */
    public function __construct(array $invoice, array $products)
    {
        $this->invoice = $invoice;
        $this->products = $products;
    }

    /**
     * Devuelve el DTO como array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'invoice' => $this->invoice,
            'products' => $this->products,
        ];
    }
}
