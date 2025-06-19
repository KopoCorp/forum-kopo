#!/bin/bash
# Configure PostgreSQL to accept remote connections using SCRAM authentication
# Usage: sudo bash enable_remote_access.sh [SUBNET]
# SUBNET defaults to 0.0.0.0/0 (toutes adresses)
set -e

PG_VERSION=$(find /etc/postgresql -maxdepth 1 -mindepth 1 -type d -printf '%f\n' | head -n1)
CONF_DIR="/etc/postgresql/$PG_VERSION/main"
POSTGRESQL_CONF="$CONF_DIR/postgresql.conf"
PG_HBA="$CONF_DIR/pg_hba.conf"

SUBNET=${1:-0.0.0.0/0}

# Ensure password_encryption is SCRAM
sudo sed -i "s/^#*\s*password_encryption\s*=.*$/password_encryption = 'scram-sha-256'/" "$POSTGRESQL_CONF"

# Active l'ecoute sur toutes les interfaces
sudo sed -i "s/^#*\s*listen_addresses\s*=.*$/listen_addresses = '*'/'" "$POSTGRESQL_CONF"

# Ajoute la ligne d'autorisation si absente
if ! grep -q "^host\s\+all\s\+all\s\+$SUBNET" "$PG_HBA"; then
    echo "host    all             all             $SUBNET            scram-sha-256" | sudo tee -a "$PG_HBA"
fi

# Redemarre PostgreSQL
sudo systemctl restart postgresql

echo "Acces distant active pour $SUBNET"

