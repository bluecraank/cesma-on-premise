<p align="center">
   <img height="210" src="https://github.com/bluecraank/cesma/blob/5d5aacbf36bd874a29d4ed4c0c93fa6308c93fd4/public/img/logo.png">
</p>

# Central Switch Management [cesma] powered by Laravel

## Working with
- HP / ArubaOS like Aruba 2930F
- HP / ArubaCX like Aruba 6100

```
"HP ProVision OS"
ArubaOS-Switch - Run by ProVision based switches (i.e. 2500, 2900, 3800, 5400, HP 54Z**).
ArubaOS-CX - Run by Aruba's next generation switches (6100, 6200, 6300, 6400, 8200, etc).
```

## Requirements
- Enabled API on Aruba Switch with Password authentication
- At least Firmware 16.10 for ArubaOS, 10.11 for ArubaCX

### PHP Modules

```php8.1 php8.1-gmp php8.1-yaml php8.1-curl php8.1-mysqli php8.1-ldap php8.1-bcmath php8.1-mbstring php8.1-dom```

### FPING to ping Devices

```sudo apt install fping```

### Database

```MariaDB / MySQL Server```


### SNMP
Make sure, your cesma server can reach your routers with snmp / ping in order to get full client data
Set your routers in file .env

## Add device into cesma
Make sure to set session timeout to one minute
Make sure to enable rest-interface on switch
newest firmware is required

## Info about uplinks
- You should set your uplinks to get high accurancy of client discovery
- Else you see clients on trunks or uplinks port, which is not correct!
- Nightly database clean up will delete wrong mac addresses and clients which were discovered on uplink ports

## Credentials
- You need to setup ldap authentication in order to log in.
- Enable LDAP_LOGGING in your .env too debug your ldap connection
- After login, set your self with Artisan command "php artisan user:role YOUR_GUID admin" to admin
- You see youre YOUR_GUID with "php artisan user:show"

## Logging
- In storage/logs/laravel.log you should see everything needed.

## Features
- Execute Commands on selected switches, locations or every switch 
- Manage VLANs
- Manage Uplinks
- Backup running-config
- Restore Backup (ArubaOS tested)
- See Device location (Which device is on which port etc.)
- See Client online / offline status
- See Trunks
- See Port statistics (tx,rx)
- Manage Locations and buildings
- Get VLANs from switch
- Get Trunks from switch
- Upload Pubkeys for SSH
- Set Untagged VLAN on Port
- Set Tagged VLAN on Port
- Set client categorys and give them an icon
- Sync VLANs (Create and Sync Name)
- Logging
- API / SSH Connection settings
- Build on PHP Version 8.1 with Laravel
- High Performance
