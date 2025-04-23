<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\DateUtility;
use App\Models\JobOrder;
use App\Models\Lead;
use App\Models\NewComplaint;
use App\Models\NewQuotation;
use App\Models\PurchaseBill;
use App\Models\PurchaseReturn;
use App\Models\SaleBill;
use App\Models\SaleReturn;
use Illuminate\Support\Facades\Auth;

class DashboardController extends BackendController
{
    public String $routePrefix = "dashboard";

    public function index()
    {
        $auth_user = Auth::user();

        if ($auth_user->isAdmin()) {
            return $this->admin();
        } else {
            return $this->other();
        }
    }

    public function admin()
    {
        $duration_type_list = [
            0 => "Today",
            'last_7_days' => "Last 7 Days",
            'last_15_days' => "Last 15 Days",
            'last_30_days' => "Last 30 Days",
            'last_60_days' => "Last 60 Days",
            'last_90_days' => "Last 90 Days",
            'this_month' => "This Month",
            'this_year' => "This Year",
        ];

        $this->setForView(compact("duration_type_list"));

        return $this->view(__FUNCTION__);
    }

    public function other()
    {
        $auth_user = Auth::user();

        $lead_counters["Follow Up"] = Lead::where("status", "follow_up")->where("follow_up_user_id", $auth_user['id'])->count();

        $lead_counters["Mature"] = Lead::where("status", "mature")->where("follow_up_user_id", $auth_user['id'])->count();
        //////////////////////////////////////////////

        $complaint_counters["Pending"] = NewComplaint::where("status", "pending")->where("assign_to", $auth_user['id'])->count();

        $complaint_counters["In-Progress"] = NewComplaint::where("status", "in_progress")->where("assign_to", $auth_user['id'])->count();

        $complaint_counters["Hold"] = NewComplaint::where("status", "hold")->where("assign_to", $auth_user['id'])->count();

        $complaint_counters["Done"] = NewComplaint::where("status", "done")->where("assign_to", $auth_user['id'])->count();

        /////////////////////////////////////////////////////////
        $quotation_counters["Follow Up"] = NewQuotation::where("status", "follow_up")->where("follow_up_user_id", $auth_user['id'])->count();

        $quotation_counters["Approve"] = NewQuotation::where("status", "approve")->where("follow_up_user_id", $auth_user['id'])->count();

        $quotation_counters["Decline"] = NewQuotation::where("status", "decline")->where("follow_up_user_id", $auth_user['id'])->count();

        $this->setForView(compact("lead_counters", "complaint_counters", "quotation_counters"));

        return $this->view(__FUNCTION__);
    }

    public function ajax_admin_role_counters($duration_type)
    {
        $date = date(DateUtility::DATE_FORMAT);
        if ($duration_type == "last_7_days") {
            $date = DateUtility::change($date, -7, DateUtility::DAYS, DateUtility::DATE_FORMAT);
        } else if ($duration_type == "last_15_days") {
            $date = DateUtility::change($date, -15, DateUtility::DAYS, DateUtility::DATE_FORMAT);
        } else if ($duration_type == "last_30_days") {
            $date = DateUtility::change($date, -30, DateUtility::DAYS, DateUtility::DATE_FORMAT);
        } else if ($duration_type == "last_60_days") {
            $date = DateUtility::change($date, -60, DateUtility::DAYS, DateUtility::DATE_FORMAT);
        } else if ($duration_type == "last_90_days") {
            $date = DateUtility::change($date, -90, DateUtility::DAYS, DateUtility::DATE_FORMAT);
        } else if ($duration_type == "this_month") {
            $date = date("Y-m-01");
        } else if ($duration_type == "this_year") {
            $date = date("Y-01-01");
        }

        // dd($date);

        $lead_counters["Pending"] = Lead::where("status", "pending")->where("date", ">=", $date)->count();

        $lead_counters["Not Interested"] = Lead::where("status", "not_interested")->where("date", ">=", $date)->count();

        $lead_counters["Follow Up"] = Lead::where("status", "follow_up")->where("date", ">=", $date)->count();

        $lead_counters["Mature"] = Lead::where("status", "mature")->where("date", ">=", $date)->count();

        ///////////////////////////////////////////////////////////////////////////////

        $complaint_counters["Pending"] = NewComplaint::where("status", "pending")->where("date", ">=", $date)->count();

        $complaint_counters["In-Progress"] = NewComplaint::where("status", "in_progress")->where("date", ">=", $date)->count();

        $complaint_counters["Hold"] = NewComplaint::where("status", "hold")->where("date", ">=", $date)->count();

        $complaint_counters["Done"] = NewComplaint::where("status", "done")->where("date", ">=", $date)->count();

        ///////////////////////////////////////////////////////////////////////////////

        $quotation_counters["Pending"] = NewQuotation::where("status", "pending")->where("date", ">=", $date)->count();

        $quotation_counters["Follow Up"] = NewQuotation::where("status", "follow_up")->where("date", ">=", $date)->count();

        $quotation_counters["Approve"] = NewQuotation::where("status", "approve")->where("date", ">=", $date)->count();

        $quotation_counters["Decline"] = NewQuotation::where("status", "decline")->where("date", ">=", $date)->count();


        ////////////////////////////////////////////////////////////////////////////        
        $purchase_counters["Purchase Count"] = PurchaseBill::where("bill_date", ">=", $date)->count();
        $purchase_counters["Purchase Amount"] = PurchaseBill::where("bill_date", ">=", $date)->sum("payable_amount");

        $purchase_counters["Purchase Return Count"] = PurchaseReturn::where("voucher_date", ">=", $date)->count();
        $purchase_counters["Purchase Return Amount"] = PurchaseReturn::where("voucher_date", ">=", $date)->sum("receivable_amount");

        ////////////////////////////////////////////////////////////////////////////        
        $sale_counters["Sale Count"] = SaleBill::where("bill_date", ">=", $date)->count();
        $sale_counters["Sale Amount"] = SaleBill::where("bill_date", ">=", $date)->sum("receivable_amount");

        $sale_counters["Sale Return Count"] = SaleReturn::where("voucher_date", ">=", $date)->count();
        $sale_counters["Sale Return Amount"] = SaleReturn::where("voucher_date", ">=", $date)->sum("payable_amount");

        ////////////////////////////////////////////////////////

        $job_order_complete = JobOrder::where("created_at", ">=", $date)->with([
            "party",
            "jobOrderReceive"
        ])
            ->whereHas("jobOrderReceive")
            ->orderBy("id", "DESC")
            ->limit(20)->get();

        $job_order_pending = JobOrder::where("created_at", ">=", $date)->with([
            "party"
        ])
            ->doesntHave("jobOrderReceive")
            ->orderBy("id", "DESC")
            ->limit(20)->get();

        foreach ($job_order_pending as $k => $job_order) {
            $job_order->is_expired = false;
            if (DateUtility::compare(date("Y-m-d"), $job_order->expected_complete_date) > 0) {
                $job_order->is_expired = true;
            }
        }


        $this->setForView(compact(
            "lead_counters",
            "complaint_counters",
            "quotation_counters",
            "purchase_counters",
            "sale_counters",
            "job_order_complete",
            "job_order_pending"
        ));

        return $this->view(__FUNCTION__);
    }
    // public String $routePrefix = "dashbaord";

    // public function index()
    // {
    //     $view_name = "admin";

    //     $msg = "Comming Soon";

    //     $this->setForView(compact("view_name", "msg"));

    //     return $this->view($view_name);
    // }
}
