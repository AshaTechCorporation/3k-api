<?php

namespace App\Http\Controllers;

use App\Models\CreditorAccount;
use App\Models\DebtorAccount;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ReportsController extends Controller
{
    public function incomeExpenseSummary(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'period' => 'required|in:daily,monthly,yearly',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'payment_method' => 'nullable|in:cash,transfer,credit',
            'income_expenses_tracker_type_id' => 'nullable|integer|exists:income_expenses_tracker_types,id',
            'include_transactions' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->returnErrorData($validator->errors()->first(), 400);
        }

        $period = $request->period;

        if ($period === 'daily') {
            $periodSelect = "DATE(tx_date) as period";
            $periodGroup = "DATE(tx_date)";
        } elseif ($period === 'monthly') {
            $periodSelect = "DATE_FORMAT(tx_date, '%Y-%m') as period";
            $periodGroup = "DATE_FORMAT(tx_date, '%Y-%m')";
        } else {
            $periodSelect = "YEAR(tx_date) as period";
            $periodGroup = "YEAR(tx_date)";
        }

        $baseQuery = DB::table('transactions')->whereNull('deleted_at');

        if ($request->filled('start_date')) {
            $baseQuery->whereDate('tx_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $baseQuery->whereDate('tx_date', '<=', $request->end_date);
        }
        if ($request->filled('payment_method')) {
            $baseQuery->where('payment_method', $request->payment_method);
        }
        if ($request->filled('income_expenses_tracker_type_id')) {
            $baseQuery->where('income_expenses_tracker_type_id', $request->income_expenses_tracker_type_id);
        }

        $summaryQuery = clone $baseQuery;
        $summary = $summaryQuery->selectRaw(
            "SUM(CASE WHEN tx_type = 'income' THEN amount ELSE 0 END) as income_total,
             SUM(CASE WHEN tx_type = 'expense' THEN amount ELSE 0 END) as expense_total"
        )->first();

        $items = $baseQuery->selectRaw(
            $periodSelect . ",
             SUM(CASE WHEN tx_type = 'income' THEN amount ELSE 0 END) as income_total,
             SUM(CASE WHEN tx_type = 'expense' THEN amount ELSE 0 END) as expense_total"
        )
            ->groupBy(DB::raw($periodGroup))
            ->orderBy('period', 'asc')
            ->get();

        foreach ($items as $item) {
            $item->net_total = (float) $item->income_total - (float) $item->expense_total;
        }

        $summaryData = [
            'income_total' => (float) ($summary->income_total ?? 0),
            'expense_total' => (float) ($summary->expense_total ?? 0),
        ];
        $summaryData['net_total'] = $summaryData['income_total'] - $summaryData['expense_total'];

        $response = [
            'summary' => $summaryData,
            'items' => $items,
        ];

        if ($request->boolean('include_transactions')) {
            $transactionsQuery = Transaction::with([
                'category',
                'items.productAttribute',
                'debtorAccount',
                'creditorAccount',
            ])->whereNull('deleted_at');

            if ($request->filled('start_date')) {
                $transactionsQuery->whereDate('tx_date', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $transactionsQuery->whereDate('tx_date', '<=', $request->end_date);
            }
            if ($request->filled('payment_method')) {
                $transactionsQuery->where('payment_method', $request->payment_method);
            }
            if ($request->filled('income_expenses_tracker_type_id')) {
                $transactionsQuery->where('income_expenses_tracker_type_id', $request->income_expenses_tracker_type_id);
            }

            $transactions = $transactionsQuery->orderBy('tx_date', 'asc')->get();

            foreach ($transactions as $transaction) {
                if ($transaction->related_type === 'debtor') {
                    $transaction->setRelation('creditorAccount', null);
                } elseif ($transaction->related_type === 'creditor') {
                    $transaction->setRelation('debtorAccount', null);
                } else {
                    $transaction->setRelation('debtorAccount', null);
                    $transaction->setRelation('creditorAccount', null);
                }
            }

            $response['transactions'] = $transactions;
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $response);
    }

    public function debtorsReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'nullable|in:unpaid,partial,paid',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return $this->returnErrorData($validator->errors()->first(), 400);
        }

        $baseQuery = DebtorAccount::query()->whereNull('deleted_at');

        if ($request->filled('status')) {
            $baseQuery->where('status', $request->status);
        }
        if ($request->filled('start_date')) {
            $baseQuery->whereDate('start_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $baseQuery->whereDate('start_date', '<=', $request->end_date);
        }

        $summaryQuery = clone $baseQuery;
        $summary = $summaryQuery->selectRaw(
            'SUM(principal_amount) as principal_amount_total,
             SUM(principal_paid) as principal_paid_total,
             SUM(interest_paid) as interest_paid_total'
        )->first();

        $items = $baseQuery->orderBy('start_date', 'desc')->get();

        foreach ($items as $item) {
            $item->remaining_principal = (float) $item->principal_amount - (float) $item->principal_paid;
        }

        $summaryData = [
            'principal_amount_total' => (float) ($summary->principal_amount_total ?? 0),
            'principal_paid_total' => (float) ($summary->principal_paid_total ?? 0),
            'interest_paid_total' => (float) ($summary->interest_paid_total ?? 0),
        ];
        $summaryData['remaining_principal_total'] =
            $summaryData['principal_amount_total'] - $summaryData['principal_paid_total'];

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', [
            'summary' => $summaryData,
            'items' => $items,
        ]);
    }

    public function creditorsReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'nullable|in:unpaid,partial,paid',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return $this->returnErrorData($validator->errors()->first(), 400);
        }

        $baseQuery = CreditorAccount::query()->whereNull('deleted_at');

        if ($request->filled('status')) {
            $baseQuery->where('status', $request->status);
        }
        if ($request->filled('start_date')) {
            $baseQuery->whereDate('credit_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $baseQuery->whereDate('credit_date', '<=', $request->end_date);
        }

        $summaryQuery = clone $baseQuery;
        $summary = $summaryQuery->selectRaw(
            'SUM(credit_amount) as credit_amount_total,
             SUM(paid_amount) as paid_amount_total'
        )->first();

        $items = $baseQuery->orderBy('credit_date', 'desc')->get();

        foreach ($items as $item) {
            $item->remaining_amount = (float) $item->credit_amount - (float) $item->paid_amount;
        }

        $summaryData = [
            'credit_amount_total' => (float) ($summary->credit_amount_total ?? 0),
            'paid_amount_total' => (float) ($summary->paid_amount_total ?? 0),
        ];
        $summaryData['remaining_amount_total'] =
            $summaryData['credit_amount_total'] - $summaryData['paid_amount_total'];

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', [
            'summary' => $summaryData,
            'items' => $items,
        ]);
    }
}
