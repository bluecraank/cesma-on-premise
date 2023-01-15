<p align="center">
   <img height="210" src="https://github.com/bluecraank/cesma/blob/5d5aacbf36bd874a29d4ed4c0c93fa6308c93fd4/public/img/logo.png">
</p>

# Central Switch Management [cesma]

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
- At least Firmwareversion 16.10 for ArubaOS, 10.11 for ArubaCX
```php8.1 php8.1-gmp php8.1-yaml php8.1-curl php8.1-mysqli php8.1-ldap php8.1-bcmath php8.1-mbstring php8.1-dom```

```MariaDB / MySQL Server```

## Credentials
- Initial Admin
   - User: admin@admin.com
   - Password: password

## Features
- Execute Commands on selected switches, locations or every switch 
- Manage VLANs
- Manage Uplinks
- Backup running-config
- Restore Backup (ArubaOS tested)
- See MAC to Port data (with baramundi full support which device is on which port)
- See Client status (pinging)
- See Trunks
- Manage Locations and buildings
- Get VLANs from switch
- Get Trunks from switch
- Upload Pubkeys for SSH
- Show Live Data from 
- Update untagged VLANs
- Sync VLANs (Create and Sync Name)
- Wildcards in execution commands
- Execute commands
- Logging changes, executions and more
- Set connection settings for API and SSH
- Build on PHP Version 8.1
- High Performance
