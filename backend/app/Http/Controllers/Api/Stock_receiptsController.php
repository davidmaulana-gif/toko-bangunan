<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Stock_receiptsController extends Controller
{
    public function createStockReceipts(Request $request)
    {
        try {
            $request->validate([
                'sales_id' => 'required|integer|exists:sales,id',
                'products' => 'required|array|min:1',
                'products.*.product_id' => 'required|integer|exists:products,id',
                'products.*.quantity' => 'required|integer|min:1',
                'products.*.buying_price' => 'required|integer|min:0',
            ]);

            DB::transaction(function () use ($request, &$stockReceipt) {

                // Generate code RCV001
                $lastCode = DB::table('stock_receipts')
                    ->orderByDesc('code')
                    ->value('code');

                $sequence = $lastCode
                    ? ((int) substr($lastCode, 3)) + 1
                    : 1;

                $code = 'RCV' . str_pad($sequence, 3, '0', STR_PAD_LEFT);

                // Insert header
                $stockReceiptId = DB::table('stock_receipts')
                    ->insertGetId([
                        'code' => $code,
                        'sales_id' => $request->sales_id,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                // Insert detail + tambah stok
                foreach ($request->products as $item) {

                    $product = DB::table('products')
                        ->where('id', $item['product_id'])
                        ->whereNull('deleted_at')
                        ->first();

                    if (!$product) {
                        throw new \Exception('Produk tidak ditemukan.');
                    }

                    DB::table('stock_receipt_details')
                        ->insert([
                            'stock_receipt_id' => $stockReceiptId,
                            'product_id' => $product->id,
                            'quantity' => $item['quantity'],
                            'buying_price' => $item['buying_price'],
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);

                    DB::table('products')
                        ->where('id', $product->id)
                        ->increment('total_unit', $item['quantity']);
                }

                $stockReceipt = DB::table('stock_receipts')
                    ->where('id', $stockReceiptId)
                    ->first();

                // dd($stockReceipt);  
            });

            return response()->json([
                'status' => true,
                'message' => 'Barang berhasil diterima.',
                'data' => $stockReceipt
            ], 201);
        } catch (\Exception $error) {

            return response()->json([
                'status' => false,
                'message' => 'Barang gagal diterima.',
                'error' => $error->getMessage()
            ], 500);
        }
    }
}
