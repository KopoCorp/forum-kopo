#!/bin/bash
# Initialise la base de données du forum dans un conteneur Proxmox

set -e

DB_NAME=${1:-forum}
SQL_FILE=${2:-forum.sql}

# Crée la base si elle n'existe pas
echo "Creation de la base $DB_NAME (si besoin)..."
sudo -u postgres psql -tc "SELECT 1 FROM pg_database WHERE datname='$DB_NAME'" | grep -q 1 || sudo -u postgres createdb "$DB_NAME"

# Importe le schéma
sudo -u postgres psql -d "$DB_NAME" -f "$SQL_FILE"

echo "Base $DB_NAME initialisée." 

