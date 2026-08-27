<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class Sales_transactionsController extends Controller
{
    public function createSalesTransaction(Request $request)
    {
        try {

            $request->validate([
                'payment' => 'required|integer|min:0',
                'products' => 'required|array|min:1',
                'products.*.product_id' => 'required|integer|exists:products,id',
                'products.*.quantity' => 'required|integer|min:1',
            ]);

            DB::transaction(function () use ($request, &$transaction) {

                $lastCode = DB::table('sales_transactions')
                    ->orderByDesc('code')
                    ->value('code');

                $sequence = $lastCode
                    ? ((int) substr($lastCode, 3)) + 1
                    : 1;

                $code = 'TRX' . str_pad($sequence, 3, '0', STR_PAD_LEFT);

                $total = 0;
                $details = [];

                foreach ($request->products as $item) {

                    $product = DB::table('products')
                        ->where('id', $item['product_id'])
                        ->whereNull('deleted_at')
                        ->first();

                    if (!$product) {
                        throw new \Exception('Produk tidak ditemukan.');
                    }

                    if ($product->total_unit < $item['quantity']) {
                        throw new \Exception("Stok {$product->name} tidak mencukupi.");
                    }

                    $subtotal = $product->selling_price * $item['quantity'];

                    $total += $subtotal;

                    $details[] = [
                        'product_id' => $product->id,
                        'quantity' => $item['quantity'],
                        'selling_price' => $product->selling_price,
                        'subtotal' => $subtotal
                    ];
                }

                if ($request->payment < $total) {
                    throw new \Exception('Pembayaran kurang dari total belanja.');
                }

                $transactionId = DB::table('sales_transactions')
                    ->insertGetId([
                        'code' => $code,
                        'user_id' => Auth::id(),
                        'total' => $total,
                        'payment' => $request->payment,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                foreach ($details as $detail) {

                    DB::table('sales_transaction_details')
                        ->insert([
                            'sales_transaction_id' => $transactionId,
                            'product_id' => $detail['product_id'],
                            'quantity' => $detail['quantity'],
                            'selling_price' => $detail['selling_price'],
                            'subtotal' => $detail['subtotal'],
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);

                    DB::table('products')
                        ->where('id', $detail['product_id'])
                        ->decrement('total_unit', $detail['quantity']);
                }

                $transaction = DB::table('sales_transactions')
                    ->where('id', $transactionId)
                    ->first();
            });

            return response()->json([
                'status' => true,
                'message' => 'Transaksi berhasil ditambahkan.',
                'data' => $transaction
            ], 201);

        } catch (\Exception $error) {

            return response()->json([
                'status' => false,
                'message' => 'Transaksi gagal ditambahkan.',
                'error' => $error->getMessage()
            ], 500);
        }
    }
}
