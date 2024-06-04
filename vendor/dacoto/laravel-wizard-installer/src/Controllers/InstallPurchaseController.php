<?php

namespace dacoto\LaravelWizardInstaller\Controllers;

use dacoto\SetEnv\Facades\SetEnv;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InstallPurchaseController extends Controller
{
    /**
     * Set database settings
     *
     * @return Application|Factory|RedirectResponse|View
     */
    public function index()
    {
        if ((new InstallServerController())->check() === false || (new InstallFolderController())->check() === false) {
            return redirect()->route('LaravelWizardInstaller::install.folders');
        }
        return view('installer::steps.purchase');
    }

    /**
     * Test database and set keys in .env
     *
     * @param  Request  $request
     * @return Application|Factory|RedirectResponse|View
     */
    public function setPurchase(Request $request)
    {
        SetEnv::setKey('APPSECRET', $request->input('purchase_code'));
        SetEnv::save();
        return redirect()->route('LaravelWizardInstaller::install.database');
    }
}
