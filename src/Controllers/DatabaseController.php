<?php

namespace SdTech\ProjectInstaller\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use SdTech\ProjectInstaller\Helpers\DatabaseManager;
use SdTech\ProjectInstaller\Events\AddingInstallerSuperAdmin;

class DatabaseController extends Controller
{
    /**
     * @var DatabaseManager
     */
    private $databaseManager;

    /**
     * @param DatabaseManager $databaseManager
     */
    public function __construct(DatabaseManager $databaseManager)
    {
        set_time_limit(300);
        $this->databaseManager = $databaseManager;
    }

    /**
     * Migrate and seed the database.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function database(Request $request)
    {
        $response = $this->databaseManager->migrateAndSeed();
        event(new AddingInstallerSuperAdmin($request));
        return redirect()->route('LaravelInstaller::final')
                         ->with(['message' => $response]);
    }
}
