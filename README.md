# CESMA

A self-hosted web tool to manage the switches of a network: VLANs, ports, backups, clients and topology for HP/Aruba and Dell EMC switches. Users log in with their Active Directory account (LDAP or SSO).

## Features

- **VLANs:** create, edit and delete VLANs per site, sync them to switches (with a test mode)
- **Ports:** set port descriptions, untagged and tagged VLANs; notifications on link and speed changes
- **Backups:** daily configuration backups, download per backup, weekly status mail
- **Clients:** find a client by hostname, IP, MAC, VLAN, switch or port (IP → MAC → port)
- **Topology:** map of the switch connections (LLDP), uplink detection with suggestions
- **SSH keys:** push SSH public keys to switches
- **MAC types:** assign types and icons to MAC prefixes (e.g. printers, phones)
- **Sites:** several sites with buildings and rooms, users get access per site
- **Audit log:** who changed what (old and new values), logins, failures, devices going offline

## Supported switches

| Type | Examples | Data from | Write (VLANs, ports) | Backups |
|---|---|---|---|---|
| `aruba-os` | HP Aruba OS, ProCurve, e.g. J9851A, 2930F | SNMP and REST API | yes | yes |
| `aruba-cx` | HP Aruba OS-CX, e.g. JL675A–JL679A | REST API (uptime via SNMP) | yes | yes |
| `dell-emc` | Dell EMC OS10 Enterprise | SNMP | no (read-only) | no |
| `dell-emc-powerswitch` | Dell EMC PowerSwitch | SNMP | no (read-only) | no |

SNMP is queried with version 2c and the community `public`. The REST API is accessed with the user from `API_USERNAME` and the password stored for each device; TLS certificates of the switches are not verified.

## Risky features

These features change the configuration of many switches at once. Use the test mode of the VLAN sync first.

- **Tag VLANs to uplinks:** a mistake can cut off switches from the network.
- **Sync VLANs to all devices:** creates, renames or deletes VLANs on every selected switch.
- **Push SSH public keys:** replaces the authorized keys on the switch. At least 2 keys are required, so you do not lock yourself out with a single wrong key.

## Requirements

- Linux server with systemd
- PHP 8.1–8.3 with the extensions `snmp`, `ldap`, `pdo_mysql`, `mbstring`, `xml`, `curl`
- Composer
- Node.js and npm (to build the frontend)
- MySQL or MariaDB
- A web server (nginx or Apache) with the document root on `public/`
- Command line tools used by the background jobs: `ip` (iproute2, ARP scan), `host` (DNS lookup of clients), `nmap` (optional, client scan)

## Installation

```bash
cd /var/www
git clone <repository-url> cesma
cd cesma

composer install --no-dev --optimize-autoloader
npm ci && npm run build

cp .env.example .env
php artisan key:generate
# Edit .env, see "Configuration"

php artisan migrate
chown -R www-data:www-data storage bootstrap/cache
```

Create the first site, then log in:

```bash
php artisan site:create "Headquarters"
```

Users log in with their **e-mail address** (LDAP attribute `mail`) or their **Windows username** (`sAMAccountName`) and their directory password. The **first user who logs in becomes super administrator**. Every further user starts as read-only user, a super administrator assigns roles and sites in *Global settings → Users*.

### Background services

Two services are required, both expect the installation in `/var/www/cesma`:

```bash
sudo cp cesma-worker.service cesma-refresh.service /etc/systemd/system/
sudo systemctl daemon-reload
sudo systemctl enable --now cesma-worker.service cesma-refresh.service
```

| Service | Task |
|---|---|
| `cesma-worker` | Runs the scheduled jobs (`php artisan schedule:work`), see below |
| `cesma-refresh` | Refreshes all switches continuously (SNMP, every 5 minutes also the API) |

## Configuration

All settings are made in `.env`. After changes in production run `php artisan config:cache`.

### Application

```ini
APP_NAME=CESMA
APP_ENV=production
APP_DEBUG=false
APP_URL=https://cesma.domain.local
# Language of the interface: en or de
APP_LOCALE=en

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cesma
DB_USERNAME=cesma
DB_PASSWORD=
```

### LDAP (required)

```ini
LDAP_LOGGING=false
LDAP_CONNECTION=default
LDAP_HOST=dc1.domain.local
LDAP_USERNAME="CN=readonly,CN=Users,DC=domain,DC=local"
LDAP_PASSWORD=
LDAP_PORT=636
LDAP_BASE_DN="DC=domain,DC=local"
LDAP_TIMEOUT=15
LDAP_SSL=true
LDAP_TLS=false

# Optional: only members of this group (also nested) can log in.
# Without it every user of the directory can log in.
LDAP_ADMIN_GROUP="CN=CESMA_USERS,OU=Groups,DC=domain,DC=local"
```

