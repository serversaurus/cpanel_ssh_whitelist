<?php
/**
 * Add User declared IP's to hosts.allow.
 * User will add an IP with a label which will subsequently be stored in a file in
 * the user directory and the IP added to hosts allow.
 *
 * PHP version 5
 *
 * @category  Security
 * @package   Whitelist
 * @author    David Ford <djfordz@gmail.com>
 * @copyright 2017 Transgress Inc.
 * @license   The MIT License(MIT) https://tldrlegal.com/license/mit-license#fulltext
 * @version   Release: 1.2
 * @link      https://github.com/djfordz/cpanel_ssh_whitelist/releases
 */
class Nemj_Whitelist
{

    /**
     * File hosts.allow path
     *
     * @var string
     */
    const HOSTS_PATH = '/etc/hosts.allow';

    /**
     * UserPath
     *
     * @var string
     */
    protected $userPath = null;

    /**
     * Cpanel object
     *
     * @var object
     */
    protected $cpanel = null;

    /**
     * Constructs whitelist object, passes in cpanel object.
     *
     * @param object $cpanel pass in cpanel object
     *
     * @return void
     */
    public function __construct($cpanel)
    {
        $processUser = posix_getpwuid(posix_geteuid());
        $user = $processUser['name'];
        $this->cpanel = $cpanel;

        $this->userPath = "/home/$user/.users.allow";
    }

    /**
     * Get a list of IP's from user path.
     *
     * @return array|null
     */
    public function getIps()
    {
        if (is_file($this->userPath)) {
            $hosts = file_get_contents($this->userPath);
            $ips = array();
            $d = explode("\n", $hosts);

            foreach ($d as $v) {
                if ($v !== '') {
                    list($l, $i) = explode(':', $v);
                    $ips[] = array($l => $i);
                }
            }
            return $ips;
        } else {
            return null;
        }
    }

    /**
     * Add IP to user file and hosts.allow.
     *
     * @param string $label user declared label
     * @param string $ip    user declared ip
     *
     * @return none
     */
    public function addIp($label, $ip)
    {
        $label = urldecode($label);
        $hosts = '';
        $path = $this->userPath;

        if (!$this->isIp($ip)) {
            $this->error(2);
            return false;
        }

        if (is_file($this->userPath)) {
            $hosts = file_get_contents($path);
        } else {
            $this->cpanel->uapi(
                'NemjWhitelist', 'write_hosts',
                array(
                    'path' => $path,
                    'hosts' => ''
                )
            );
        }

        if (strpos($hosts, $ip)) {
            $this->error(1);
            return;
        }

        $h = explode("\n", $hosts);
        $h[] = $label . ":" . $ip;
        $h = array_filter($h);

        $user_allow = implode("\n", $h);

        $this->cpanel->uapi(
            'NemjWhitelist', 'write_hosts',
            array(
                'path' => $path,
                'hosts' => $user_allow
            )
        );

        $this->writeHosts($ip, false);

    }

    /**
     * Check if IP is valid
     *
     * @param string $ip user declared IP
     *
     * @return bool
     */
    public function isIp($ip = null)
    {
        if (!$ip or strlen(trim($ip)) == 0) {
            return false;
        }

        $ip = trim($ip);
        if (preg_match("/^[0-9]{1,3}(.[0-9]{1,3}){3}$/", $ip)) {
            foreach (explode(".", $ip) as $block) {
                if ($block < 0 || $block > 255) {
                    return false;
                }
            }
            if($ip == '0.0.0.0') {
                return false;
            }
            return true;
        }
        return false;
    }

    /**
     * Remove IP from hosts.allow and user file.
     *
     * @param string $ip user declared IP
     *
     * @return none
     */
    public function removeIp($ip)
    {
        $path = $this->userPath;

        if (is_file($this->userPath)) {
            $hosts = file_get_contents($this->userPath);
        }

        $h = explode("\n", $hosts);

        $h = array_filter(
            $h, function ($e) use ($ip) {
                return (!strpos($e, $ip));
            }
        );

        $user_allow = implode("\n", $h);

        $this->cpanel->uapi(
            'NemjWhitelist', 'write_hosts',
            array(
                'path' => $path,
                'hosts' => $user_allow
            )
        );

        $this->writeHosts($ip, true);
    }

    /**
     * write to hosts.allow file.
     *
     * This method requires escalation of privileges which is done through api call
     *
     * @param string $ip user declared IP
     * @param bool $flag set flag if IP exists
      */
    protected function writeHosts($ip, $flag = false)
    {
        $admin = '';
        $path = self::HOSTS_PATH;

        if (file_exists($path)) {
            $all = file_get_contents($path);
            $e = explode("\n", $all);
            $len = $index = count($e);
            $f = $ff = false;

            foreach ($e as $k=>$v) {
                if (strpos($v, '# User Allows') !== false) {
                    $index = $k;
                } 
                if ($k > $index) {
                    if ($flag === true && $f === false) {
                        if(strpos($v, $ip) !== false) {
                            unset($e[$k]);
                            $f = true;
                        }
                    }
                }
                if (strpos($v, 'sshd : all : deny') !== false) {
                    unset($e[$k]);
                }
            }
        }
        
        $hosts_allow = "# This file is autogenerated by Cpanel Plugin\n";
        $hosts_allow .= "# Any changes will be overwritten\n\n";
        $hosts_allow .= "# Admin Allows\n";

        for ($i = 0; $i < $index; $i++) {
            if ($e[$i] !== '' && strpos($e[$i], '#') === false) {
                $hosts_allow .= $e[$i] . "\n";
            }
        }

        $hosts_allow .= "# for Admin insert above this line\n";
        $hosts_allow .= "# User Allows\n";

        for ($i = $index; $i < $len - 1; $i++) {
            if ($e[$i] !== '' && strpos($e[$i], '#') === false) {
                $hosts_allow .= $e[$i] . "\n";
            }
        }

        if ($flag === false) {
            $hosts_allow .= "sshd : $ip : allow\n";
        }

        $hosts_allow .= "sshd : all : deny\n";

        

        $this->cpanel->uapi(
            'NemjWhitelist', 'write_hosts',
            array(
                'path' => $path,
                'hosts' => $hosts_allow
            )
        );

    }

    /**
     * Error Messages
     *
     * @param int $num error number
     *
     * @return string error
      */
    protected function error($num) 
    {
        switch($error) {
            case 1: echo "<h4 style='color:red'>IP is Duplicate.</h4>";
                break;
            case 2: echo "<h4 style='color:red'>IP entered is invalid.</h4>" 
                break;
            default: echo "<h4 style='color:red'>Unspecified Error.</h4>" 
                break;

        }
    }

}

