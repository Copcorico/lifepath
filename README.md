# lebonplan
site projet A2 info

## Déploiement Automatique avec GitHub Webhooks

Ce projet est configuré pour se mettre à jour automatiquement lorsqu'un push est effectué sur GitHub.

### Configuration du Webhook GitHub

1. **Générer un token secret**
   ```bash
   # Générez un token aléatoire sécurisé
   openssl rand -hex 32
   ```

2. **Configurer le webhook sur GitHub**
   - Allez dans les paramètres de votre repository : `Settings` > `Webhooks` > `Add webhook`
   - **Payload URL** : `http://votre-serveur.com/webhook.php`
   - **Content type** : `application/json`
   - **Secret** : Collez le token généré à l'étape 1
   - **Events** : Sélectionnez "Just the push event"
   - **Active** : Cochez la case
   - Cliquez sur `Add webhook`

3. **Configurer le serveur**
   
   a. Définir le token secret sur le serveur :
   ```bash
   # Méthode 1 : Variable d'environnement (recommandé)
   export WEBHOOK_SECRET="votre-token-secret-ici"
   
   # Méthode 2 : Modifier directement dans webhook.php
   # Éditez la ligne 8 de webhook.php et remplacez 'your-secret-token-here' par votre token
   ```
   
   b. Configurer les permissions :
   ```bash
   # Assurez-vous que le serveur web peut exécuter git
   # L'utilisateur du serveur web (www-data, apache, nginx) doit avoir accès au repository
   
   # Exemple pour Apache/Nginx:
   sudo chown -R www-data:www-data /chemin/vers/lebonplan
   sudo chmod -R 755 /chemin/vers/lebonplan
   
   # Permissions pour les fichiers de log
   touch webhook.log deployment.log
   chmod 666 webhook.log deployment.log
   ```
   
   c. Configurer Git pour le serveur web :
   ```bash
   # En tant qu'utilisateur du serveur web
   sudo -u www-data git config --global user.email "deploy@example.com"
   sudo -u www-data git config --global user.name "Auto Deploy"
   
   # Configurer les credentials si nécessaire (pour repositories privés)
   sudo -u www-data git config --global credential.helper store
   ```

4. **Tester le webhook**
   
   a. Test manuel avec le script de déploiement :
   ```bash
   ./deploy.sh
   ```
   
   b. Test du webhook depuis GitHub :
   - Allez dans `Settings` > `Webhooks`
   - Cliquez sur votre webhook
   - Allez dans l'onglet "Recent Deliveries"
   - Cliquez sur "Redeliver" pour tester

5. **Vérifier les logs**
   ```bash
   # Logs du webhook
   tail -f webhook.log
   
   # Logs du déploiement
   tail -f deployment.log
   ```

### Sécurité

- **Ne partagez jamais votre token secret**
- Le webhook vérifie la signature de chaque requête
- Seules les requêtes authentifiées de GitHub sont traitées
- Les logs enregistrent toutes les tentatives d'accès

### Dépannage

**Le webhook ne fonctionne pas :**
1. Vérifiez les logs : `cat webhook.log`
2. Vérifiez les permissions du serveur web
3. Vérifiez que le token secret est identique sur GitHub et sur le serveur
4. Vérifiez que Git est installé et accessible par le serveur web

**Erreur "Permission denied" :**
```bash
# Assurez-vous que l'utilisateur du serveur web a les bonnes permissions
sudo chown -R www-data:www-data /chemin/vers/lebonplan
```

**Erreur "Git fetch/pull failed" :**
```bash
# Vérifiez la configuration Git
sudo -u www-data git config --list
# Vérifiez l'accès au repository
sudo -u www-data git fetch origin
```

### Utilisation Manuelle

Vous pouvez également déployer manuellement :
```bash
./deploy.sh
```