Test the connection with `php artisan ldap:test`. If `LDAP_ADMIN_GROUP` is set but the group can not be found, nobody can log in; the reason is written to the log file.

### SSO (optional)

With SSO a reverse proxy (e.g. Authentik) authenticates the user and passes the username in an HTTP header.

```ini
SSO_ENABLED=true
# Header with the username as PHP server key (HTTP_ + header name in upper case)
SSO_HTTP_HEADER_USER_KEY=HTTP_X_AUTHENTIK_USERNAME
# Accept usernames without a matching domain
SSO_BYPASS_DOMAIN_VERIFICATION=true
```

> **Security:** with SSO enabled, CESMA trusts this header. CESMA must only be reachable through the proxy, and the proxy must always set (overwrite) the header. Otherwise anyone can log in as any user by sending the header.

### Switch access

```ini
# User for the REST API of the switches (password is set per device)
API_USERNAME=admin
# true = https, false = http
API_HTTPS=true

# SSH login for the public key upload (Aruba OS)
SSH_USERNAME=admin
# true  = log in with the private key of CESMA
# false = log in with the device password
SSH_PRIVATEKEY=true
```

The private key is imported in *Global settings → SSH Publickeys* (link "add a private key for CESMA") and stored encrypted.

### Backup mail

Sent every Sunday at 22:00 with the backup status of every switch of the week.

```ini
BACKUP_MAIL_ADDRESS=backup@domain.local

MAIL_MAILER=smtp
MAIL_HOST=mail.domain.local
MAIL_PORT=587
MAIL_USERNAME=mailer@domain.local
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="mailer@domain.local"
MAIL_FROM_NAME="CESMA"
```

### Logging

```ini
LOG_CHANNEL=stack
# info for production, debug also logs expected failures (e.g. hosts without SNMP)
LOG_LEVEL=info
# Days the daily log files in storage/logs are kept
LOG_DAILY_DAYS=30
```

CESMA writes two logs:

- **Audit log** in the database, shown in *Logs*: user actions, logins, denied access, devices going offline, failed backups. Entries older than 8 weeks are deleted. Users see the entries of their current site, administrators also entries without site (e.g. logins).
- **Log files** in `storage/logs/laravel-YYYY-MM-DD.log`: all audit entries plus technical errors (API, SSH, SNMP).

## Roles

| Role | Value | Permissions |
|---|---|---|
| Read-only user | 0 | View everything of the assigned sites |
| Administrator | 1 | Additionally change devices, VLANs, ports and sites, sync VLANs, create backups, push SSH keys |
| Super administrator | 2 | Additionally manage users and roles, access to all sites |

Roles can also be set on the command line:

```bash
php artisan user:show                 # list users with their guid
php artisan user:role <guid> <0|1|2>
```

## Scheduled jobs

Run by `cesma-worker`:

| Job | Interval | Task |
|---|---|---|
| `devices:arp-scan` | 5 minutes | Find new switches in the ARP table of the server (see below) |
| `clients:nmap-scan` | 5 minutes | Ping the IP ranges of VLANs with scanning enabled, so clients appear in the ARP tables |
| `clients:query-providers` | every minute | Read IP ↔ MAC from the SNMP gateways (*Global settings → SNMP*) |
| `clients:update` | 5 minutes | Assign clients to switch ports |
| `clients:dns-lookup` | 5 minutes | Resolve the hostnames of clients |
| `clients:resolve-mac-vendors` | daily 05:00 | Look up the vendors of MAC prefixes (api.macvendors.com) |
| `device:resolve-topology` | hourly | Read LLDP neighbours, build the topology |
| `device:backup-all` | daily 10:30 | Back up all switches that support backups |
| `database:cleanup` | daily 04:00 | Delete old data: backups after 4 weeks, port statistics after 2 weeks, audit log and clients after 8 weeks, notifications after 6 weeks |
| `backup:mail` | Sunday 22:00 | Send the weekly backup mail |

**ARP scan:** switches found in the ARP table of the server (SNMP answers as switch, not known yet) are added automatically to the first room of the first building of the first site, with the password `public`. Set the correct room and password for these devices; they are listed in the audit log.

All commands can also be run by hand, e.g. `php artisan device:backup-all`. `php artisan list` shows all commands.

## Update

```bash
cd /var/www/cesma
git pull
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate
php artisan config:cache
sudo systemctl restart cesma-worker cesma-refresh
```

## Project status

CESMA is maintained occasionally in spare time. Issues and pull requests are welcome.
