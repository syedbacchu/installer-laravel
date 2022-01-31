<?php

namespace SdTech\ProjectInstaller\Controllers;

use Illuminate\Routing\Controller;
use SdTech\ProjectInstaller\Events\LaravelInstallerFinished;
use SdTech\ProjectInstaller\Helpers\EnvironmentManager;
use SdTech\ProjectInstaller\Helpers\FinalInstallManager;
use SdTech\ProjectInstaller\Helpers\InstalledFileManager;

class FinalController extends Controller
{
    function __construct()
    {
        set_time_limit(300);
    }

    /**
     * Update installed file and display finished view.
     *
     * @param \SdTech\ProjectInstaller\Helpers\InstalledFileManager $fileManager
     * @param \SdTech\ProjectInstaller\Helpers\FinalInstallManager $finalInstall
     * @param \SdTech\ProjectInstaller\Helpers\EnvironmentManager $environment
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function finish(InstalledFileManager $fileManager, FinalInstallManager $finalInstall, EnvironmentManager $environment)
    {
        $finalMessages = $finalInstall->runFinal();
        $finalStatusMessage = $fileManager->update();
        $finalEnvFile = $environment->getEnvContent();

        event(new LaravelInstallerFinished);

        return view('vendor.installer.finished', compact('finalMessages', 'finalStatusMessage', 'finalEnvFile'));
    }
}
