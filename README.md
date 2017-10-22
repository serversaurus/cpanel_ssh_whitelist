## Simple cpanel plugin to allow cpanel users to whitelist IP's in /etc/hosts.allow file.

to install:

*note: must have root privileges to install cpanel plugins*


copy `nemj_whitelist.tar.gz` to cpanel server.

```
wget https://github.com/djfordz/cpanel_ssh_whitelist/archive/ssh_whitelist-1.1.tar.gz 
tar -xvf nemj_whitelist-1.0.tar.gz && cd cpanel_ssh_whitelist-ssh_whitelist-1.1/ 
chmod +x install.sh
./install.sh
```

To uninstall:

use the uninstall script. 

```
chmod +x uninstall
./uninstall
```

To add admin IP's or IP's that you do not want user to see or alter. add to `/etc/hosts.allow` where it is commented to add admin IP's

Submit issues for bugs.

Future features:
add support for subnets
