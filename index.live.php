<?php
/**
 * This script creates the ability to block IP's in Cpanel.
 *
 *  @category Whitelist
 *  @package  Nemj
 *  @author   David Ford <djfordz@gmail.com>
 *  @license  Copyright http://dfordz.com
 *  @link     http://dfordz.com/cpanel-whitelist.html
  */

require_once '/usr/local/cpanel/php/cpanel.php';
require_once 'Whitelist.php';
require_once 'whitelist.css';

$cpanel = new CPANEL();

$whitelist = new Nemj_Whitelist($cpanel);

print $cpanel->header("SSH Whitelist", "nemj-wl");

if (isset($_POST['update'])) {
    if (array_key_exists('nemj-label', $_POST) && array_key_exists('nemj-ip', $_POST)) {
        if (urlencode($_POST['nemj-label']) !== '' && urlencode($_POST['nemj-ip']) !== '') {
            $whitelist->addIp(urlencode(strip_tags(trim($_POST['nemj-label']))), urlencode(strip_tags(trim($_POST['nemj-ip']))));
        }
    }
}
if (!isset($_POST['update'])) {
    if (array_key_exists('ip', $_POST)) {
        if ($_POST['ip'] !== '') {
            $whitelist->removeIp(urlencode(strip_tags(trim($_POST['ip']))));
        }
    }
}
?>

<div id="desc">
    <p>Add IP's to hosts.allow for ssh access.</p>
</div>
<div id="nemj-wrapper">
    <div id="nemj-list">
        <h4>Label</h4><h4>IP</h4>
        <ul id="list">
            <?php $ips = $whitelist->getIps(); ?>
                <?php if (isset($ips)) { ?>
                    <?php foreach ($ips as $i) { ?>
                        <?php foreach ($i as $label => $ip) { ?>
                            <li>
                                <form method="post">
                                    <input type="text" id="label" class="input" value="<?php echo $label; ?>" readonly />
                                    <input class="input" type="text" value="<?php  echo $ip; ?>" id="ip" name="ip" readonly />
                                    <input class="button remove" id="remove" type="submit" value="Remove" />
                                </form>
                            </li>
                        <?php } ?>
                    <?php } ?>
                <?php } ?>
        </ul>
    </div>
    <form id="nemj-whitelist" method="post">
        <div id="ip-wrapper">
            <label for="nemj-label">
                <span>Label</span>
            </label>
            <input type="text" id="nemj-label" class="input" name="nemj-label" />
            <label for="nemj-ip">
                <span>IP</span>
            </label>
            <input type="text" id="nemj-ip" class="input" name="nemj-ip" />
            <input type="button" value="Cancel" id="cancel" class="button cancel" name="cancel" />
            <input type="submit" value="Update" id="update" class="button update" name="update" />
        </div>
    </form>
</div>
<script>
document.getElementById('cancel').addEventListener('click', function(event) {
    event.preventDefault();
    document.getElementById('nemj-label').value = '';
    document.getElementById('nemj-ip').value = '';
});
</script>


<?php
print $cpanel->footer();
$cpanel->end();
?>
