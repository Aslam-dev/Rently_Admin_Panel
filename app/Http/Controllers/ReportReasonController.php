<?php

namespace App\Http\Controllers;

use App\Models\report_reasons;
use App\Models\user_reports;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportReasonController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        return view('reports.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }
    public function users_reports()
    {
        return view('reports.user_reports');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $report_reason = new report_reasons();
        $report_reason->reason = $request->reason;
        $report_reason->save();
        return back()->with('success', 'Reason Successfully Added');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        $offset = 0;
        $limit = 10;
        $sort = 'id';
        $order = 'DESC';

        if (isset($_GET['offset'])) {
            $offset = $_GET['offset'];
        }

        if (isset($_GET['limit'])) {
            $limit = $_GET['limit'];
        }

        if (isset($_GET['sort'])) {
            $sort = $_GET['sort'];
        }

        if (isset($_GET['order'])) {
            $order = $_GET['order'];
        }



        $sql = report_reasons::orderBy($sort, $order);
        // dd($sql->toArray());


        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $search = $_GET['search'];
            $sql->where('id', 'LIKE', "%$search%")->orwhere('reason', 'LIKE', "%$search%");
        }


        $total = $sql->count();

        if (isset($_GET['limit'])) {
            $sql->skip($offset)->take($limit);
        }


        $res = $sql->get();
        // return $res;
        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();
        $count = 1;


        $operate = '';


        foreach ($res as $row) {
            $tempRow['id'] = $row->id;
            $tempRow['reason'] = $row->reason;

            $operate = '<a  id="' . $row->id . '"  class="btn icon btn-primary btn-sm rounded-pill mt-2 edit_btn"  data-bs-toggle="modal" data-bs-target="#editModal"  onclick="setValue(this.id);" title="Edit"><i class="fa fa-edit edit_icon"></i></a>';
            $operate .= '&nbsp;<a href="' . route('reasons.destroy', $row->id) . '" onclick="return confirmationDelete(event);" class="btn icon btn-danger btn-sm rounded-pill mt-2" id="delete_btn" data-bs-toggle="tooltip" data-bs-custom-class="tooltip-dark" title="Delete"><i class="bi bi-trash delete_icon"></i></a>';

            $tempRow['operate'] = $operate;
            $rows[] = $tempRow;
            $count++;
        }

        $bulkData['rows'] = $rows;
        return response()->json($bulkData);
    }
    public function user_reports_list()
    {

        $offset = 0;
        $limit = 10;
        $sort = 'id';
        $order = 'DESC';

        if (isset($_GET['offset'])) {
            $offset = $_GET['offset'];
        }

        if (isset($_GET['limit'])) {
            $limit = $_GET['limit'];
        }

        if (isset($_GET['sort'])) {
            $sort = $_GET['sort'];
        }

        if (isset($_GET['order'])) {
            $order = $_GET['order'];
        }
        if ($sort == "reason") {


            $sql = user_reports::with('customer')->with(['reason' => function ($q) use ($order) {

                $q->orderBy('reason', $order);
            }])->with('property.category');
        } else if ($sort == "customer_name") {
            $sql = user_reports::with('reason')->with(['customer' => function ($q) use ($order) {

                $q->orderBy('name', $order);
            }])->with('property.category');
        } else {
            $sql = user_reports::with('customer')->with('reason')->with('property.category')->orderBy($sort, $order);
        }



        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $search = $_GET['search'];
            $sql->where('id', 'LIKE', "%$search%")->orwherehas('reason', function ($q1) use ($search) {
                $q1->where('reason', 'LIKE', "%$search%");
            })->orwherehas('customer', function ($q1) use ($search) {
                $q1->where('name', 'LIKE', "%$search%");
            });
        }


        $total = $sql->count();

        if (isset($_GET['limit'])) {
            $sql->skip($offset)->take($limit);
        }


        $res = $sql->get();
        // return $res;
        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();
        $count = 1;
        $operate = '';
        // dd($res->toArray());

        foreach ($res as $row) {
            $tempRow['id'] = $row->id;
            if ($row->reason_id == 0) {
                $tempRow['reason'] = $row->other_message;
            } else {

                $tempRow['reason'] = $row->reason->reason;
            }
            $tempRow['property'] = $row->property;
            $tempRow['customer_name'] = !empty($row->customer) ? $row->customer->name : '';
            $tempRow['property_title']
                =  '<a class="btn icon btn-info btn-sm rounded-pill view-property"  data-bs-toggle="modal" data-bs-target="#ViewPropertyModal"   title="View Property"><i class="bi bi-building"></i></a>&nbsp;&nbsp;';

            $operate = '&nbsp;&nbsp;<a  id="' . $row->id . '"  class="btn icon btn-primary btn-sm rounded-pill mt-2"  data-bs-toggle="modal" data-bs-target="#editModal"  onclick="setValue(this.id);" title="Edit"><i class="fa fa-edit"></i></a>';
            $operate .= '&nbsp;&nbsp;<a href="' . route('reasons.destroy', $row->id) . '" onclick="return confirmationDelete(event);" class="btn icon btn-danger btn-sm rounded-pill mt-2" data-bs-toggle="tooltip" data-bs-custom-class="tooltip-dark" title="Delete"><i class="bi bi-trash"></i></a>';
            $tempRow['operate'] = $operate;
            $rows[] = $tempRow;
            $count++;
        }

        $bulkData['rows'] = $rows;
        return response()->json($bulkData);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $report_reason = report_reasons::find($request->edit_id);
        $report_reason->reason = $request->edit_reason;
        $report_reason->save();
        return back()->with('success', 'Reason Successfully Updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        if (env('DEMO_MODE') && Auth::user()->email != "superadmin@gmail.com") {
            return redirect()->back()->with('error', 'This is not allowed in the Demo Version');
        }
        if (!has_permissions('delete', 'property')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        } else {
            $report_reason = report_reasons::find($id);


            if ($report_reason->delete()) {
                user_reports::where('reason_id', $id)->delete();

                return back()->with('success', 'Reason Deleted Successfully');
            } else {
                return back()->with('error', 'Something Wrong');
            }
        }
    }
}
