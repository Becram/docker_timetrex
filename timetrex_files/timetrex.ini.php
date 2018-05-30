;<?php die('Unauthorized Access...'); //SECURITY MECHANISM, DO NOT REMOVE//?>
;
;
; TimeTrex Configuration File
; *Linux* Example
;
;

;
; System paths. NO TRAILING SLASHES!
;
[path]
;URL to TimeTrex web root directory. ie: http://your.domain.com/<*BASE_URL*>
;DO NOT INCLUDE http://your.domain.com, just the directory AFTER your domain
;base_url = "/timetrex/interface/"
base_url = "/interface/"

;
;Log directory  -- NOTICE: For security reasons, this should always be outside the web server document root.
;
log = "/var/log/timetrex"

;
;Misc storage, for attachments/images -- NOTICE: For security reasons, this should always be outside the web server document root.
;
storage = "/var/timetrex/storage"

;
;Full path and name to the PHP CLI Binary
;
php_cli = "/usr/bin/php"



;
; Database connection settings. These can be set from the installer.
;
[database]
type = postgres8
;type = mysqli

host = db
database_name = timetrex
user = timetrex
password = "013Nep@!2018"


;
; Email delivery settings.
;
[mail]
;Least setup, deliver email through TimeTrex's email relay via SOAP (HTTP port 80/443)
delivery_method = soap

;Deliver email through remote SMTP server with the following settings.
;delivery_method = smtp
;smtp_host=smtp.gmail.com
;smtp_port=587
;smtp_username=timetrex@gmail.com
;smtp_password=testpass123

;The domain that emails will be sent from, do not include the "@" or anything before it.
; *ONLY* specify this if "delivery_method" above is "smtp"
;email_domain = mydomain.com

;The local part of the email address that emails will be sent from, do not include the "@" or anything after it.
; *ONLY* specify this if "delivery_method" above is "smtp"
;email_local_part = DoNotReply


;
; Cache settings
;
[cache]
enable = TRUE
;NOTICE: For security reasons, this must be outside the web server document root.
dir = "/tmp/timetrex"



[debug]
;Set to false if you're debugging
production = TRUE

enable = FALSE
enable_display = FALSE
buffer_output = TRUE
enable_log = FALSE
verbosity = 10



[other]
uuid_seed = fabdbdea9638
default_interface = html5
; Force all clients to use SSL.
force_ssl = FALSE
installer_enabled = TRUE
primary_company_id = 2

;Specify the URL hostname to be used to access TimeTrex. The BASE_URL specified above will be appended on to this automatically.
; This should be a fully qualified domain name only, do not include http:// or any trailing directories.
hostname = localhost

;ONLY when using a fully qualified hostname specified above, enable CSRF validation for increased security.
;enable_csrf_validation = TRUE

; System Administrators Email address to send critical errors to if necessary. Set to FALSE to disable completely.
system_admin_email = admins@olenepal.org

;WARNING: DO NOT CHANGE THIS AFTER YOU HAVE INSTALLED TIMETREX.
;If you do it will cause all your passwords to become invalid,
;and you may lose access to some encrypted data.
salt = d7a0c4036dd26eca6904e23912eec36d
#salt = bb97e41bb7e462cb2f7028808d475b0e
