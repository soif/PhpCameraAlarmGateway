# Php Camera Alarm Gateway (WORK IN PROGRESS)
Server Daemon to listen to IP Camera Alarm messages, decode it and send triggers to ZoneMinder, Domoticz, Custom URLs....

This allows to offload the motion detection work to each camera


## Features

- Parse Hivision based (chinese cheap camera) IP Camera messages
- Can trigger :
    - ZoneMinder API
    - ZoneMinder Triggers
    - Domoticz Switches
    - Custom URL
- Run as multi threaded unix daemon

## Installation
#### PEAR modules needed
Under Debian, install by:
```
apt-get install php-pear
pear install System_Daemon
pear install Log
```

#### Set Configuration
Copy *config.default.php* to *config.php*, and set your configuration

#### Setting in each Hivision IP Cameras
In the Device Setting (access via CMS, or via InternetExporer):
- **Alarm** page / Video Motion :
    - set sensivity to 'Highest' (or lower)
    - set the region
    - Check "Enable"
    - Check "Alarm Output"
    - You may Check "Write Log" to verify that event are set as you wish
- **System** page / NetServices / AlarmServer :
    - Check "Enable"
    - Check "Alarm Report"
    - Server Address (the IP/hostname where PCAG is installed)
    - Port (same as set in PCAG config, ie 15000)

#### Daemon & Log file
- Launch Daemon :`./pcag.php`
- Kill Daemon : `killall -9 pcag.php`
- view Logfile: `tail -f /var/log/pcag.php.log`


## Known bugs
 While the daemon is working as expected to serve client requests as a multi threaded server, the parent/child mechanism is launched twice (PEAR system_daemon + php-multithreaded-socket-server), which obvioulsy is NOT clean and safe.

**Anyone mastering the *PCNTL* Php Extension is *ABSOLUTELY WELCOME* to submit a Pull Request to fix this.**


## License

This program is free software. You can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
