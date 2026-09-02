<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;

class ReturnController extends Controller
{
    public function createReturn(Request $request)
    {
        try {

            // 1. Validasi request
            $request->validate([
                'sales_transaction_id' => 'required|integer|exists:sales_transactions,id',
                'products' => 'required|array|min:1',
                'products.*.product_id' => 'required|integer|exists:products,id',
                'products.*.quantity' => 'required|integer|min:1',
                'products.*.condition' => 'required|in:layak,rusak',
            ]);

            DB::transaction(function () use ($request, &$returnData) {

                $totalReturn = 0;
                $details = [];
                $uuid = Str::uuid();


                foreach ($request->products as $item) {

                    $transactionDetail = DB::table('sales_transaction_details')
                        ->where('sales_transaction_id', $request->sales_transaction_id)
                        ->where('product_id', $item['product_id'])
                        ->first();

                    if (!$transactionDetail) {
                        throw new Exception('Barang tidak ada pada transaksi ini.');
                    }

                    if ($item['quantity'] > $transactionDetail->quantity) {
                        throw new Exception('Jumlah return melebihi jumlah pembelian.');
                    }

                    $subtotal = $transactionDetail->selling_price * $item['quantity'];

                    $totalReturn += $subtotal;

                    $details[] = [
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'return_price' => $transactionDetail->selling_price,
                        'condition' => $item['condition'],
                        'subtotal' => $subtotal,
                    ];
                }

                // 3. Simpan header return
                $returnId = DB::table('returns')->insertGetId([
                    'uuid' => $uuid,
                    'sales_transaction_id' => $request->sales_transaction_id,
                    'user_id' => Auth::id(),
                    'total_return' => $totalReturn,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // 4. Simpan detail return + update stok
                foreach ($details as $detail) {

                $uuidDetail=Str::uuid();

                    DB::table('return_details')->insert([
                        'uuid'=>$uuidDetail,
                        'return_id' => $returnId,
                        'product_id' => $detail['product_id'],
                        'quantity' => $detail['quantity'],
                        'return_price' => $detail['return_price'],
                        'condition' => $detail['condition'],
                        'subtotal' => $detail['subtotal'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $uuidDamage=Str::uuid();

                    if ($detail['condition'] === 'layak') {

                        DB::table('products')
                            ->where('id', $detail['product_id'])
                            ->increment('total_unit', $detail['quantity']);
                    } else {

                        DB::table('damaged_products')->insert([
                            'uuid'=> $uuidDamage,
                            'product_id' => $detail['product_id'],
                            'user_id' => Auth::id(),
                            'quantity' => $detail['quantity'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                $returnData = DB::table('returns')
                    ->where('id', $returnId)
                    ->first();
            });

            return response()->json([
                'status' => true,
                'message' => 'Return berhasil diproses.',
                'data' => $returnData,
            ], 201);
        } catch (Exception $error) {

            return response()->json([
                'status' => false,
                'message' => 'Return gagal diproses.',
                'error' => $error->getMessage(),
            ], 500);
        }
    }
}
