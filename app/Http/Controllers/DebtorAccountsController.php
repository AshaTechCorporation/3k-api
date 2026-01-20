<?php

namespace App\Http\Controllers;

use App\Models\DebtorAccount;
use App\Models\DebtorPayment;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DebtorAccountsController extends Controller
{
    public function getList(Request $request)
    {
        $query = DebtorAccount::orderBy('start_date', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('q')) {
            $query->where('debtor_name', 'like', '%' . $request->q . '%');
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
            'debtor_user_id',
            'debtor_name',
            'principal_amount',
            'principal_paid',
            'interest_paid',
            'status',
            'start_date',
            'note',
            'create_by',
            'update_by',
            'created_at',
            'updated_at',
        ];

        $orderby = [
            '',
            'debtor_name',
            'principal_amount',
            'principal_paid',
            'status',
            'start_date',
            'create_by',
        ];

        $query = DebtorAccount::with(['payments.transaction'])->select($col);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($startDate && $endDate) {
            $query->whereBetween('start_date', [$startDate, $endDate]);
        } elseif ($startDate) {
            $query->whereDate('start_date', '>=', $startDate);
        } elseif ($endDate) {
            $query->whereDate('start_date', '<=', $endDate);
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
        $query = DebtorAccount::with('payments.transaction')->orderBy('start_date', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('debtor_name', 'like', '%' . $request->q . '%');
            });
        }

        $data = $query->get();

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $data);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tx_date' => 'required|date',
            'payment_method' => 'required|in:cash,transfer',
            'principal_amount' => 'required|numeric|min:0.01',
            'debtor_user_id' => 'nullable|integer',
            'debtor_name' => 'nullable|string|max:250',
            'income_expenses_tracker_type_id' => 'nullable|integer|exists:income_expenses_tracker_types,id',
            'description' => 'nullable|string',
            'note' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->returnErrorData($validator->errors()->first(), 400);
        }

        DB::beginTransaction();

        try {
            $transaction = new Transaction();
            $transaction->tx_date = $request->tx_date;
            $transaction->tx_type = 'expense';
            $transaction->payment_method = $request->payment_method;
            $transaction->amount = $request->principal_amount;
            $transaction->income_expenses_tracker_type_id = $request->income_expenses_tracker_type_id;
            $transaction->description = $request->description;
            $transaction->related_type = 'debtor';
            $transaction->create_by = $this->getActorName();
            $transaction->save();

            $account = new DebtorAccount();
            $account->debtor_user_id = $request->debtor_user_id;
            $account->debtor_name = $request->debtor_name;
            $account->principal_amount = $request->principal_amount;
            $account->principal_paid = 0;
            $account->interest_paid = 0;
            $account->status = 'unpaid';
            $account->start_date = $request->tx_date;
            $account->note = $request->note;
            $account->create_by = $this->getActorName();
            $account->save();

            $transaction->related_id = $account->id;
            $transaction->save();

            DB::commit();

            return $this->returnSuccess('เพิ่มข้อมูลสำเร็จ', [
                'transaction' => $transaction,
                'debtor_account' => $account,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->returnErrorData('เกิดข้อผิดพลาด: ' . $e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        $account = DebtorAccount::with(['payments.transaction'])->find($id);

        if (!$account) {
            return $this->returnErrorData('ไม่พบข้อมูลนี้', 404);
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $account);
    }

    public function pay(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,transfer',
            'payment_type' => 'nullable|in:full,partial',
            'principal_paid' => 'nullable|numeric|min:0',
            'interest_paid' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return $this->returnErrorData($validator->errors()->first(), 400);
        }

        DB::beginTransaction();

        try {
            $account = DebtorAccount::with('payments')->find($id);
            if (!$account) {
                return $this->returnErrorData('ไม่พบข้อมูลนี้', 404);
            }

            if ($account->status === 'paid') {
                return $this->returnErrorData('รายการนี้ชำระครบแล้ว', 400);
            }

            $remaining = $account->principal_amount - $account->principal_paid;
            $principalPaid = $request->payment_type === 'full' || !$request->filled('principal_paid')
                ? $remaining
                : $request->principal_paid;
            $interestPaid = $request->filled('interest_paid') ? $request->interest_paid : 0;

            if ($principalPaid > $remaining) {
                return $this->returnErrorData('ยอดชำระเกินยอดคงเหลือ', 400);
            }

            $installmentNo = (int) ($account->payments()->max('installment_no') ?? 0) + 1;

            $transaction = new Transaction();
            $transaction->tx_date = $request->payment_date;
            $transaction->tx_type = 'income';
            $transaction->payment_method = $request->payment_method;
            $transaction->amount = $principalPaid + $interestPaid;
            $transaction->description = 'ชำระลูกหนี้';
            $transaction->related_type = 'debtor';
            $transaction->related_id = $account->id;
            $transaction->create_by = $this->getActorName();
            $transaction->save();

            $payment = new DebtorPayment();
            $payment->debtor_account_id = $account->id;
            $payment->transaction_id = $transaction->id;
            $payment->installment_no = $installmentNo;
            $payment->principal_paid = $principalPaid;
            $payment->interest_paid = $interestPaid;
            $payment->payment_date = $request->payment_date;
            $payment->create_by = $this->getActorName();
            $payment->save();

            $account->principal_paid += $principalPaid;
            $account->interest_paid += $interestPaid;

            if ($account->principal_paid >= $account->principal_amount) {
                $account->status = 'paid';
            } else {
                $account->status = 'partial';
            }
            $account->update_by = $this->getActorName();
            $account->save();

            DB::commit();

            return $this->returnSuccess('ชำระเงินสำเร็จ', [
                'transaction' => $transaction,
                'debtor_payment' => $payment,
                'debtor_account' => $account,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->returnErrorData('เกิดข้อผิดพลาด: ' . $e->getMessage(), 500);
        }
    }

    public function payBulk(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'debtor_account_ids' => 'nullable|array|min:1',
            'debtor_account_ids.*' => 'required|integer|exists:debtor_accounts,id',
            'payments' => 'nullable|array|min:1',
            'payments.*.debtor_account_id' => 'required_with:payments|integer|exists:debtor_accounts,id',
            'payments.*.interest_paid' => 'nullable|numeric|min:0',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,transfer',
        ]);

        if ($validator->fails()) {
            return $this->returnErrorData($validator->errors()->first(), 400);
        }

        DB::beginTransaction();

        try {
            $hasPayments = $request->filled('payments');
            $hasAccountIds = $request->filled('debtor_account_ids');

            if (!$hasPayments && !$hasAccountIds) {
                return $this->returnErrorData('กรุณาระบุรายการที่ต้องการชำระ', 400);
            }

            $results = [];

            $targets = $hasPayments ? $request->payments : $request->debtor_account_ids;

            foreach ($targets as $target) {
                $accountId = $hasPayments ? $target['debtor_account_id'] : $target;
                $account = DebtorAccount::with('payments')->find($accountId);
                if (!$account) {
                    throw new \RuntimeException('ไม่พบข้อมูลลูกหนี้');
                }

                if ($account->status === 'paid') {
                    throw new \RuntimeException('พบรายการที่ชำระครบแล้ว');
                }

                $remaining = $account->principal_amount - $account->principal_paid;
                if ($remaining <= 0) {
                    throw new \RuntimeException('ยอดคงเหลือไม่ถูกต้อง');
                }

                $interestPaid = $hasPayments && isset($target['interest_paid']) ? $target['interest_paid'] : 0;
                $installmentNo = (int) ($account->payments()->max('installment_no') ?? 0) + 1;

                $transaction = new Transaction();
                $transaction->tx_date = $request->payment_date;
                $transaction->tx_type = 'income';
                $transaction->payment_method = $request->payment_method;
                $transaction->amount = $remaining + $interestPaid;
                $transaction->description = 'ชำระลูกหนี้ (เต็มจำนวน)';
                $transaction->related_type = 'debtor';
                $transaction->related_id = $account->id;
                $transaction->create_by = $this->getActorName();
                $transaction->save();

                $payment = new DebtorPayment();
                $payment->debtor_account_id = $account->id;
                $payment->transaction_id = $transaction->id;
                $payment->installment_no = $installmentNo;
                $payment->principal_paid = $remaining;
                $payment->interest_paid = $interestPaid;
                $payment->payment_date = $request->payment_date;
                $payment->create_by = $this->getActorName();
                $payment->save();

                $account->principal_paid += $remaining;
                $account->interest_paid += $interestPaid;
                $account->status = 'paid';
                $account->update_by = $this->getActorName();
                $account->save();

                $results[] = [
                    'transaction' => $transaction,
                    'debtor_payment' => $payment,
                    'debtor_account' => $account,
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
