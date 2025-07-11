# Base de données Kopo Forum

Ce dépôt contient le fichier `forum.sql` décrivant la structure de la base de données du forum/blog Kopo.

Cette base utilise **PostgreSQL**. Les instructions ci-dessous expliquent comment l'initialiser dans un conteneur LXC géré par **Proxmox**.

## Pré-requis

- Un conteneur (LXC) Debian/Ubuntu provisionné sur Proxmox.
- Les droits root dans le conteneur pour installer et configurer PostgreSQL.
- Le fichier `forum.sql` présent dans ce dépôt.

## Installation pas à pas

1. Installez PostgreSQL :
   ```bash
   apt update && apt install -y postgresql
   ```
2. Démarrez le service PostgreSQL (si ce n'est pas déjà le cas) :
   ```bash
   pg_ctlcluster 16 main start
   ```
   Ou utilisez `systemctl start postgresql` selon la version.
3. Créez une base de données nommée `forum` :
   ```bash
   sudo -u postgres createdb forum
   ```
4. Copiez le fichier `forum.sql` dans le conteneur (via `scp` ou `pct push`).
5. Importez le schéma dans la base :
   ```bash
   sudo -u postgres psql -d forum -f forum.sql
   ```
6. Vérifiez la présence des tables :
   ```bash
   sudo -u postgres psql -d forum -c '\dt'
   ```

## Script d'initialisation

Pour automatiser les étapes 4 à 6, un script `setup_db.sh` est fourni. Exécutez-le depuis le répertoire du dépôt :
```bash
bash setup_db.sh
```
Le script crée la base (si nécessaire) et importe `forum.sql`. Il crée aussi un
utilisateur `kopo_user` avec le mot de passe `kopo_pass` (haché via SCRAM). Ce
compte reçoit tous les droits sur la base et sur l'ensemble des tables et
séquences du schéma `public` afin qu'il puisse manipuler les données existantes
et futures.
Vous pouvez définir d'autres identifiants via les variables `DB_USER` et
`DB_PASS` :

```bash
DB_USER=monuser DB_PASS=monpass bash setup_db.sh
```
Lorsqu'une base du même nom existe déjà, le script demande confirmation avant
de la supprimer puis de la recréer.
Par défaut, le schéma crée un compte `admin` avec le mot de passe `admin`
(haché en bcrypt) possédant le rôle `moderator`.
Les rôles disponibles sont désormais simplement `user` et `moderator`.


## Activer l'accès réseau

Si la base doit être accessible depuis d'autres conteneurs, utilisez le script
`enable_remote_access.sh`. Il configure `postgresql.conf` et `pg_hba.conf` pour
autoriser les connexions distantes en activant l'authentification
`scram-sha-256`, puis redémarre le service.

Exemple d'usage pour autoriser tout le réseau :

```bash
sudo bash enable_remote_access.sh
```

Vous pouvez aussi spécifier un sous-réseau autorisé :

```bash
sudo bash enable_remote_access.sh 192.168.1.0/24
```

Le script force l'option `password_encryption` à `scram-sha-256` et ajoute une
ligne correspondante dans `pg_hba.conf`.


## Activer l'accès réseau

Si la base doit être accessible depuis d'autres conteneurs, utilisez le script
`enable_remote_access.sh`. Il configure `postgresql.conf` et `pg_hba.conf` pour
autoriser les connexions distantes puis redémarre le service.

Exemple d'usage pour autoriser tout le réseau :

```bash
sudo bash enable_remote_access.sh
```

Vous pouvez aussi spécifier un sous-réseau autorisé :

```bash
sudo bash enable_remote_access.sh 192.168.1.0/24
```


## Notes

- Adaptez les noms de base ou d'utilisateur selon vos besoins en modifiant le script.

