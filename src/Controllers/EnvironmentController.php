<?php

namespace SdTech\ProjectInstaller\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use SdTech\ProjectInstaller\Events\EnvironmentSaved;
use SdTech\ProjectInstaller\Helpers\EnvironmentManager;

class EnvironmentController extends Controller
{
    /**
     * @var EnvironmentManager
     */
    protected $EnvironmentManager;

    /**
     * @param EnvironmentManager $environmentManager
     */
    public function __construct(EnvironmentManager $environmentManager)
    {
        set_time_limit(300);
        $this->EnvironmentManager = $environmentManager;
    }

    /**
     * Display the Environment menu page.
     *
     * @return \Illuminate\View\View
     */
    public function environmentMenu()
    {
        return view('vendor.installer.environment');
    }

    /**
     * Display the Environment page.
     *
     * @return \Illuminate\View\View
     */
    public function environmentWizard()
    {
        $envConfig = $this->EnvironmentManager->getEnvContent();

        return view('vendor.installer.environment-wizard', compact('envConfig'));
    }

    /**
     * Display the Environment page.
     *
     * @return \Illuminate\View\View
     */
    public function environmentClassic()
    {
        $envConfig = $this->EnvironmentManager->getEnvContent();

        return view('vendor.installer.environment-classic', compact('envConfig'));
    }

    /**
     * Processes the newly saved environment configuration (Classic).
     *
     * @param Request $input
     * @param Redirector $redirect
     * @return \Illuminate\Http\RedirectResponse
     */
    public function saveClassic(Request $input, Redirector $redirect)
    {
        $message = $this->EnvironmentManager->saveFileClassic($input);

        event(new EnvironmentSaved($input));

        return $redirect->route('LaravelInstaller::environmentClassic')
                        ->with(['message' => $message]);
    }

    /**
     * Processes the newly saved environment configuration (Form Wizard).
     *
     * @param Request $request
     * @param Redirector $redirect
     * @return \Illuminate\Http\RedirectResponse
     */
    public function saveWizard(Request $request, Redirector $redirect)
    {
        $rules = config('installer.environment.form.rules');
        $messages = [
            'environment_custom.required_if' => trans('installer_messages.environment.wizard.form.name_required'),
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return $redirect->route('LaravelInstaller::environmentWizard')->withInput()->withErrors($validator->errors());
        }

        if (! $this->checkDatabaseConnection($request)) {
            return $redirect->route('LaravelInstaller::environmentWizard')->withInput()->withErrors([
                'database_connection' => trans('installer_messages.environment.wizard.form.db_connection_failed'),
            ]);
        }

//         if (! $this->checkForAuthentication($request)) {
//             return $redirect->route('LaravelInstaller::environmentWizard')->withInput()->withErrors([
//                 'envato_purchase_code' => trans('installer_messages.environment.wizard.form.envato_purchase_code_failed'),
//             ]);
//         }

        $results = $this->EnvironmentManager->saveFileWizard($request);

        event(new EnvironmentSaved($request));

        return $redirect->route('LaravelInstaller::database', ['email' => $request->admin_email, 'password' => $request->admin_password])
                        ->with(['results' => $results]);
    }

    /**
     * @param Request $request
     * @return bool
     */
    private function checkForAuthentication(Request $request)
    {
        $license = $request->input('envato_purchase_code') ?? Cookie::get('addenvparkey');
        if ($license == '') return false;
        $domain = url('/');
        $a = config('installer.updater_url', 'http://149.28.199.74') . '/register';
        try {
            return true;
            $ch = curl_init($a);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['app' => config('app.project', 'laravel'), 'version' => config('app.version', 'v1.0'), 'license' => $license, 'domain' => $domain, 'email' => $request->admin_email]));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $response = curl_exec($ch);
            curl_close($ch);
            $res = json_decode($response);
            if (is_object($res) && $res->success) {
                return true;
            }
            return false;
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     *
     * Validate database connection with user credentials (Form Wizard).
     *
     * @param Request $request
     * @return bool
     */
    private function checkDatabaseConnection(Request $request)
    {
        $connection = 'mysql';

        $settings = config("database.connections.$connection");

        config([
            'database' => [
                'default' => $connection,
                'connections' => [
                    $connection => array_merge($settings, [
                        'driver' => $connection,
                        'host' => $request->input('database_hostname'),
                        'port' => $request->input('database_port'),
                        'database' => $request->input('database_name'),
                        'username' => $request->input('database_username'),
                        'password' => $request->input('database_password'),
                    ]),
                ],
            ],
        ]);

        DB::purge();

        try {
            DB::connection()->getPdo();

            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
