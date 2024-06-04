<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\parameter;
use App\Models\Type;
use Illuminate\Http\Request;


class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!has_permissions('read', 'categories')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        } else {
            $parameters = parameter::all();
            return view('categories.index', ['parameters' => $parameters]);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!has_permissions('create', 'categories')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        } else {
            $request->validate([
                'image' => 'required|image|mimes:jpg,png,jpeg,svg|max:2048',
                'category' => 'required'
            ]);
            $saveCategories = new Category();
            $destinationPath = public_path('images') . config('global.CATEGORY_IMG_PATH');
            if (!is_dir($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }
            // image upload


            if ($request->hasFile('image')) {
                $profile = $request->file('image');
                $imageName = microtime(true) . "." . $profile->getClientOriginalExtension();
                $profile->move($destinationPath, $imageName);
                $saveCategories->image = $imageName;
            } else {
                $saveCategories->image  = '';
            }

            $saveCategories->category = ($request->category) ? $request->category : '';
            $saveCategories->parameter_types = ($request->parameter_type) ? implode(',', $request->parameter_type) : '';
            $title = $request->category;
            $model = Category::class;
            $saveCategories->slug_id = generateUniqueSlug($title, $model);
            $saveCategories->meta_title = $request->meta_title;
            $saveCategories->meta_description = $request->meta_description;
            $saveCategories->meta_keywords = $request->meta_keywords;

            $saveCategories->save();

            return back()->with('success', 'Category Successfully Added');
        }
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
            'image' => 'mimes:png,jpg,jpeg,svg|max:2048', // Adjust max size as needed
        ], [

            'image.image' => 'The uploaded file must be an image.',
            'image.mimes' => 'The image must be a PNG, JPG, JPEG, or SVG file.',
            'image.max' => 'The image size should not exceed 2MB.', // Adjust as needed
        ]);

        if (!has_permissions('update', 'categories')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        } else {




            $arr = [];
            $parameters = parameter::all();
            foreach ($parameters as $par) {

                if ($request->has($par->name)) {
                    $arr = $arr + [$par->id => $request->input($par->name)];
                }
            }

            $Category = Category::find($request->edit_id);



            $destinationPath = public_path('images') . config('global.CATEGORY_IMG_PATH');
            if (!is_dir($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }
            // image upload


            if ($request->hasFile('edit_image')) {

                $url = $Category->image;
                $relativePath = parse_url($url, PHP_URL_PATH);

                if (file_exists(public_path()  . $relativePath)) {
                    unlink(public_path()  . $relativePath);
                }
                $profile = $request->file('edit_image');
                $imageName = microtime(true) . "." . $profile->getClientOriginalExtension();
                $profile->move($destinationPath, $imageName);
                $Category->image = $imageName;
            }


            $Category->category = $request->edit_category;
            $Category->meta_title = $request->edit_meta_title;
            $Category->meta_description = $request->edit_meta_description;
            $Category->meta_keywords = $request->edit_keywords;

            $Category->sequence = ($request->sequence) ? $request->sequence : 0;
            $Category->parameter_types = $request->update_seq;
            // $Category->parameter_types = implode(",", $request->edit_parameter_type);

            $Category->update();

            return back()->with('success', 'Category Successfully Update');
        }
    }



    public function categoryList()
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



        $sql = Category::orderBy($sort, $order);
        // dd($sql->toArray());
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $search = $_GET['search'];
            $sql->where('id', 'LIKE', "%$search%")->orwhere('category', 'LIKE', "%$search%");
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
        $tempRow['type'] = '';
        $parameter_name_arr = [];
        foreach ($res as $row) {
            $tempRow['id'] = $row->id;
            $tempRow['category'] = $row->category;
            $tempRow['status'] = ($row->status == '0') ? '<span class="badge rounded-pill bg-danger">Inactive</span>' : '<span class="badge rounded-pill bg-success">Active</span>';
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


            $tempRow['sequence'] = $row->sequence;
            $tempRow['meta_title'] = $row->meta_title;
            $tempRow['meta_description'] = $row->meta_description;
            $tempRow['meta_keywords'] = $row->meta_keywords;



            $parameter_type_arr = explode(',', $row->parameter_types);

            $arr = [];

            if ($row->parameter_types) {
                foreach ($parameter_type_arr as $p) {
                    $par = parameter::find($p);
                    if ($par) {
                        $arr = array_merge($arr, [$par->name]);
                    }
                }
            }



            $tempRow['type'] = implode(',', $arr);

            $ids = isset($row->parameter_types) ? $row->parameter_types : '';
            $operate = '&nbsp;&nbsp;<a  id="' . $row->id . '"  class="btn icon btn-primary btn-sm rounded-pill edit_btn" data-status="' . $row->status . '" data-oldimage="' . $row->image . '" data-types="' . $ids . '" data-bs-toggle="modal" data-bs-target="#editModal"  onclick="setValue(this.id);"  title="Edit"><i class="fa fa-edit edit_icon"></i></a>';

            $status = $row->status == '1' ? 'checked' : '';
            $enable_disable =   '<div class="form-check form-switch" style="justify-content: center;display: flex;">
            <input class="form-check-input switch1" name="' . $row->id . '"  onclick="chk(this);" type="checkbox" role="switch" ' . $status . '>

            </div>';

            $tempRow['enble_disable'] = $enable_disable;

            $tempRow['operate'] = $operate;

            $rows[] = $tempRow;
            $count++;
        }

        $bulkData['rows'] = $rows;
        return response()->json($bulkData);
    }



    public function updateCategory(Request $request)
    {
        if (!has_permissions('delete', 'categories')) {
            $response['error'] = true;
            $response['message'] = PERMISSION_ERROR_MSG;
            return response()->json($response);
        } else {

            Category::where('id', $request->id)->update(['status' => $request->status]);
            $response['error'] = false;
            return response()->json($response);
        }
    }
}
