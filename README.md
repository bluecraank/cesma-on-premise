# cesma - A switch management tool for aruba os, hp and dell emc

# Features
- Create, edit, delete and sync vlans from and to devices
- Tag/Untag vlans to Ports
- Daily backups
- Port status and speed notifications
- Client lookup (IP to MAC to Port)
- Logging
- Push SSH-Keys to Switches
- Visualize switch topology
- Define Uplinks
- Assign Icons to MACs for better visualization
- LDAP User login

# Compatible switches
- HP Aruba OS (SNMP & API)
- Dell EMC (SNMP in read only)
- HP Switches like J9851A (SNMP & API)

# Risky features
- Tagging Vlans to Uplinks (Should work, but has a risk to kill your infrastructure)
- Sync Vlans across all devices (Works well so far)
- Push SSH-Keys (Has a safety minimum push rate of atleast 2 keys)

# Development & future plans
- This repo is archived, because i dont time to maintain it
- Feel free to fork

# Configuration
## LDAP (required)
```
LDAP_LOGGING=false
LDAP_CONNECTION=default
LDAP_HOST=dc1.domain.local
LDAP_USERNAME="CN=readonly,CN=Users,DC=Domain,DC=local"
LDAP_PASSWORD=
LDAP_PORT=636
LDAP_BASE_DN="dc=Domain,dc=local"
LDAP_TIMEOUT=15
LDAP_SSL=true
LDAP_TLS=false
LDAP_ADMIN_GROUP="CN=GROUP_ALLOWED,OU=Groups,DC=Domain,DC=local"

Test with php artisan ldap:test
```
## SSO
```
SSO_ENABLED=true
SSO_HTTP_HEADER_USER_KEY=HTTP_X_AUTHENTIK_USERNAME
SSO_BYPASS_DOMAIN_VERIFICATION=true
```
## BACKUP MAILS
```
BACKUP_MAIL_ADDRESS=administration-netzwerk@doepke.de

MAIL_MAILER=smtp
MAIL_HOST=mail.domain.local
MAIL_PORT=587
MAIL_USERNAME=mailer@domain.local
MAIL_PASSWORD=
MAIL_ENCRYPTION=STARTTLS
MAIL_FROM_ADDRESS="mailer@domain.local"
MAIL_FROM_NAME="CESMA Weekly Report"
```
## API Authentication
```
API_USERNAME=admin
API_HTTPS=true
```
## SSH (deprecated)
```
SSH_USERNAME=admin
SSH_PRIVATEKEY=true
```

## Workers
### General worker
```
sudo cp ./cesma-worker.service /etc/systemd/system/
sudo systemctl enable cesma-worker.service

Task: Triggers scheduled jobs
```
### Specific switch update worker loop
```
[Unit]
Description=CESMA Device Refresh Loop Service
After=network.target
StartLimitIntervalSec=0

[Service]
Type=simple
Restart=always
RestartSec=1
User=root
WorkingDirectory=/var/www/cesma
ExecStart=/usr/bin/php /var/www/cesma/artisan app:refresh-devices-loop

[Install]
WantedBy=multi-user.target
```
