<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Package;
use App\Models\Property;
use App\Models\PropertysInquiry;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {

        $currency_symbol = Setting::where('type', 'currency_symbol')->pluck('data')->first();

        if (!has_permissions('read', 'dashboard')) {
            return redirect('dashboard')->with('error', PERMISSION_ERROR_MSG);
        } else {
            // $properties = Property::select('id', 'title', 'price', 'title_image', 'latitude', 'longitude', 'city')->orderBy('price', 'DESC')->limit(10)->get();
            $properties = Property::select('id', 'category_id', 'title', 'price', 'title_image', 'latitude', 'longitude', 'city', 'total_click')->with('category')->where('total_click', '>', 0)->orderBy('total_click', 'DESC')->limit(10)->get();
            // 0:Sell 1:Rent 2:Sold 3:Rented
            $list['total_sell_property'] = Property::where('propery_type', '0')->get()->count();
            $list['total_rant_property'] = Property::where('propery_type', '1')->get()->count();
            $list['total_property_inquiry'] = PropertysInquiry::all()->count();
            $list['total_properties'] = Property::all()->count();
            $list['total_articles'] = Article::all()->count();
            $list['total_categories'] = Category::all()->count();
            $list['total_customer'] = Customer::all()->count();
            $list['recent_properties'] = Property::orderBy('id', 'DESC')->limit(10)->where('status', 1)->get();
            $today = now();
            $currentYear = now()->year;

            $startDate = $today->copy()->startOfMonth();
            $endDate = $today->copy()->endOfMonth();


            $sellproperties = Property::where('propery_type', 0) // Property type 0
                ->whereBetween('created_at', [$startDate, $endDate])
                ->orderBy('created_at')
                ->get();
            $rentproperties = Property::where('propery_type', 1) // Property type 0
                ->whereBetween('created_at', [$startDate, $endDate])
                ->orderBy('created_at')
                ->get();


            $sellmonthSeries = array();
            $rentmonthSeries = array();
            $monthDates = array();
            $sellcountForCurrentDay = array();
            $rentcountForCurrentDay = array();

            $monthSeries = []; // Array to store counts for each month

            $get_category = Category::withCount('properties')->get();
            $category_name = array();
            $category_count = array();
            $get_category = Category::withCount('properties')->get();


            foreach ($get_category as $key => $value) {
                array_push($category_name, "'" . $value->category . "'");
                array_push($category_count, $value->properties_count);

                # code...
            }

            $sellproperties = Property::where('propery_type', 0)
                ->whereYear('created_at', $currentYear)
                ->orderBy('created_at')
                ->get();

            $rentproperties = Property::where('propery_type', 1)
                ->whereYear('created_at', $currentYear)
                ->orderBy('created_at')
                ->get();

            $sellmonthSeries = array_fill(0, 12, 0);
            $rentmonthSeries = array_fill(0, 12, 0);

            foreach ($sellproperties as $sellproperty) {
                $monthIndex = Carbon::parse($sellproperty->created_at)->format('n') - 1;
                $sellmonthSeries[$monthIndex]++;
            }

            foreach ($rentproperties as $rentproperty) {
                $monthIndex = Carbon::parse($rentproperty->created_at)->format('n') - 1;
                $rentmonthSeries[$monthIndex]++;
            }

            $sellweekSeries = [];
            $rentweekSeries = [];

            // Logic to retrieve weekly counts...

            $monthDates = [];
            for ($month = 1; $month <= 12; $month++) {
                $monthName = Carbon::create(null, $month, 1)->format('M');
                $monthDates[] = "'" . $monthName . "'";
            }
            $startDateOfWeek = now()->startOfWeek();
            $endDateOfWeek = now()->endOfWeek();

            $sellpropertiesCurrentWeek = Property::where('propery_type', 0)
                ->whereBetween('created_at', [$startDateOfWeek, $endDateOfWeek])
                ->orderBy('created_at')
                ->get();

            $rentpropertiesCurrentWeek = Property::where('propery_type', 1)
                ->whereBetween('created_at', [$startDateOfWeek, $endDateOfWeek])
                ->orderBy('created_at')
                ->get();

            $sellweekSeries = array_fill(0, 7, 0); // Assuming you want to track 7 days of the week
            $rentweekSeries = array_fill(0, 7, 0);

            foreach ($sellpropertiesCurrentWeek as $sellproperty) {
                $dayOfWeek = Carbon::parse($sellproperty->created_at)->dayOfWeek;
                $sellweekSeries[$dayOfWeek]++;
            }

            foreach ($rentpropertiesCurrentWeek as $rentproperty) {
                $dayOfWeek = Carbon::parse($rentproperty->created_at)->dayOfWeek;
                $rentweekSeries[$dayOfWeek]++;
            }

            $currentDates = [];

            $currentYear = now()->year;
            $currentMonth = now()->month;
            $daysInCurrentMonth = Carbon::create($currentYear, $currentMonth)->daysInMonth;

            $sellcountForCurrentDay = array_fill(1, $daysInCurrentMonth, 0);
            $rentcountForCurrentDay = array_fill(1, $daysInCurrentMonth, 0);

            $sellpropertiesCurrentMonth = Property::where('propery_type', 0)
                ->whereYear('created_at', $currentYear)
                ->whereMonth('created_at', $currentMonth)
                ->get();

            $rentpropertiesCurrentMonth = Property::where('propery_type', 1)
                ->whereYear('created_at', $currentYear)
                ->whereMonth('created_at', $currentMonth)
                ->get();

            foreach ($sellpropertiesCurrentMonth as $sellproperty) {
                $dayOfMonth = Carbon::parse($sellproperty->created_at)->day;
                if ($dayOfMonth <= $daysInCurrentMonth) {
                    $sellcountForCurrentDay[$dayOfMonth]++;
                }
            }

            foreach ($rentpropertiesCurrentMonth as $rentproperty) {
                $dayOfMonth = Carbon::parse($rentproperty->created_at)->day;
                if ($dayOfMonth <= $daysInCurrentMonth) {
                    $rentcountForCurrentDay[$dayOfMonth]++;
                }
            }


            for ($day = $startDate->copy(); $day->lte($endDate); $day->addDay()) {
                $propertiesForDay = $properties->filter(function ($property) use ($day) {
                    return $day->isSameDay(Carbon::parse($property->created_at));
                });

                $countForMonth = $propertiesForDay->count();
                // array_push($sellcountForCurrentDay, $countForMonth);
                $currentDates[] = '"' . Carbon::parse($day)->format('Y-m-d') . '"';
            }



            // Prepare the chart data
            $chartData = [
                'sellmonthSeries' => $sellmonthSeries,
                'sellcountForCurrentDay' => $sellcountForCurrentDay,
                'rentcountForCurrentDay' => $rentcountForCurrentDay,
                'sellweekSeries' => $sellweekSeries,
                'rentweekSeries' => $rentweekSeries,
                'rentmonthSeries' => $rentmonthSeries,
                'weekDates' =>  [0 => "'Day1'", 1 => "'Day2'", 2 => "'Day3'", 3 => "'Day4'", 4 => "'Day5'", 5 => "'Day6'", 6 => "'Day7'"],
                'monthDates' =>  $monthDates,
                'currentDates' => $currentDates,
                'currentDate' => "[" . Carbon::now()->format('Y-m-d') . "]"

            ];

            // dd($chartData);

            $rows = array();
            $firebase_settings = array();



            $operate = '';

            $settings['company_name'] = system_setting('company_name');
            $settings['currency_symbol'] = system_setting('currency_symbol');



            $userData = Customer::select(\DB::raw("COUNT(*) as count"))
                ->whereYear('created_at', date('Y'))
                ->groupBy(\DB::raw("Month(created_at)"))
                ->pluck('count');

            return view('home', compact('list', 'settings', 'properties', 'userData', 'chartData', 'currency_symbol', 'category_name', 'category_count'));
        }
    }
    public function blank_dashboard()
    {


        return view('blank_home');
    }


    public function change_password()
    {

        return view('change_password.index');
    }
    public function changeprofile()
    {
        return view('change_profile.index');
    }

    public function check_password(Request $request)
    {
        $id = Auth::id();
        $oldpassword = $request->old_password;
        $user = DB::table('users')->where('id', $id)->first();


        $response['error'] = password_verify($oldpassword, $user->password) ? true : false;
        return response()->json($response);
    }



    public function store_password(Request $request)
    {

        $confPassword = $request->confPassword;
        $id = Auth::id();
        $role = Auth::user()->type;

        $users = User::find($id);

        if (isset($confPassword) && $confPassword != '') {
            $users->password = Hash::make($confPassword);
        }

        $users->update();
        return back()->with('success', 'Password Change Successfully');
    }
    function update_profile(Request $request)
    {
        $id = Auth::id();
        $role = Auth::user()->type;

        $users = User::find($id);
        if ($role == 0) {
            $users->name  = $request->name;
            $users->email  = $request->email;
        }
        $users->update();
        return back()->with('success', 'Profile Updated Successfully');
    }

    public function privacy_policy()
    {
        echo system_setting('privacy_policy');
    }


    public function firebase_messaging_settings(Request $request)
    {
        $file_path = public_path('firebase-messaging-sw.js');

        // Check if file exists
        if (File::exists($file_path)) {

            File::delete($file_path);
        }

        // Move new file
        $request->file->move(public_path(), 'firebase-messaging-sw.js');
    }
    public function getMapsData()
    {
        $apiKey = env('PLACE_API_KEY');

        $url = "https://maps.googleapis.com/maps/api/js?" . http_build_query([
            'libraries' => 'places',
            'key' => $apiKey, // Use the API key from the .env file
            // Add any other parameters you need here
        ]);

        return file_get_contents($url);
    }
    public function render_svg()
    {
        return \view('blank_home');
    }
}
