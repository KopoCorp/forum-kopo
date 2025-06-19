# Base de données Kopo Forum

Ce dépôt contient le fichier `forum.sql` décrivant la structure de la base de données du forum/blog Kopo.

Cette base utilise **PostgreSQL**. Les instructions ci-dessous expliquent comment l'initialiser dans un conteneur LXC géré par **Proxmox**.

## Pré-requis

- Un conteneur (LXC) Debian/Ubuntu provisionné sur Proxmox.
- Les droits root dans le conteneur pour installer et configurer PostgreSQL.
- Le fichier `forum.sql` présent dans ce dépôt.

## Installation pas à pas

1. Connectez-vous au conteneur :
   ```bash
   pct exec <ID_CONTENEUR> -- bash
   ```
2. Installez PostgreSQL :
   ```bash
   apt update && apt install -y postgresql
   ```
3. Démarrez le service PostgreSQL (si ce n'est pas déjà le cas) :
   ```bash
   pg_ctlcluster 16 main start
   ```
   Ou utilisez `systemctl start postgresql` selon la version.
4. Créez une base de données nommée `forum` :
   ```bash
   sudo -u postgres createdb forum
   ```
5. Copiez le fichier `forum.sql` dans le conteneur (via `scp` ou `pct push`).
6. Importez le schéma dans la base :
   ```bash
   sudo -u postgres psql -d forum -f forum.sql
   ```
7. Vérifiez la présence des tables :
   ```bash
   sudo -u postgres psql -d forum -c '\dt'
   ```

## Script d'initialisation

Pour automatiser les étapes 4 à 6, un script `setup_db.sh` est fourni. Exécutez-le depuis le répertoire du dépôt :
```bash
bash setup_db.sh
```
Il crée la base (si nécessaire) et importe `forum.sql`.

## Notes

- Adaptez les noms de base ou d'utilisateur selon vos besoins en modifiant le script.
- Pensez à configurer l'accès réseau de PostgreSQL si l'application se trouve hors du conteneur.

