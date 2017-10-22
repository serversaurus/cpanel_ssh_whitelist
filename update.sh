#!/bin/bash -
#===============================================================================
#
#          FILE: update.sh
#
#         USAGE: ./update.sh
#
#   DESCRIPTION:
#
#       OPTIONS: ---
#  REQUIREMENTS: ---
#          BUGS: ---
#         NOTES: ---
#        AUTHOR: YOUR NAME (),
#  ORGANIZATION:
#       CREATED: 10/22/2017 02:38
#      REVISION:  ---
#===============================================================================

set -o nounset                              # Treat unset variables as an error
set -e # Abort script at first error

cwd=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
install_plugin='/usr/local/cpanel/scripts/install_plugin'
dst='/usr/local/cpanel/base/frontend/paper_lantern/nemj_whitelist'
api='/usr/local/cpanel/Cpanel/API'
adm='/usr/local/cpanel/bin/admin/Nemanja'

if [ $EUID -ne 0 ]; then
    echo 'Script requires root privileges, run it as root or with sudo'
    exit 1
fi

if [ ! -f /usr/local/cpanel/version ]; then
    echo 'cPanel installation not found'
    exit 1
fi

cp -v ${cwd}/index.live.php $dst
cp -v ${cwd}/Whitelist.php $dst
cp -v ${cwd}/whitelist.css $dst
cp -v ${cwd}/Cpanel/API/NemjWhitelist.pm $api
cp -v ${cwd}/bin/admin/Nemanja/Whitelist.conf $adm
cp -v ${cwd}/bin/admin/Nemanja/Whitelist $adm

chmod 700 ${adm}/Whitelist

echo 'Update finished without errors'
