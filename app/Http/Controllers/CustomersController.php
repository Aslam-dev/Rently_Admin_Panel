<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\InterestedUser;
use Illuminate\Http\Request;

class CustomersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        return view('customer.index');
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
        if (!has_permissions('update', 'customer')) {
            $response['error'] = true;
            $response['message'] = PERMISSION_ERROR_MSG;
            return response()->json($response);
        } else {

            Customer::where('id', $request->id)->update(['isActive' => $request->status]);



            $fcm_ids = array();

            $customer_id = Customer::where('id', $request->id)->where('isActive', '1')->where('notification', 1)->get();
            if (count($customer_id)) {
                $user_token = Usertokens::where('customer_id', $request->id)->select('id', 'fcm_id')->get()->pluck('fcm_id')->toArray();
                $fcm_ids[] = $user_token;
            }


            $msg = "";
            if (!empty($fcm_ids)) {
                $msg =
                    $request->status == 1 ? 'Activate now by Adminstrator ' : 'Deactive now by Adminstrator ';
                $type = $request->status == 1 ? 'account_activated' : 'account_deactivated';
                $full_msg = $request->status == 1 ? 'Your Account' . $msg : 'Please Contact to Administrator';
                $registrationIDs = $fcm_ids[0];

                $fcmMsg = array(
                    'title' =>  'Your Account' . $msg,
                    'message' => $full_msg,
                    'type' => $type,
                    'body' => 'Your Account'  . $msg,
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    'sound' => 'default',


                );
                send_push_notification($registrationIDs, $fcmMsg);
            }

            $response['error'] = false;
            return response()->json($response);
        }
    }




    public function customerList()
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

        if (isset($_GET['property_id'])) {
            $interested_users =  InterestedUser::select('customer_id')->where('property_id', $_GET['property_id'])->pluck('customer_id');
            // dd($interested_users);
            $sql = Customer::whereIn('id', $interested_users)->orderBy($sort, $order);
        } else {

            $sql = Customer::orderBy($sort, $order);
        }



        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $search = $_GET['search'];
            $sql->where('id', 'LIKE', "%$search%")->orwhere('email', 'LIKE', "%$search%")->orwhere('name', 'LIKE', "%$search%")->orwhere('mobile', 'LIKE', "%$search%");
        }


        $total = $sql->count();

        if (isset($_GET['limit'])) {
            $sql->skip($offset)->take($limit);
        }


        $res = $sql->get();

        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();
        $count = 1;


        $operate = '';
        foreach ($res as $row) {
            $tempRow['id'] = $row->id;
            $tempRow['name'] = $row->name;
            $tempRow['email'] = $row->email;
            $tempRow['mobile'] = $row->mobile;
            $tempRow['address'] = $row->address;
            $tempRow['firebase_id'] = $row->firebase_id;
            $tempRow['isActive'] = ($row->isActive == '0') ? '<span class="badge rounded-pill bg-danger">Inactive</span>' : '<span class="badge rounded-pill bg-success">Active</span>';
            $tempRow['profile'] = ($row->profile != '') ? '<a class="image-popup-no-margins" href="' . $row->profile . '" width="55" height="55"><img class="rounded avatar-md shadow img-fluid" alt="" src="' . $row->profile . '" width="55" height="55"></a>' : '';
            $status = $row->isActive == '1' ? 'checked' : '';
            $enable_disable =   '<div class="form-check form-switch center" style="padding-left: 5.2rem;">
            <input class="form-check-input switch1" id="' . $row->id . '"  onclick="chk(this);" type="checkbox" role="switch"' . $status . '>

            </div>';
            $tempRow['enable_disable'] = $enable_disable;
            $tempRow['fcm_id'] = $row->fcm_id;
            if ($row->isActive == '0') {
                $operate =   '&nbsp;<a id="' . $row->id . '" class="btn btn-primary btn-sm rounded-pill" onclick="return active(this.id);" title="Enable"><i class="bi bi-eye-fill"></i></a>';
            } else {
                $operate =   '&nbsp;<a id="' . $row->id . '" class="btn btn-danger btn-sm rounded-pill" onclick="return disable(this.id);" title="Disable"><i class="bi bi-eye-slash-fill"></i></a>';
            }

            $tempRow['customertotalpost'] =  '<a href="' . url('property') . '?customer=' . $row->id . '">' . $row->customertotalpost . '</a>';
            $tempRow['operate'] = $operate;
            $rows[] = $tempRow;
            $count++;
        }

        $bulkData['rows'] = $rows;
        return response()->json($bulkData);
    }
}
