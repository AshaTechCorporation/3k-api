<?php

namespace App\Http\Controllers;

use App\Models\CreditorAccount;
use App\Models\DebtorAccount;
use App\Models\ProductAttribute;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TransactionsController extends Controller
{
    public function getList($date = null)
    {
        $query = Transaction::with('category')->orderBy('tx_date', 'desc');

        if ($date) {
            $query->whereDate('tx_date', $date);
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
        $date = $request->date;

        $col = [
            'id',
            'tx_date',
            'tx_type',
            'payment_method',
            'amount',
            'income_expenses_tracker_type_id',
            'car_id',
            'description',
            'related_type',
            'related_id',
            'create_by',
            'update_by',
            'created_at',
            'updated_at',
        ];

        $orderby = [
            '',
            'tx_date',
            'tx_type',
            'payment_method',
            'amount',
            'income_expenses_tracker_type_id',
            'car_id',
            'related_type',
            'related_id',
            'create_by',
        ];

        $query = Transaction::with('category')->select($col);

        if ($date) {
            $query->whereDate('tx_date', $date);
        }

        if ($orderby[$order[0]['column']] ?? null) {
            $query->orderBy($orderby[$order[0]['column']], $order[0]['dir']);
        } else {
            $query->orderBy('tx_date', 'desc');
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

    public function show($id)
    {
        $transaction = Transaction::with(['category', 'items.productAttribute', 'debtorPayment', 'creditorPayment'])
            ->find($id);

        if (!$transaction) {
            return $this->returnErrorData('ไม่พบข้อมูลนี้', 404);
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $transaction);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tx_date' => 'required|date',
            'tx_type' => 'required|in:income,expense',
            'payment_method' => 'required|in:cash,transfer,credit',
            'amount' => 'required|numeric|min:0',
            'income_expenses_tracker_type_id' => 'nullable|integer|exists:income_expenses_tracker_types,id',
            'car_id' => 'nullable|integer|exists:products,id',
            'description' => 'nullable|string',
            'related_type' => 'nullable|in:debtor,creditor',
            'related_id' => 'nullable|integer',
            'debtor_user_id' => 'nullable|integer',
            'debtor_name' => 'nullable|string|max:250',
            'debtor_note' => 'nullable|string',
            'vendor_name' => 'nullable|string|max:250',
            'credit_note' => 'nullable|string',
            'skip_creditor' => 'nullable|boolean',
            'items' => 'nullable|array',
            'items.*.product_attribute_id' => 'required_with:items|integer|exists:product_attributes,id',
            'items.*.qty' => 'required_with:items|integer|min:1',
            'items.*.unit_cost' => 'required_with:items|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return $this->returnErrorData($validator->errors()->first(), 400);
        }

        DB::beginTransaction();

        try {
            if ($request->related_type === 'debtor') {
                $hasDebtorName = $request->filled('debtor_name');
                $hasDebtorUser = $request->filled('debtor_user_id');

                if (!$hasDebtorName && !$hasDebtorUser) {
                    return $this->returnErrorData('กรุณาระบุชื่อหรือลูกหนี้ที่ยืมเงิน', 400);
                }
            }

            $transaction = new Transaction();
            $transaction->tx_date = $request->tx_date;
            $transaction->tx_type = $request->tx_type;
            $transaction->payment_method = $request->payment_method;
            $transaction->amount = $request->amount;
            $transaction->income_expenses_tracker_type_id = $request->income_expenses_tracker_type_id;
            $transaction->car_id = $request->car_id;
            $transaction->description = $request->description;
            $transaction->related_type = $request->related_type;
            $transaction->related_id = $request->related_id;
            $transaction->create_by = $this->getActorName();
            $transaction->save();

            if ($request->items) {
                foreach ($request->items as $part) {
                    $transactionItem = new TransactionItem();
                    $transactionItem->transaction_id = $transaction->id;
                    $transactionItem->product_attribute_id = $part['product_attribute_id'];
                    $transactionItem->qty = $part['qty'];
                    $transactionItem->unit_cost = $part['unit_cost'];
                    $transactionItem->total_cost = $part['qty'] * $part['unit_cost'];
                    $transactionItem->create_by = $this->getActorName();
                    $transactionItem->save();

                    $partItem = ProductAttribute::find($part['product_attribute_id']);
                    if ($partItem) {
                        $partItem->qty += $part['qty'];
                        $partItem->cost = $part['unit_cost'];
                        $partItem->update_by = $this->getActorName();
                        $partItem->save();
                    }
                }
            }

            if ($transaction->related_type === 'debtor' && !$transaction->related_id) {
                if ($transaction->tx_type !== 'expense') {
                    return $this->returnErrorData('รายการลูกหนี้ต้องเป็นรายจ่ายเท่านั้น', 400);
                }

                $account = new DebtorAccount();
                $account->debtor_user_id = $request->debtor_user_id;
                $account->debtor_name = $request->debtor_name;
                $account->principal_amount = $transaction->amount;
                $account->principal_paid = 0;
                $account->interest_paid = 0;
                $account->status = 'unpaid';
                $account->start_date = $transaction->tx_date;
                $account->note = $request->debtor_note;
                $account->create_by = $this->getActorName();
                $account->save();

                $transaction->related_id = $account->id;
                $transaction->save();
            }

            $skipCreditor = (bool) $request->skip_creditor;
            $shouldAutoCredit = $transaction->tx_type === 'expense'
                && $transaction->payment_method === 'credit'
                && !$skipCreditor;

            if ($transaction->related_type === 'debtor' && $shouldAutoCredit) {
                return $this->returnErrorData('เครดิตไม่สามารถเป็นลูกหนี้ได้', 400);
            }

            if ($shouldAutoCredit && !$transaction->related_id) {
                if (!$request->filled('vendor_name')) {
                    return $this->returnErrorData('กรุณาระบุชื่อร้านสำหรับเจ้าหนี้', 400);
                }

                $account = new CreditorAccount();
                $account->vendor_name = $request->vendor_name;
                $account->credit_amount = $transaction->amount;
                $account->paid_amount = 0;
                $account->status = 'unpaid';
                $account->credit_date = $transaction->tx_date;
                $account->note = $request->credit_note;
                $account->create_by = $this->getActorName();
                $account->save();

                $transaction->related_type = 'creditor';
                $transaction->related_id = $account->id;
                $transaction->save();
            }

            DB::commit();

            return $this->returnSuccess('เพิ่มข้อมูลสำเร็จ', $transaction);
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->returnErrorData('เกิดข้อผิดพลาด: ' . $e->getMessage(), 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'tx_date' => 'nullable|date',
            'payment_method' => 'nullable|in:cash,transfer,credit',
            'amount' => 'nullable|numeric|min:0',
            'income_expenses_tracker_type_id' => 'nullable|integer|exists:income_expenses_tracker_types,id',
            'car_id' => 'nullable|integer|exists:products,id',
            'description' => 'nullable|string',
            'debtor_name' => 'nullable|string|max:250',
            'vendor_name' => 'nullable|string|max:250',
        ]);

        if ($validator->fails()) {
            return $this->returnErrorData($validator->errors()->first(), 400);
        }

        DB::beginTransaction();

        try {
            $transaction = Transaction::with(['debtorPayment.account', 'creditorPayment.account'])->find($id);
            if (!$transaction) {
                return $this->returnErrorData('ไม่พบข้อมูลนี้', 404);
            }

            if ($request->filled('tx_date')) {
                $transaction->tx_date = $request->tx_date;
            }
            if ($request->filled('payment_method')) {
                $transaction->payment_method = $request->payment_method;
            }
            if ($request->has('amount')) {
                $transaction->amount = $request->amount;
            }
            if ($request->has('income_expenses_tracker_type_id')) {
                $transaction->income_expenses_tracker_type_id = $request->income_expenses_tracker_type_id;
            }
            if ($request->has('car_id')) {
                $transaction->car_id = $request->car_id;
            }
            if ($request->has('description')) {
                $transaction->description = $request->description;
            }
            $transaction->update_by = $this->getActorName();
            $transaction->save();

            if ($transaction->related_type === 'debtor' && $transaction->related_id) {
                $debtorAccount = DebtorAccount::find($transaction->related_id);
                if ($debtorAccount) {
                    if (!$transaction->debtorPayment) {
                        $debtorAccount->principal_amount = $transaction->amount;
                        if ($request->filled('tx_date')) {
                            $debtorAccount->start_date = $transaction->tx_date;
                        }
                        if ($request->filled('debtor_name')) {
                            $debtorAccount->debtor_name = $request->debtor_name;
                        }
                        $debtorAccount->update_by = $this->getActorName();
                        $debtorAccount->save();
                    }
                    $this->recalculateDebtorAccount($debtorAccount);
                }
            } elseif ($transaction->debtorPayment && $transaction->debtorPayment->account) {
                $this->recalculateDebtorAccount($transaction->debtorPayment->account);
            }

            if ($transaction->related_type === 'creditor' && $transaction->related_id) {
                $creditorAccount = CreditorAccount::find($transaction->related_id);
                if ($creditorAccount) {
                    if (!$transaction->creditorPayment) {
                        $creditorAccount->credit_amount = $transaction->amount;
                        if ($request->filled('tx_date')) {
                            $creditorAccount->credit_date = $transaction->tx_date;
                        }
                        if ($request->filled('vendor_name')) {
                            $creditorAccount->vendor_name = $request->vendor_name;
                        }
                        $creditorAccount->update_by = $this->getActorName();
                        $creditorAccount->save();
                    }
                    $this->recalculateCreditorAccount($creditorAccount);
                }
            } elseif ($transaction->creditorPayment && $transaction->creditorPayment->account) {
                $this->recalculateCreditorAccount($transaction->creditorPayment->account);
            }

            DB::commit();

            return $this->returnSuccess('อัปเดตข้อมูลสำเร็จ', $transaction);
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->returnErrorData('เกิดข้อผิดพลาด: ' . $e->getMessage(), 500);
        }
    }

    private function recalculateDebtorAccount(DebtorAccount $account)
    {
        $principalPaid = $account->payments()->sum('principal_paid');
        $interestPaid = $account->payments()->sum('interest_paid');

        $account->principal_paid = min($principalPaid, $account->principal_amount);
        $account->interest_paid = max($interestPaid, 0);

        if ($account->principal_paid >= $account->principal_amount) {
            $account->status = 'paid';
        } elseif ($account->principal_paid > 0) {
            $account->status = 'partial';
        } else {
            $account->status = 'unpaid';
        }

        $account->update_by = $this->getActorName();
        $account->save();
    }

    private function recalculateCreditorAccount(CreditorAccount $account)
    {
        $paidAmount = $account->payments()->sum('paid_amount');

        $account->paid_amount = min($paidAmount, $account->credit_amount);
        $account->status = $account->paid_amount >= $account->credit_amount ? 'paid' : 'unpaid';
        $account->update_by = $this->getActorName();
        $account->save();
    }

    private function getActorName()
    {
        return auth()->user()->name ?? 'system';
    }
}
