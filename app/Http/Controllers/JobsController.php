<?php

namespace App\Http\Controllers;

use App\Models\Jobs;
use App\Models\JobsImages;
use App\Models\JobsExpensesList;
use App\Models\StepJobsTypeLists;
use App\Models\Products;
use App\Models\WorkType;
use App\Models\ExpenseType;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeImages;
use App\Models\StepJobsTypeProductAttribute;
use App\Models\StepJobsTypeExpense;
use App\Models\StepJobsTypeProAttrOther;
use App\Models\StepJobs;
use App\Models\StepJobTypeListImages;
use App\Models\OrderList;
use App\Models\Orders;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class JobsController extends Controller
{
    public function getList()
    {
        try {
            $items = Jobs::with([
                'images',
                'otherExpenses',
                'steps' => function ($query) {
                    $query->with([
                        'stepJobTypeLists' => function ($stepQuery) {
                            $stepQuery->with([
                                'productAttributes',
                                'productAttributeOthers',
                                'expenses',
                                'workType' // รวม work_type ด้วย
                            ]);
                        }
                    ]);
                }
            ])->orderBy('created_at', 'desc')->get();

            foreach ($items as $i => $item) {
                $item->No = $i + 1;
            }

            return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $items);
        } catch (\Throwable $e) {
            return $this->returnErrorData('เกิดข้อผิดพลาด: ' . $e->getMessage(), 500);
        }
    }


    public function getMasterList()
    {
        try {
            $items = Jobs::select('id', 'master', 'master_name') // ✅ เลือกเฉพาะฟิลด์
            ->with([
                'images',
                'otherExpenses',
                'steps' => function ($query) {
                    $query->with([
                        'stepJobTypeLists' => function ($stepQuery) {
                            $stepQuery->with([
                                'productAttributes',
                                'productAttributeOthers',
                                'expenses',
                                'workType' // รวม work_type ด้วย
                            ]);
                        }
                    ]);
                }
            ])
            ->where('master', 'Y') // ✅ เพิ่มเงื่อนไขที่นี่
            ->orderBy('created_at', 'desc')->get();

            foreach ($items as $i => $item) {
                $item->No = $i + 1;
            }

            return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $items);
        } catch (\Throwable $e) {
            return $this->returnErrorData('เกิดข้อผิดพลาด: ' . $e->getMessage(), 500);
        }
    }

    
    public function getPage(Request $request)
    {
        try {
            $length = $request->length ?? 10;
            $start = $request->start ?? 0;
            $page = floor($start / $length) + 1;
            $search = $request->search['value'] ?? '';
            $status = $request->status;

            $query = Jobs::with([
                'product', // products
                'images', // jobs_images
                'otherExpenses.expenseType', // jobs_expenses_lists + expense_types
                'steps' => function ($query) {
                    $query->with([
                        'stepJobTypeLists' => function ($stepQuery) {
                            $stepQuery->with([
                                'productAttributes',
                                'productAttributeOthers',
                                'expenses',
                                'workType' // work_types
                            ]);
                        }
                    ]);
                }
            ]);

            if ($status) {

                $query->where('status', $status);
            }

            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('remark', 'like', "%$search%")
                    ->orWhere('status', 'like', "%$search%");
                });
            }

            $items = $query->orderBy('created_at', 'desc')->paginate($length, ['*'], 'page', $page);

            $No = (($page - 1) * $length);
            foreach ($items as $i => $item) {
                $item->No = ++$No;
                if($item->order_id)
                    $item->order = Orders::find($item->order_id);
                else
                    $item->order = null;

                // ปรับ URL รูปให้เต็ม (เฉพาะ jobs_images)
                if ($item->images) {
                    foreach ($item->images as &$img) {
                        $img->image = url($img->image);
                    }
                }
            }

            return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $items);
        } catch (\Throwable $e) {
            return $this->returnErrorData('เกิดข้อผิดพลาด: ' . $e->getMessage(), 500);
        }
    }



    // public function store(Request $request)
    // {
    //     DB::beginTransaction();

    //     try {
    //         $job = new Jobs();
    //         $job->product_id = $request->product_id;
    //         $job->work_type_id = $request->work_type_id;
    //         $job->estimated_cost = $request->estimated_cost ?? 0;
    //         $job->remark = $request->remark;
    //         $job->status = $request->status ?? 'pending';
    //         $job->completed_date = $request->completed_date;
    //         $job->create_by = $request->create_by;
    //         $job->update_by = $request->create_by;
    //         $job->save();

    //         if (isset($request->images)) {
    //             foreach ($request->images as $img) {
    //                 $image = new JobsImages();
    //                 $image->job_id = $job->id;
    //                 $image->image = $img['image'];
    //                 $image->create_by = $request->create_by;
    //                 $image->update_by = $request->create_by;
    //                 $image->save();
    //             }
    //         }

    //         if (isset($request->expenses)) {
    //             foreach ($request->expenses as $exp) {
    //                 $expense = new JobsExpensesList();
    //                 $expense->job_id = $job->id;
    //                 $expense->expense_type_id = $exp['expense_type_id'];
    //                 $expense->description = $exp['description'] ?? null;
    //                 $expense->amount = $exp['amount'] ?? 0;
    //                 $expense->product_attribute_id = $exp['product_attribute_id'] ?? null;
    //                 $expense->product_attribute_qty = $exp['product_attribute_qty'] ?? null;
    //                 $expense->create_by = $request->create_by;
    //                 $expense->update_by = $request->create_by;
    //                 $expense->save();
    //             }
    //         }

    //         DB::commit();
    //         return $this->returnSuccess('บันทึกข้อมูลงานสำเร็จ', $job);

    //     } catch (\Throwable $e) {
    //         DB::rollback();
    //         return $this->returnErrorData('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง ' . $e->getMessage(), 500);
    //     }
    // }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $job = new Jobs();

            $prefix = "#JO-";
            $id = IdGenerator::generate(['table' => 'jobs', 'field' => 'code', 'length' => 9, 'prefix' => $prefix]);

            $job->code = $id;
            $job->product_id = $request->product_id;
            $job->remark = $request->remark;
            $job->completed_date = $request->completed_date;
            $job->master = $request->master ?? "N";
            $job->master_name = $request->master_name ?? "";
            $job->save();

            if (isset($request->images)) {
                foreach ($request->images as $img) {
                    $image = new JobsImages();
                    $image->job_id = $job->id;
                    $image->image = $img['image'];
                    $image->save();
                }
            }

            if (isset($request->steps)) {
                foreach ($request->steps as $step) {

                    $stp = new StepJobs();
                    $stp->job_id = $job->id;
                    $stp->step_no = $step['step_no'] ?? null;
                    $stp->completed_date = $step['completed_date'] ?? null;
                    $stp->save();

                    foreach ($step['work_types'] as $work_type) {
                        $jobtype = new StepJobsTypeLists();
                        $jobtype->job_id = $job->id;
                        $jobtype->step_jobs_id = $stp->id;
                        $jobtype->work_type_id = $work_type['work_type_id'] ?? null;
                        $jobtype->save();
                        
                        foreach ($work_type['product_attributes'] as $product_attribute) {
                            $product_attr = new StepJobsTypeProductAttribute();
                            $product_attr->job_id = $job->id;
                            $product_attr->step_jobs_type_list_id = $jobtype->id;
                            $product_attr->work_type_id = $work_type['work_type_id'] ?? null;
                            $product_attr->deposit_type = $product_attribute['deposit_type'];
                            $product_attr->amount = $product_attribute['amount'] ?? null;
                            $product_attr->product_attribute_id = $product_attribute['product_attribute_id'] ?? null;
                            $product_attr->product_attribute_qty = $product_attribute['product_attribute_qty'] ?? null;

                            $product_attr->save();
                        }

                        foreach ($work_type['product_attribute_others'] as $product_attribute_other) {
                            $product_attr_other = new StepJobsTypeProAttrOther();
                            $product_attr_other->job_id = $job->id;
                            $product_attr_other->step_jobs_type_list_id = $jobtype->id;
                            $product_attr_other->work_type_id = $work_type['work_type_id'] ?? null;
                            $product_attr_other->deposit_type = $product_attribute_other['deposit_type'];
                            $product_attr_other->amount = $product_attribute_other['amount'] ?? null;
                            $product_attr_other->name = $product_attribute_other['name'] ?? null;
                            $product_attr_other->qty = $product_attribute_other['qty'] ?? null;
                            $product_attr_other->detail = $product_attribute_other['detail'] ?? null;

                            $product_attr_other->save();
                        }

                        foreach ($work_type['expenses'] as $expense) {
                            $expen = new StepJobsTypeExpense();
                            $expen->job_id = $job->id;
                            $expen->step_jobs_type_list_id = $jobtype->id;
                            $expen->work_type_id = $work_type['work_type_id'] ?? null;
                            $expen->amount = $expense['amount'] ?? null;
                            $expen->expense_type_id = $expense['expense_type_id'] ?? null;

                            $expen->save();
                        }
                    }
                }
            }

            foreach ($request->other_expenses as $otherexpense) {
                $other_expense = new JobsExpensesList();
                $other_expense->job_id = $job->id;
                $other_expense->name = $otherexpense['name'] ?? null;
                $other_expense->amount = $otherexpense['amount'] ?? null;

                $other_expense->save();
            }

            DB::commit();
            return $this->returnSuccess('บันทึกข้อมูลงานสำเร็จ', $job);

        } catch (\Throwable $e) {
            DB::rollback();
            return $this->returnErrorData('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง ' . $e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        try {
            $job = Jobs::with([
                'product.area',
                'product.brand',
                'product.brandModel',
                'product.cc',
                'product.color',
                'images',
                'otherExpenses',
                'steps' => function ($query) {
                    $query->with([
                        'stepJobTypeLists' => function ($stepQuery) {
                            $stepQuery->with([
                                'productAttributes.product_attribute', // ← เพิ่มตรงนี้
                                'productAttributeOthers',
                                'expenses',
                                'workType',
                                'images',
                            ]);
                        }
                    ]);
                }
            ])->find($id);

            if (!$job) {
                return $this->returnErrorData('ไม่พบข้อมูล', 404);
            }

            foreach ($job->images as $image) {
                $image->image = url($image->image); // ปรับ path ที่ต้องการ
            }

            foreach ($job->steps as $step) {
                foreach ($step->stepJobTypeLists as $typeList) {
                    foreach ($typeList->images as $image) {
                        $image->image = url($image->image); // ปรับ path ที่ต้องการ
                    }
                }
            }

            return $this->returnSuccess('ดึงข้อมูลสำเร็จ', $job);
        } catch (\Throwable $e) {
            return $this->returnErrorData('เกิดข้อผิดพลาด: ' . $e->getMessage(), 500);
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $job = Jobs::findOrFail($id);
            $job->product_id = $request->product_id;
            $job->remark = $request->remark;
            $job->completed_date = $request->completed_date;
            $job->save();

            // ลบข้อมูลเก่าที่เกี่ยวข้องทั้งหมด
            JobsImages::where('job_id', $job->id)->delete();
            JobsExpensesList::where('job_id', $job->id)->delete();
            StepJobs::where('job_id', $job->id)->delete();
            StepJobsTypeLists::where('job_id', $job->id)->delete();
            StepJobsTypeProductAttribute::where('job_id', $job->id)->delete();
            StepJobsTypeProAttrOther::where('job_id', $job->id)->delete();
            StepJobsTypeExpense::where('job_id', $job->id)->delete();

            if (isset($request->images)) {
                foreach ($request->images as $img) {
                    $image = new JobsImages();
                    $image->job_id = $job->id;
                    $image->image = $img['image'];
                    $image->save();
                }
            }

            if (isset($request->other_expenses)) {
                foreach ($request->other_expenses as $exp) {
                    $expense = new JobsExpensesList();
                    $expense->job_id = $job->id;
                    $expense->name = $exp['name'] ?? null;
                    $expense->amount = $exp['amount'] ?? 0;
                    $expense->save();
                }
            }

            if (isset($request->steps)) {
                foreach ($request->steps as $step) {

                    $stp = new StepJobs();
                    $stp->job_id = $job->id;
                    $stp->step_no = $step['step_no'] ?? null;
                    $stp->completed_date = $step['completed_date'] ?? null;
                    $stp->save();

                    foreach ($step['work_types'] as $work_type) {
                        $jobtype = new StepJobsTypeLists();
                        $jobtype->job_id = $job->id;
                        $jobtype->step_jobs_id = $stp->id;
                        $jobtype->work_type_id = $work_type['work_type_id'] ?? null;
                        $jobtype->save();

                        foreach ($work_type['product_attributes'] as $product_attribute) {
                            $product_attr = new StepJobsTypeProductAttribute();
                            $product_attr->job_id = $job->id;
                            $product_attr->step_jobs_type_list_id = $jobtype->id;
                            $product_attr->work_type_id = $work_type['work_type_id'] ?? null;
                            $product_attr->deposit_type = $product_attribute['deposit_type'];
                            $product_attr->amount = $product_attribute['amount'] ?? null;
                            $product_attr->product_attribute_id = $product_attribute['product_attribute_id'] ?? null;
                            $product_attr->product_attribute_qty = $product_attribute['product_attribute_qty'] ?? null;
                            $product_attr->save();
                        }

                        foreach ($work_type['product_attribute_others'] as $product_attribute_other) {
                            $product_attr_other = new StepJobsTypeProAttrOther();
                            $product_attr_other->job_id = $job->id;
                            $product_attr_other->step_jobs_type_list_id = $jobtype->id;
                            $product_attr_other->work_type_id = $work_type['work_type_id'] ?? null;
                            $product_attr_other->deposit_type = $product_attribute_other['deposit_type'];
                            $product_attr_other->amount = $product_attribute_other['amount'] ?? null;
                            $product_attr_other->name = $product_attribute_other['name'] ?? null;
                            $product_attr_other->qty = $product_attribute_other['qty'] ?? null;
                            $product_attr_other->detail = $product_attribute_other['detail'] ?? null;
                            $product_attr_other->save();
                        }

                        foreach ($work_type['expenses'] as $expense) {
                            $expen = new StepJobsTypeExpense();
                            $expen->job_id = $job->id;
                            $expen->step_jobs_type_list_id = $jobtype->id;
                            $expen->work_type_id = $work_type['work_type_id'] ?? null;
                            $expen->amount = $expense['amount'] ?? null;
                            $expen->expense_type_id = $expense['expense_type_id'] ?? null;
                            $expen->save();
                        }
                    }
                }
            }

            DB::commit();
            return $this->returnSuccess('อัปเดตข้อมูลงานสำเร็จ', $job);

        } catch (\Throwable $e) {
            DB::rollback();
            return $this->returnErrorData('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง ' . $e->getMessage(), 500);
        }
    }


    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $job = Jobs::find($id);
            if (!$job) {
                return $this->returnErrorData('ไม่พบรายการที่จะลบ', 404);
            }

            JobsImages::where('job_id', $id)->delete();
            JobsExpensesList::where('job_id', $id)->delete();
            $job->delete();

            DB::commit();
            return $this->returnSuccess('ลบข้อมูลสำเร็จ', null);

        } catch (\Throwable $e) {
            DB::rollback();
            return $this->returnErrorData('เกิดข้อผิดพลาดในการลบ ' . $e->getMessage(), 500);
        }
    }

    public function updateStepJobTypeListStatus(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $status = $request->status;

            if (!in_array($status, ['waiting', 'in_progress', 'completed', 'cancelled'])) {
                return $this->returnErrorData('สถานะไม่ถูกต้อง', 400);
            }

            // 1. หา StepJobsTypeLists ที่จะอัพเดท
            $stepJobTypeList = StepJobsTypeLists::find($id);

            if (!$stepJobTypeList) {
                return $this->returnErrorData('ไม่พบข้อมูล StepJobTypeList', 404);
            }

            // 2. อัพเดทสถานะ
            $stepJobTypeList->status = $status;

            foreach ($request->images as $img) {
                $image = new StepJobTypeListImages();
                $image->step_jobs_type_list_id = $id;
                $image->image = $img;
                $image->save();
            }
            
            $stepJobTypeList->save();

            // 3. หา StepJobs (step) ที่ตัวเองอยู่ในนั้น
            $step = StepJobs::find($stepJobTypeList->step_jobs_id);

            if (!$step) {
                return $this->returnErrorData('ไม่พบข้อมูล StepJobs', 404);
            }

            // 4. เช็คสถานะ step_jobs_type_lists ทั้งหมดใน step นี้
            $stepJobTypeLists = StepJobsTypeLists::where('step_jobs_id', $step->id)->get();

            $allCompleted = $stepJobTypeLists->every(fn($item) => $item->status === 'completed');
            $allCancelled = $stepJobTypeLists->every(fn($item) => $item->status === 'cancelled');
            $hasInProgressOrCompleted = $stepJobTypeLists->contains(fn($item) =>
                in_array($item->status, ['in_progress', 'completed'])
            );

            if ($allCompleted) {
                $step->status = 'completed';
            } elseif ($allCancelled) {
                $step->status = 'cancelled';
            } elseif ($hasInProgressOrCompleted) {
                $step->status = 'in_progress';
            }


            $step->save();

            // 5. หา Jobs (งานแม่)
            $job = Jobs::find($step->job_id);

            if (!$job) {
                return $this->returnErrorData('ไม่พบข้อมูล Jobs', 404);
            }

            // 6. เช็ค StepJobs ทั้งหมดใน Jobs
            $steps = StepJobs::where('job_id', $job->id)->get();

            $allStepsCompleted = $steps->every(fn($item) => $item->status === 'completed');
            $allStepsCancelled = $steps->every(fn($item) => $item->status === 'cancelled');
            $hasStepInProgressOrCompleted = $steps->contains(fn($item) =>
                in_array($item->status, ['in_progress', 'completed'])
            );

            if ($allStepsCompleted) {
                $job->status = 'completed';
            } elseif ($allStepsCancelled) {
                $job->status = 'cancelled';
            } elseif ($hasStepInProgressOrCompleted) {
                $job->status = 'in_progress';
            }
            $job->save();

            DB::commit();

            return $this->returnSuccess('อัพเดทสถานะสำเร็จ', [
                'step_job_type_list' => $stepJobTypeList,
                'step' => $step,
                'job' => $job
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->returnErrorData('เกิดข้อผิดพลาด: ' . $e->getMessage(), 500);
        }
    }

    public function getAllProductExpenseSummaryWithDetails(Request $request)
    {
        $productIds = $request->products;

        if (empty($productIds) || !is_array($productIds)) {
            return $this->returnErrorData('กรุณาระบุ products เป็น array', 400);
        }

        $products = Jobs::whereIn('product_id', $productIds)
            ->distinct()
            ->pluck('product_id')
            ->toArray();

        if (empty($products)) {
            return $this->returnErrorData('ไม่พบข้อมูลรถตามที่ระบุ', 404);
        }

        $results = [];
        $overallTotal = 0;

        foreach ($products as $productId) {
            $product = Products::find($productId);
            $jobs = Jobs::with('steps.stepJobTypeLists.expenses', 'steps.stepJobTypeLists.productAttributes.product_attribute')
                ->where('product_id', $productId)
                ->get();

            $productTotal = 0;
            $jobDetails = [];

            foreach ($jobs as $job) {
                $jobTotal = 0;

                foreach ($job->steps as $step) {
                    foreach ($step->stepJobTypeLists as $stepJob) {
                        foreach ($stepJob->expenses as $expense) {
                            $jobTotal += floatval($expense->amount);
                        }
                        foreach ($stepJob->productAttributes as $attr) {
                            $jobTotal += floatval($attr->amount);
                        }
                    }
                }

                $jobDetails[] = [
                    'job_id' => $job->id,
                    'job_code' => $job->code,
                    'job_total' => $jobTotal
                ];

                $productTotal += $jobTotal;
            }

            $results[] = [
                'product_id' => $productId,
                'product_details' => $product,
                'jobs' => $jobDetails,
                'product_total_expense' => $productTotal
            ];

            $overallTotal += $productTotal;
        }

        return $this->returnSuccess('สรุปรายการรถที่เลือกพร้อมค่าใช้จ่าย', [
            'products' => $results,
            'overall_total_expense' => $overallTotal
        ]);
    }



    public function getAllProductExpenseSummaryWithDetails1(Request $request)
    {
        $productIds = $request->products;

        if (empty($productIds) || !is_array($productIds)) {
            return $this->returnErrorData('กรุณาระบุ products เป็น array', 400);
        }

        // ดึงเฉพาะ product_id ที่มีอยู่จริงใน jobs
        $products = Jobs::whereIn('product_id', $productIds)
            ->distinct()
            ->pluck('product_id')
            ->toArray();

        if (empty($products)) {
            return $this->returnErrorData('ไม่พบข้อมูลรถตามที่ระบุ', 404);
        }

        $results = [];

        foreach ($products as $productId) {
            $product = Products::find($productId);
            $jobs = Jobs::where('product_id', $productId)->pluck('id')->toArray();

            $typeExpenses = StepJobsTypeExpense::whereIn('job_id', $jobs)
                ->select('expense_type_id', DB::raw('SUM(amount) as total_amount'))
                ->groupBy('expense_type_id')
                ->get();

            $otherExpenses = JobsExpensesList::whereIn('job_id', $jobs)
                ->select(DB::raw('SUM(amount) as total_amount'))
                ->first();

            $total = $typeExpenses->sum('total_amount') + ($otherExpenses->total_amount ?? 0);

            $results[] = [
                'product_id' => $productId,
                'product_details' => $product,
                'expenses_by_type' => $typeExpenses,
                'other_expenses' => $otherExpenses->total_amount ?? 0,
                'total_expense' => $total
            ];
        }

        return $this->returnSuccess('สรุปรายการรถที่เลือกพร้อมค่าใช้จ่าย', $results);
    }



}
