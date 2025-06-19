#!/bin/bash
# Configure PostgreSQL to accept remote connections
# Usage: sudo bash enable_remote_access.sh [SUBNET]
# SUBNET defaults to 0.0.0.0/0 (toutes adresses)
set -e

PG_VERSION=$(ls /etc/postgresql | head -n1)
CONF_DIR="/etc/postgresql/$PG_VERSION/main"
POSTGRESQL_CONF="$CONF_DIR/postgresql.conf"
PG_HBA="$CONF_DIR/pg_hba.conf"

SUBNET=${1:-0.0.0.0/0}

# Active l'ecoute sur toutes les interfaces
sudo sed -i "s/^#*\s*listen_addresses\s*=.*$/listen_addresses = '*'/'" "$POSTGRESQL_CONF"

# Ajoute la ligne d'autorisation si absente
if ! grep -q "^host\s\+all\s\+all\s\+$SUBNET" "$PG_HBA"; then
    echo "host    all             all             $SUBNET            md5" | sudo tee -a "$PG_HBA"
fi

# Redemarre PostgreSQL
sudo systemctl restart postgresql

echo "Acces distant active pour $SUBNET"

