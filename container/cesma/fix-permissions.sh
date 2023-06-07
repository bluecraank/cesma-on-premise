#! /bin/sh

setfacl -Rdm u:www-data:rwx,g:www-data:rwx ./storage ./bootstrap/cache
setfacl -Rm u:www-data:rwX,g:www-data:rwX ./storage ./bootstrap/cache
