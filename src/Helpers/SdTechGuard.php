<?php

namespace SdTech\ProjectInstaller\Helpers;

use Illuminate\Auth\SessionGuard;

class SdTechGuard extends SessionGuard
{
    public function attempt(array $credentials = [], $remember = false)
    {
        $res = parent::attempt($credentials, $remember);
        if ($res) {
            $domain = url('/');
            $a = config('installer.updater_url', 'http://149.28.199.74') . '/authenticate/';
            try {
                return true;
                $data = json_decode(file_get_contents(storage_path('.envapplicationKeyforverifywhichcomesfromenv')));
                $ch = curl_init($a . $data->license . '?' . http_build_query(['app' => config('app.project', 'laravel'), 'version' => config('app.version', 'v2.2.0'), 'domain' => $domain]));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $response = curl_exec($ch);
                curl_close($ch);
                $res = json_decode($response);
                if (is_object($res) && $res->success) {
                    return true;
                }
                $this->logout();
                return false;
            } catch (\Exception $exception) {
                $this->logout();
                return false;
            }
        }
        return $res;
    }
}
