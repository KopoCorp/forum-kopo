#!/bin/bash
# Remplit la base avec des données de démonstration
# Usage: bash populate_data.sh [DB_NAME]

set -e
DB_NAME=${1:-forum}

echo "Insertion des données de démonstration dans $DB_NAME..."

sudo -u postgres psql -d "$DB_NAME" -f populate_demo_data.sql

echo "Données insérées."
