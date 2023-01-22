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
```php8.1 php8.1-gmp php8.1-yaml php8.1-curl php8.1-mysqli php8.1-ldap php8.1-bcmath php8.1-mbstring php8.1-dom```

```MariaDB / MySQL Server```

## Credentials
- You need to setup ldap authentication in order to log in.
- Enable LDAP_LOGGING in your .env too debug your ldap connection
- After login, set your self with Artisan command "php artisan user:role <guid> <role>" to admin
- You see youre <guid> with "php artisan user:show"

## Features
- Execute Commands on selected switches, locations or every switch 
- Manage VLANs
- Manage Uplinks
- Backup running-config
- Restore Backup (ArubaOS tested)
- See Device location (Which device is on which port etc.)
- See Client online / offline status
- See Trunks
- See Port statistics
- Manage Locations and buildings
- Get VLANs from switch
- Get Trunks from switch
- Upload Pubkeys for SSH
- Set Untagged VLAN on Port
- Set Tagged VLAN on Port
- Sync VLANs (Create and Sync Name)
- Logging
- API / SSH Connection settings
- Build on PHP Version 8.1 with Laravel
- High Performance
