<p align="center">
   <img height="210" src="https://github.com/bluecraank/cesma/blob/5d5aacbf36bd874a29d4ed4c0c93fa6308c93fd4/public/img/logo.png">
</p>

# Central Switch Management

## Working with
- HP / Aruba Switch with ArubaOS Firmware
- In theory, every arubaos like switch should work (not arubacx!)

```
"HP ProVision OS"
ArubaOS-Switch - Run by ProVision based switches (i.e. 2500, 2900, 3800, 5400, HP 54Z**).
ArubaOS-CX - Run by Aruba's next generation switches (6100, 6200, 6300, 6400, 8200, etc).
```

## Requirements
```php8.1 php8.1-gmp php8.1-yaml php8.1-curl php8.1-mysqli php8.1-ldap php8.1-bcmath php8.1-mbstring php8.1-dom```

```MariaDB / MySQL Server```

## Credentials
- Initial Admin
   - User: admin@admin.com
   - Password: password

## Features
- Execute Commands on selected switches, locations or every switch 
- Manage VLANs
- Manage Trunks
- Manage Locations and buildings
- Get VLANs from switch
- Get Trunks from switch
- Show Live Data from switch
- Login via AD / LDAP
- Wildcards in execution commands
- Simultaneously execute commands
- Logging changes, executions and more
- Set connection settings for API and SSH
- Build on PHP Version 8.1
- High Performance
