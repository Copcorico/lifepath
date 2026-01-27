# Guide Rapide - Configuration du Webhook GitHub

## 🚀 Configuration en 5 Minutes

### 1️⃣ Générer un Token Secret

```bash
openssl rand -hex 32
```
**Copiez ce token** - vous en aurez besoin pour GitHub et le serveur.

---

### 2️⃣ Configurer GitHub

1. Allez sur : https://github.com/Copcorico/lebonplan/settings/hooks
2. Cliquez sur **"Add webhook"**
3. Remplissez :
   - **Payload URL** : `http://votre-serveur.com/webhook.php`
   - **Content type** : `application/json`
   - **Secret** : Collez le token de l'étape 1
   - **Which events** : Sélectionnez "Just the push event"
4. Cliquez sur **"Add webhook"**

---

### 3️⃣ Configurer le Serveur

#### Option A : Variable d'Environnement (Recommandé)

**Pour Apache** - Ajoutez dans votre VirtualHost ou `/etc/apache2/envvars` :
```apache
SetEnv WEBHOOK_SECRET "votre-token-ici"
```
Puis redémarrez : `sudo systemctl restart apache2`

**Pour Nginx** - Ajoutez dans votre bloc server :
```nginx
fastcgi_param WEBHOOK_SECRET "votre-token-ici";
```
Puis redémarrez : `sudo systemctl restart nginx`

#### Option B : Modifier webhook.php

Éditez `webhook.php` ligne 8 :
```php
define('SECRET_TOKEN', 'votre-token-ici');
```

---

### 4️⃣ Configurer les Permissions

```bash
# Remplacez /var/www/lebonplan par le chemin de votre projet
REPO_PATH="/var/www/lebonplan"
WEB_USER="www-data"  # ou "apache" ou "nginx" selon votre serveur

# Donner les permissions au serveur web
sudo chown -R $WEB_USER:$WEB_USER $REPO_PATH
sudo chmod -R 755 $REPO_PATH

# Configurer Git pour l'utilisateur web
sudo -u $WEB_USER git config --global user.email "deploy@example.com"
sudo -u $WEB_USER git config --global user.name "Auto Deploy"

# Créer les fichiers de log
cd $REPO_PATH
touch webhook.log deployment.log
chmod 666 webhook.log deployment.log
```

---

### 5️⃣ Tester

#### Test Manuel
```bash
cd /var/www/lebonplan
./deploy.sh
```

#### Test avec GitHub
1. Faites un commit et push sur GitHub
2. Vérifiez les logs :
```bash
tail -f /var/www/lebonplan/webhook.log
```
3. Vérifiez sur GitHub : Settings > Webhooks > Cliquez sur votre webhook > "Recent Deliveries"

---

## ✅ C'est Fait !

Maintenant, chaque fois que vous faites un `git push`, votre serveur se mettra automatiquement à jour !

---

## 🔧 Dépannage Rapide

**Erreur 403 - Forbidden**
→ Le token secret ne correspond pas. Vérifiez qu'il est identique sur GitHub et le serveur.

**Erreur 500 - Git failed**
→ Problème de permissions. Exécutez les commandes de l'étape 4.

**Rien ne se passe**
→ Vérifiez que webhook.php est accessible : `http://votre-serveur.com/webhook.php`

**Voir les logs détaillés**
```bash
tail -100 webhook.log
```

---

## 📝 Pour Plus d'Informations

Consultez le fichier `README.md` pour la documentation complète.
