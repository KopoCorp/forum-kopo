#!/bin/bash
# Initialise la base de données du forum dans un conteneur Proxmox

set -e

# Default credentials can be overridden with the DB_USER and DB_PASS env vars
DB_USER=${DB_USER:-kopo_user}
DB_PASS=${DB_PASS:-kopo_pass}

DB_NAME=${1:-forum}
SQL_FILE=${2:-forum.sql}

# Crée la base ou propose de l¹écraser si elle existe
echo "Creation de la base $DB_NAME (si besoin)..."
if sudo -u postgres psql -tc "SELECT 1 FROM pg_database WHERE datname='$DB_NAME'" | grep -q 1; then
    read -r -p "La base $DB_NAME existe deja. La remplacer ? [y/N] " ans
    if [[ $ans =~ ^[Yy]$ ]]; then
        sudo -u postgres dropdb "$DB_NAME"
        sudo -u postgres createdb "$DB_NAME"
        echo "Base $DB_NAME recree."
    else
        echo "Utilisation de la base existante $DB_NAME."
    fi
else
    sudo -u postgres createdb "$DB_NAME"
fi

# Importe le schéma
sudo -u postgres psql -d "$DB_NAME" -f "$SQL_FILE"

echo "Base $DB_NAME initialisée."

# Ensure password_encryption is set to scram-sha-256
PG_VERSION=$(find /etc/postgresql -maxdepth 1 -mindepth 1 -type d -printf '%f\n' | head -n1)
CONF_DIR="/etc/postgresql/$PG_VERSION/main"
POSTGRESQL_CONF="$CONF_DIR/postgresql.conf"
sudo sed -i "s/^#*\s*password_encryption\s*=.*$/password_encryption = 'scram-sha-256'/" "$POSTGRESQL_CONF"

# Create user if needed with the provided password
if ! sudo -u postgres psql -tAc "SELECT 1 FROM pg_roles WHERE rolname='$DB_USER'" | grep -q 1; then
    sudo -u postgres psql -c "CREATE ROLE $DB_USER LOGIN PASSWORD '$DB_PASS'"
fi

# Grant privileges on the database
sudo -u postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE $DB_NAME TO $DB_USER"

# Grant access to all tables and sequences in the public schema
sudo -u postgres psql -d "$DB_NAME" -c "GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO $DB_USER"
sudo -u postgres psql -d "$DB_NAME" -c "GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO $DB_USER"

# Ensure future tables/sequences are accessible too
sudo -u postgres psql -d "$DB_NAME" -c "ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT SELECT, INSERT, UPDATE, DELETE ON TABLES TO $DB_USER"
sudo -u postgres psql -d "$DB_NAME" -c "ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT USAGE, SELECT ON SEQUENCES TO $DB_USER"

sudo systemctl restart postgresql

