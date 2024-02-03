#!/usr/bin/env bash

# Author: Stefan Saam, github@saams.de

#######################################################################
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.

# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.

# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#######################################################################

# expected from calling script
## const_WEB_ROOT_LBB
## USER_WWW_DATA

# settings
USER="lbb"

# Don't start as root
if [[ $EUID -eq 0 ]]; then
    echo "Run the script as a regular user"
    exit 1
fi

# Don't start setup if no graphical system installed
if [ ! -f "/usr/bin/startx" ]; then
	exit "No graphical system detected."
fi

# auto logon user lbb
## enable auto login
sudo raspi-config nonint do_boot_behaviour B4

## edit /etc/lightdm/lightdm.conf to set auto login user
CONFIG_FILE="/etc/lightdm/lightdm.conf"
VAR="autologin-user"
NEW_VALUE="lbb"

sudo sed $CONFIG_FILE -i -e "s/^\(#\|\)${VAR}=.*/autologin-user=${NEW_VALUE}/"

# disable auto mount for user
CONFIG_DIR="/home/$USER/.config/pcmanfm/LXDE-pi"
CONFIG_FILE="${CONFIG_DIR}/pcmanfm.conf"

sudo -u $USER  mkdir -p $CONFIG_DIR
echo """[volume]
mount_on_startup=0
mount_removable=0
autorun=0
""" | sudo -u $USER tee $CONFIG_FILE

# auto start browser
AUTOSTART_USER_DIR="/home/$USER/.config/autostart"

sudo -u $USER mkdir -p $AUTOSTART_USER_DIR
echo """[Desktop Entry]
Type=Application
Name=little-backup-box
Exec="firefox --kiosk http://localhost"
""" | sudo -u $USER tee $AUTOSTART_USER_DIR/little-backup-box.desktop

# install KioskBoard virtual keyboard
rm -R '~/KioskBoard'
git clone https://github.com/outdoorbits/KioskBoard.git
GIT_CLONE=$?
if [ "${GIT_CLONE}" -gt 0 ]; then
	echo "Cloning KioskBoard from github.com failed. Please try again later."
else
	KioskBoardDir="${const_WEB_ROOT_LBB}/KioskBoard"
	mkdir -p "${KioskBoardDir}"
	sudo cp -f ~/KioskBoard/LICENSE ${KioskBoardDir}/
	sudo cp -f ~/KioskBoard/dist/kioskboard-2.3.0.min.css ${KioskBoardDir}/
	sudo cp -f ~/KioskBoard/dist/kioskboard-2.3.0.min.js ${KioskBoardDir}/
	sudo cp -f ~/KioskBoard/dist/kioskboard-keys-* ${KioskBoardDir}/

	# set file permissions in $KioskBoardDir
	sudo chown ${USER_WWW_DATA}:${USER_WWW_DATA} "${KioskBoardDir}" -R
	sudo chmod 777 ${KioskBoardDir}/*
fi
