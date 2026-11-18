#!/bin/sh
# Démarrage de la démonstration : base SQLite neuve avec le menu par défaut, puis Apache.
set -e
cd /var/www/html

# Adresse publique fournie par Render : l'application refuse les autres en-têtes Host.
if [ -z "${APP_FULL_BASE_URL:-}" ] && [ -n "${RENDER_EXTERNAL_URL:-}" ]; then
    export APP_FULL_BASE_URL="$RENDER_EXTERNAL_URL"
fi

# Clé de sécurité : fournie par l'hébergeur, sinon générée à chaque démarrage.
if [ -z "${SECURITY_SALT:-}" ]; then
    SECURITY_SALT="$(php -r 'echo bin2hex(random_bytes(32));')"
    export SECURITY_SALT
fi

if [ ! -f tmp/demo.sqlite ]; then
    bin/cake migrations migrate --no-lock --quiet
    bin/cake seeds run MenusSeed --quiet
fi

# Les commandes précédentes tournent en root : Apache doit pouvoir écrire la base et le cache.
chown -R www-data:www-data tmp logs

exec apache2-foreground
