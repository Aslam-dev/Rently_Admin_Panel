<?php

namespace App\Http\Controllers;

use App\Models\AssignedOutdoorFacilities;
use App\Models\OutdoorFacilities;
use Illuminate\Http\Request;

class OutdoorFacilityController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        return view('OutdoorFacilities.index');
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:png,jpg,jpeg,svg|max:2048', // Adjust max size as needed
        ], [
            'image.required' => 'The image field is required.',
            'image.image' => 'The uploaded file must be an image.',
            'image.mimes' => 'The image must be a PNG, JPG, JPEG, or SVG file.',
            'image.max' => 'The image size should not exceed 2MB.', // Adjust as needed
        ]);


        $destinationPath = public_path('images') . config('global.FACILITY_IMAGE_PATH');
        if (!is_dir($destinationPath)) {
            mkdir($destinationPath, 0777, true);
        }

        $facility = new OutdoorFacilities();
        $facility->name = $request->facility;
        if ($request->hasFile('image')) {
            $profile = $request->file('image');
            $imageName = microtime(true) . "." . $profile->getClientOriginalExtension();
            $profile->move($destinationPath, $imageName);
            $facility->image = $imageName;
        } else {
            $facility->image  = '';
        }
        $facility->save();


        return redirect()->back()->with('success', 'Near by Place Successfully Added');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        //
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

        $sql = OutdoorFacilities::orderBy($sort, $order);

        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $search = $_GET['search'];
            $sql->where('id', 'LIKE', "%$search%")->orwhere('name', 'LIKE', "%$search%");
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

            if (!empty(system_setting('svg_clr')) && system_setting('svg_clr') == 1) {

                $imageUrl = $row->image;
                $headers = @get_headers($imageUrl);
                if ($headers && strpos($headers[0], '200')) {

                    if (pathinfo($row->image, PATHINFO_EXTENSION) === 'svg') {
                        $tempRow['image'] = ($row->image != '') ? '<embed class="svg-img" src="' . $row->image . '">' : '';
                    }
                }
            } else {
                $tempRow['image'] = ($row->image != '') ? '<a class="image-popup-no-margins" href="' . $row->image . '"><img class="rounded avatar-md shadow img-fluid" alt="" src="' . $row->image . '" width="55"></a>' : '';
            }
            if (has_permissions('update', 'type')) {
                $operate = '<a  id="' . $row->id . '"  class="btn icon btn-primary btn-sm rounded-pill edit_btn"  data-bs-toggle="modal" data-bs-target="#editModal"  onclick="setValue(this.id);" title="Edit"><i class="fa fa-edit edit_icon"></i></a>&nbsp;&nbsp;';
                $operate .= '<a href="' . route('outdoor_facilities.destroy', $row->id) . '" onclick="return confirmationDelete(event);" class="btn icon btn-danger btn-sm rounded-pill " data-bs-toggle="tooltip" id="delete_btn" data-bs-custom-class="tooltip-dark" title="Delete"><i class="bi bi-trash delete_icon"></i></a>';

                $tempRow['operate'] = $operate;
            }
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

        $request->validate([
            'image' => 'required|image|mimes:png,jpg,jpeg,svg|max:2048', // Adjust max size as needed
        ], [
            'image.required' => 'The image field is required.',
            'image.image' => 'The uploaded file must be an image.',
            'image.mimes' => 'The image must be a PNG, JPG, JPEG, or SVG file.',
            'image.max' => 'The image size should not exceed 2MB.', // Adjust as needed
        ]);


        if (!has_permissions('update', 'type')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        } else {

            $id =  $request->edit_id;
            $facility = OutdoorFacilities::find($id);
            $facility->name = ($request->edit_name) ? $request->edit_name : '';
            $destinationPath = public_path('images') . config('global.FACILITY_IMAGE_PATH');
            if (!is_dir($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }
            if ($request->hasFile('image')) {
                $profile = $request->file('image');
                $imageName = microtime(true) . "." . $profile->getClientOriginalExtension();

                $profile->move($destinationPath, $imageName);

                if ($facility->image != '') {
                    if (file_exists(public_path('images') . config('global.FACILITY_IMAGE_PATH') .  $facility->image)) {
                        unlink(public_path('images') . config('global.FACILITY_IMAGE_PATH') . $facility->image);
                    }
                }
                $facility->image = $imageName;
            }

            $facility->update();


            return back()->with('success', 'Near by Place  Successfully Update');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function destroy($id)
    {

        if (!has_permissions('delete', 'property')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        } else {
            $facility = OutdoorFacilities::find($id);

            AssignedOutdoorFacilities::where('facility_id')->delete();
            if ($facility->delete()) {

                if ($facility->image != '') {
                    if (file_exists(public_path('images') . config('global.FACILITY_IMAGE_PATH') . $facility->image)) {
                        unlink(public_path('images') . config('global.FACILITY_IMAGE_PATH') . $facility->image);
                    }
                }
                return back()->with('success', 'Facility Deleted Successfully');
            } else {
                return back()->with('error', 'Something Wrong');
            }
        }
    }
}
