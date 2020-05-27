<?php

namespace local_dynamic_cohorts;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/dynamic_cohorts/vendor/autoload.php');

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;

class aadgroups {

    private static $instance = null;

    private $graph;

    private function __construct() {
        $config = [
            'tenant_id' => get_config('local_dynamic_cohorts', 'aad_tenant_id'),
            'client_id' => get_config('local_dynamic_cohorts', 'aad_client_id'),
            'client_secret' => get_config('local_dynamic_cohorts', 'aad_client_secret'),
        ];

        $guzzle = new \GuzzleHttp\Client();

        $url = 'https://login.microsoftonline.com/' . $config['tenant_id']. '/oauth2/token?api-version=1.0';
        $token = json_decode($guzzle->post($url, [
            'form_params' => [
                'client_id' => $config['client_id'],
                'client_secret' => $config['client_secret'],
                'resource' => 'https://graph.microsoft.com/',
                'grant_type' => 'client_credentials',
            ],
        ])->getBody()->getContents());
        $accesstoken = !empty($token->access_token) ? $token->access_token : null;
        $this->graph = new Graph();
        $this->graph->setAccessToken($accesstoken);

    }

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    public static function check_group($groupid) {
        $instance = self::get_instance();

        try {
            $instance->graph->createRequest('GET', '/groups/' . $groupid)
                            ->setReturnType(Model\Group::class)
                            ->execute();
        } catch(\Exception $e) {
            return false;
        }

        return true;
    }

    public static function get_group_members($groupid, $enabledonly = true) {
        $instance = self::get_instance();

        try {
            $members = $instance->graph->createRequest('GET', '/groups/' . $groupid . '/members?$select=userPrincipalName,employeeId,accountEnabled')
                                       ->setReturnType(Model\User::class)
                                       ->execute();
        } catch(\Exception $e) {
            return [];
        }

        // Filter out disabled users (if required) and/or those with no employee id.
        $return = [];
        foreach ($members as $member) {
            if ($enabledonly && empty($member->getAccountEnabled())) {
                continue;
            } elseif (empty($member->getEmployeeId())) {
                continue;
            }
            $return[$member->getEmployeeId()] = $member->getUserPrincipalName();
        }
        return $return;
    }
}