<?php

namespace SdTech\ProjectInstaller\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Validator;
use SdTech\ProjectInstaller\Helpers\PermissionsChecker;
use Illuminate\Validation\Rule;

class PermissionsController extends Controller
{

    /**
     * @var PermissionsChecker
     */
    protected $permissions;
    protected $token;
    protected $envUrl;

    /**
     * @param PermissionsChecker $checker
     */
    public function __construct(PermissionsChecker $checker)
    {
        $this->permissions = $checker;
        $this->token = config('installer.env_path.env_token');
        $this->envUrl = config('installer.env_path.env_url_path');
    }

    /**
     * Display the permissions check page.
     *
     * @return \Illuminate\View\View
     */
    public function permissions()
    {
        $permissions = $this->permissions->check(
            config('installer.permissions')
        );

        return view('vendor.installer.permissions', compact('permissions'));
    }

    public function verify()
    {
        $permissions = $this->permissions->check(
            config('installer.permissions')
        );

        return view('vendor.installer.verify', compact('permissions'));
    }

    public function codeVerifyProcess(Request $request)
    {
        $rules = ['purchase_code' => 'required'];
        $messages = [
            'purchase_code.required' => __('Purchase code field is required.'),
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            $errors = $validator->errors();
//            return view('vendor.installer.verify', compact('errors'));
            return redirect()->back()->with(['errors' => $errors]);
        } else {
            $check = $this->checkEnvatoPurchaseCode($request);
            if ($check['success'] == false) {
                return redirect()->back()->with(['message' => $check['message']]);
            } else {
                return redirect()->route('LaravelInstaller::environment')->with('message', $check['message']);
            }
        }
    }

    // check envato purchase code
    public function checkEnvatoPurchaseCode($request)
    {
        //SETUP THE API DATA
        $response = ['success' => false, 'message' => __('Invalid request')];
        try {
            $token = $this->token;
            $purchase_code = $request->purchase_code;

            $purchase_code = htmlspecialchars($purchase_code);
            $o = $this->verifyPurchase($purchase_code, $token);
            if (is_object($o)) {
//                $this->update_or_create('is_authenticated',LICENSE_VERIFIED);

                $response = ['success' => true, 'message' => __('Purchase code verified successfully.')];
                $this->verifyMessages($purchase_code);
            } else {
                $response = ['success' => false, 'message' => __('Sorry, This is not a valid purchase code or this user have not purchased any of your items.')];
            }
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => $e->getMessage()];
        }

        return $response;
    }

    public function verifyPurchase($code, $token)
    {
        $verify_obj = $this->getPurchaseData($code, $token);

        // Check for correct verify code
        if (
            (false === $verify_obj) ||
            !is_object($verify_obj) ||
            !isset($verify_obj->{"verify-purchase"}) ||
            !isset($verify_obj->{"verify-purchase"}->item_name)
        )
            return -1;

        // If empty or date present, then it's valid
        if (
            $verify_obj->{"verify-purchase"}->supported_until == "" ||
            $verify_obj->{"verify-purchase"}->supported_until != null
        )
            return $verify_obj->{"verify-purchase"};

        // Null or something non-string value, thus support period over
        return 0;

    }

    public function getPurchaseData($code, $token)
    {

        //setting the header for the rest of the api
        $bearer = 'bearer ' . $token;
        $header = array();
        $header[] = 'Content-length: 0';
        $header[] = 'Content-type: application/json; charset=utf-8';
        $header[] = 'Authorization: ' . $bearer;

        $verify_url = $this->envUrl. $code . '.json';
        $ch_verify = curl_init($verify_url . '?code=' . $code);

        curl_setopt($ch_verify, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch_verify, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch_verify, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch_verify, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch_verify, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');

        $cinit_verify_data = curl_exec($ch_verify);
        curl_close($ch_verify);

        if ($cinit_verify_data != "")
            return json_decode($cinit_verify_data);
        else
            return false;

    }

    public function verifyMessages($envPharseKey)
    {
        Cookie::queue('addenvparkey', $envPharseKey);
    }

}
