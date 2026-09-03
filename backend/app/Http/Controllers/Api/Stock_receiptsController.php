<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Stock_receiptsController extends Controller
{
    public function createStockReceipts(Request $request)
    {
        try {
            $request->validate([
                'sales_id' => 'required|integer|exists:sales,id',
                'products' => 'required|array',
                'products.*.product_id' => 'required|integer|exists:product_id,id',
                'products.*.quantity' => 'required|integer',
                'products.*.buying_price=>' => 'required|integer'
            ]);

            DB::transaction(function () use ($request, &$stockReceipt) {

                $uuid = Str::uuid();

                $lastcode = DB::table('stock_receipts')
                    ->orderByDesc('code')
                    ->value('code');

                $sequence = $lastcode
                    ? ((int)substr($lastcode, 3)) + 1
                    : 1;

                $code = 'RCV' . str_pad($sequence, 3, '0');

                $header = DB::table('stock_receipts')->insertGetId([
                    'uuid' => $uuid,
                    'code' => $code,
                    'sales_id' => $request->sales_id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                foreach ($request->products as $barang) {

                    $uuidDetail = Str::uuid();

                    $product = DB::table('products')
                        ->where('id', $barang['product_id'])
                        ->whereNull('deleted_at')
                        ->first();

                    if (!$product) {
                        throw new \Exception('product tidak di temukan.');
                    }

                    DB::table('stock_receipt_details')->insert([
                        'uuid' => $uuidDetail,
                        'stock_receipt_id' => $header,
                        'product_id' => $product->id,
                        'quantity' => $barang['quantity'],
                        'buying_price' => $barang['buying_price'],
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                    DB::table('products')
                        ->where('id', $product->id)
                        ->increment('total_unit', $barang['quantity']);

                    $stockReceipt = DB::table('stock_receipts')
                        ->where('id', $header)
                        ->first();
                }

                return response()->json([
                    'message' => 'barang berhasil diterima.',
                    'data' => $stockReceipt
                ]);
            });
        } catch (\Exception $errorStock) {
            return response()->json([
                'message' => 'data gagal di tambahkan.',
                'error' => $errorStock->getMessage()
            ]);
        }
    }
}
