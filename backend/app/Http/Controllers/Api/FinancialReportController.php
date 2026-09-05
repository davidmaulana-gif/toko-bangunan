<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FinancialReportController extends Controller
{
    public function financialReport(Request $request)
    {
        try {
            $request->validate([
                'start_date' => 'required|date',
                'end_date'   => 'required|date|after_or_equal:start_date',
            ]);

            $start = $request->start_date . ' 00:00:00';
            $end   = $request->end_date . ' 23:59:59';

            // Total pemasukan
            $income = DB::table('sales_transactions')
                ->whereBetween('created_at', [$start, $end])
                ->sum('total');

            // Total pengeluaran pembelian stok
            $expense = DB::table('stock_receipt_details')
                ->join(
                    'stock_receipts',
                    'stock_receipt_details.stock_receipt_id',
                    '=',
                    'stock_receipts.id'
                )
                ->whereBetween('stock_receipts.created_at', [$start, $end])
                ->selectRaw('SUM(stock_receipt_details.quantity * stock_receipt_details.buying_price) as total')
                ->value('total');

            // Total retur
            $return = DB::table('returns')
                ->whereBetween('created_at', [$start, $end])
                ->sum('total_return');

            $expense = $expense ?? 0;
            $return = $return ?? 0;

            // Laba kotor
            $profit = $income - $expense - $return;

            return response()->json([
                'status' => true,
                'message' => 'Laporan keuangan berhasil diambil.',
                'data' => [
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                    'income' => $income,
                    'expense' => $expense,
                    'return' => $return,
                    'gross_profit' => $profit
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
