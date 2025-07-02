<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class InvoiceController extends Controller
{
    protected InvoiceService $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'status', 'date_from', 'date_to', 'sort_by', 'sort_dir', 'per_page']);
        $invoices = $this->invoiceService->getFilteredInvoices($filters);

        return Inertia::render('Invoices/Index', [
            'invoices' => $invoices,
            'filters' => $filters,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $customers = Customer::select('id', 'first_name', 'last_name', 'email')->get();
        $products = Product::with('category', 'unitMeasure')
            ->select('id', 'sku', 'name', 'price', 'stock', 'category_id', 'unit_measure_id')
            ->where('stock', '>', 0)
            ->get();

        return Inertia::render('Invoices/Create', [
            'customers' => $customers,
            'products' => $products,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'date' => 'required|date',
            'status' => ['required', Rule::in(['pending', 'paid', 'canceled'])],
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'nullable|numeric|min:0',
        ]);

        try {
            $invoice = $this->invoiceService->createInvoice($request->all());

            return redirect()->route('invoices.show', $invoice)
                ->with('success', 'Invoice created successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice)
    {
        $invoice->load(['customer', 'user', 'items.product.category', 'items.product.unitMeasure']);

        return Inertia::render('Invoices/Show', [
            'invoice' => $invoice,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Invoice $invoice)
    {
        $invoice->load(['customer', 'user', 'items.product']);
        $customers = Customer::select('id', 'first_name', 'last_name', 'email')->get();
        $products = Product::with('category', 'unitMeasure')
            ->select('id', 'sku', 'name', 'price', 'stock', 'category_id', 'unit_measure_id')
            ->get();

        return Inertia::render('Invoices/Edit', [
            'invoice' => $invoice,
            'customers' => $customers,
            'products' => $products,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Invoice $invoice)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'date' => 'required|date',
            'status' => ['required', Rule::in(['pending', 'paid', 'canceled'])],
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'nullable|numeric|min:0',
        ]);

        try {
            $this->invoiceService->updateInvoice($invoice, $request->all());

            return redirect()->route('invoices.show', $invoice)
                ->with('success', 'Invoice updated successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        try {
            $this->invoiceService->deleteInvoice($invoice);

            return redirect()->route('invoices.index')
                ->with('success', 'Invoice deleted successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
