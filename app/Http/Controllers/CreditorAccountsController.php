<?php

namespace App\Http\Controllers;

use App\Models\CreditorAccount;
use App\Models\CreditorPayment;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CreditorAccountsController extends Controller
{
    public function getList(Request $request)
    {
        $query = CreditorAccount::orderBy('credit_date', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('q')) {
            $query->where('vendor_name', 'like', '%' . $request->q . '%');
        }

        $items = $query->get()->toArray();

        if (!empty($items)) {
            for ($i = 0; $i < count($items); $i++) {
                $items[$i]['No'] = $i + 1;
            }
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $items);
    }

    public function getPage(Request $request)
    {
        $length = $request->length;
        $order = $request->order;
        $search = $request->search;
        $start = $request->start;
        $page = $start / $length + 1;
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $col = [
            'id',
            'vendor_name',
            'credit_amount',
            'paid_amount',
            'status',
            'credit_date',
            'note',
            'create_by',
            'update_by',
            'created_at',
            'updated_at',
        ];

        $orderby = [
            '',
            'vendor_name',
            'credit_amount',
            'paid_amount',
            'status',
            'credit_date',
            'create_by',
        ];

        $query = CreditorAccount::with(['payments.transaction'])->select($col);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($startDate && $endDate) {
            $query->whereBetween('credit_date', [$startDate, $endDate]);
        } elseif ($startDate) {
            $query->whereDate('credit_date', '>=', $startDate);
        } elseif ($endDate) {
            $query->whereDate('credit_date', '<=', $endDate);
        }

        if ($orderby[$order[0]['column']] ?? null) {
            $query->orderBy($orderby[$order[0]['column']], $order[0]['dir']);
        } else {
            $query->orderBy('created_at', 'desc')->orderBy('id', 'desc');
        }

        if (!empty($search['value'])) {
            $query->where(function ($q) use ($search, $col) {
                foreach ($col as $c) {
                    $q->orWhere($c, 'like', '%' . $search['value'] . '%');
                }
            });
        }

        $data = $query->paginate($length, ['*'], 'page', $page);

        if ($data->isNotEmpty()) {
            $no = (($page - 1) * $length);
            for ($i = 0; $i < count($data); $i++) {
                $no = $no + 1;
                $data[$i]->No = $no;
            }
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $data);
    }

    public function index(Request $request)
    {
        $query = CreditorAccount::with('payments.transaction')->orderBy('credit_date', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('q')) {
            $query->where('vendor_name', 'like', '%' . $request->q . '%');
        }

        $data = $query->get();

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $data);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vendor_name' => 'required|string|max:250',
            'credit_amount' => 'required|numeric|min:0.01',
            'credit_date' => 'required|date',
            'note' => 'nullable|string',
            'income_expenses_tracker_type_id' => 'nullable|integer|exists:income_expenses_tracker_types,id',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->returnErrorData($validator->errors()->first(), 400);
        }

        DB::beginTransaction();

        try {
            $transaction = new Transaction();
            $transaction->tx_date = $request->credit_date;
            $transaction->tx_type = 'expense';
            $transaction->payment_method = 'credit';
            $transaction->amount = 0;
            $transaction->income_expenses_tracker_type_id = $request->income_expenses_tracker_type_id;
            $transaction->description = $request->description ?: 'ตั้งเจ้าหนี้เครดิต';
            $transaction->related_type = 'creditor';
            $transaction->create_by = $this->getActorName();
            $transaction->save();

            $account = new CreditorAccount();
            $account->vendor_name = $request->vendor_name;
            $account->credit_amount = $request->credit_amount;
            $account->paid_amount = 0;
            $account->status = 'unpaid';
            $account->credit_date = $request->credit_date;
            $account->note = $request->note;
            $account->create_by = $this->getActorName();
            $account->save();

            $transaction->related_id = $account->id;
            $transaction->save();

            DB::commit();

            return $this->returnSuccess('เพิ่มข้อมูลสำเร็จ', [
                'transaction' => $transaction,
                'creditor_account' => $account,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->returnErrorData('เกิดข้อผิดพลาด: ' . $e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        $account = CreditorAccount::with(['payments.transaction'])->find($id);

        if (!$account) {
            return $this->returnErrorData('ไม่พบข้อมูลนี้', 404);
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $account);
    }

    public function pay(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'creditor_account_ids' => 'nullable|array|min:1',
            'creditor_account_ids.*' => 'required|integer|exists:creditor_accounts,id',
            'payments' => 'nullable|array|min:1',
            'payments.*.creditor_account_id' => 'required_with:payments|integer|exists:creditor_accounts,id',
            'payments.*.paid_amount' => 'required_with:payments|numeric|min:0.01',
            'paid_date' => 'required|date',
            'payment_method' => 'required|in:cash,transfer',
        ]);

        if ($validator->fails()) {
            return $this->returnErrorData($validator->errors()->first(), 400);
        }

        DB::beginTransaction();

        try {
            $hasPayments = $request->filled('payments');
            $hasAccountIds = $request->filled('creditor_account_ids');

            if (!$hasPayments && !$hasAccountIds) {
                return $this->returnErrorData('กรุณาระบุรายการที่ต้องการชำระ', 400);
            }

            $results = [];

            $targets = $hasPayments ? $request->payments : $request->creditor_account_ids;

            foreach ($targets as $target) {
                $accountId = $hasPayments ? $target['creditor_account_id'] : $target;
                $account = CreditorAccount::with('payments')->find($accountId);
                if (!$account) {
                    throw new \RuntimeException('ไม่พบข้อมูลเจ้าหนี้');
                }

                if ($account->status === 'paid') {
                    throw new \RuntimeException('พบรายการที่ชำระครบแล้ว');
                }

                $remaining = $account->credit_amount - $account->paid_amount;
                if ($remaining <= 0) {
                    throw new \RuntimeException('ยอดคงเหลือไม่ถูกต้อง');
                }

                $paidAmount = $hasPayments ? $target['paid_amount'] : $remaining;
                if ($paidAmount > $remaining) {
                    throw new \RuntimeException('ยอดชำระเกินยอดคงเหลือ');
                }

                $transaction = new Transaction();
                $transaction->tx_date = $request->paid_date;
                $transaction->tx_type = 'expense';
                $transaction->payment_method = $request->payment_method;
                $transaction->amount = $paidAmount;
                $transaction->description = 'ชำระเจ้าหนี้';
                $transaction->related_type = 'creditor';
                $transaction->related_id = $account->id;
                $transaction->create_by = $this->getActorName();
                $transaction->save();

                $payment = new CreditorPayment();
                $payment->creditor_account_id = $account->id;
                $payment->transaction_id = $transaction->id;
                $payment->paid_amount = $paidAmount;
                $payment->paid_date = $request->paid_date;
                $payment->payment_method = $request->payment_method;
                $payment->create_by = $this->getActorName();
                $payment->save();

                $account->paid_amount += $paidAmount;
                if ($account->paid_amount >= $account->credit_amount) {
                    $account->status = 'paid';
                } elseif ($account->paid_amount > 0) {
                    $account->status = 'partial';
                } else {
                    $account->status = 'unpaid';
                }
                $account->update_by = $this->getActorName();
                $account->save();

                $results[] = [
                    'transaction' => $transaction,
                    'creditor_payment' => $payment,
                    'creditor_account' => $account,
                ];
            }

            DB::commit();

            return $this->returnSuccess('ชำระเงินสำเร็จ', $results);
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->returnErrorData('เกิดข้อผิดพลาด: ' . $e->getMessage(), 500);
        }
    }

    private function getActorName()
    {
        return auth()->user()->name ?? 'system';
    }
}
