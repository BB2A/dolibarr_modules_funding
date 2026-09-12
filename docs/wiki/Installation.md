# Installation

## Prérequis

| Prérequis | Version |
| --- | --- |
| PHP | ≥ 8 |
| Dolibarr | ≥ 18 |
| Module `funding` | version actuelle `1.1.6` |

> Le module nécessite PHP 8 minimum et Dolibarr 18 minimum (`$this->phpmin = array(8)` et `$this->need_dolibarr_version = array(18)` dans le descripteur).

## Installation depuis un fichier ZIP (interface Dolibarr)

1. Téléchargez le module sous forme de fichier ZIP (par exemple depuis [Dolistore](https://www.dolistore.com)).
2. Dans Dolibarr, ouvrez le menu **Accueil → Configuration → Modules → Déployer un module externe**.
3. Uploadez le fichier ZIP.

### Vérification du répertoire `custom`

Si l'écran indique qu'aucun répertoire `custom` n'existe, vérifiez la configuration :

Dans le fichier `htdocs/conf/conf.php` de votre installation Dolibarr, décommentez (supprimez les `//`) les lignes suivantes :

```php
$dolibarr_main_url_root_alt = '/custom';
$dolibarr_main_document_root_alt = '/var/www/Dolibarr/htdocs/custom';
```

- **UNIX** : `$dolibarr_main_document_root_alt = '/var/www/Dolibarr/htdocs/custom';`
- **Windows** : `$dolibarr_main_document_root_alt = 'C:/My Web Sites/Dolibarr/htdocs/custom';`

## Installation manuelle (git)

Clonez le dépôt dans le répertoire `custom` de Dolibarr :

```bash
cd /var/www/Dolibarr/htdocs/custom
git clone https://github.com/BB2A/dolibarr_modules_funding.git funding
```

Le nom du dossier doit être `funding` (correspondant au nom du module).

## Étapes finales

Depuis votre navigateur :

1. Connectez-vous à Dolibarr en tant que **super-administrateur**.
2. Allez dans **Configuration → Modules**.
3. Recherchez le module **Funding** (famille *financial*).
4. **Activez** le module.

À l'activation :
- Les tables SQL du dossier `sql/` sont chargées (`_load_tables('/funding/sql/')`).
- Les permissions, menus, dictionnaires et tâches planifiées sont enregistrés.
- Le répertoire de données `/funding/temp` est créé.

## Mises à jour

Les scripts de migration SQL sont automatiquement exécutés lors des mises à jour du module :

- `sql/update_1.1.3-1.1.4.sql`
- `sql/update_1.1.5-1.1.6.sql`
- `sql/dolibarr_18.0.0-19.0.0.sql` (correction des événements `llx_actioncomm`)
- `sql/dolibarr_19.0.0-20.0.0.sql` (correction des liaisons d'objets `llx_element_element`)

> Après une mise à jour de Dolibarr (18→19, 19→20), désactivez puis réactivez le module pour exécuter les scripts de migration associés.

## Désactivation

Désactiver le module supprime les constantes, boîtes et permissions de la base Dolibarr. **Les répertoires de données ne sont pas supprimés.**
