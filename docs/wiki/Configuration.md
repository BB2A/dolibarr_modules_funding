# Configuration

La configuration du module se fait depuis **Configuration → Modules → Funding → Paramètres** (page `admin/setup.php`).

## Paramètres généraux

Les paramètres sont stockés dans la table `llx_const` avec le préfixe `FUNDING_`. Ils sont définis dans `admin/setup.php`.

### Règlement et valeurs par défaut

| Constante | Description | Type |
| --- | --- | --- |
| `FUNDING_ID_REGLEMENT` | Mode de règlement considéré comme « financement » (utilisé pour les triggers et la validation) | Sélection (mode de règlement, filtre `CRDT`) |
| `FUNDING_DEFAULT_DURATION` | Durée de financement par défaut à la création | Sélection (dictionnaire `c_funding_duration`) |
| `FUNDING_DEFAULT_SCALE` | Échelle de financement par défaut à la création | Sélection (dictionnaire `c_funding_scale`) |
| `FUNDING_DEFAULT_REDEMPTION` | Valeur par défaut du rachat (redemption) | Sélection |
| `FUNDING_DEFAULT_TYPE` | Type de financement par défaut | Sélection (dictionnaire `c_funding_type`) |
| `FUNDING_VALIDITY_MONTH` | Nombre de mois de validité (permet de calculer `date_endvalidity`) | Nombre entier ≥ 0 |

### Organismes de financement

| Constante | Description | Type |
| --- | --- | --- |
| `FUNDING_FILTRE_ORGANIZATION` | Filtre des organismes de financement affichés | Sélection (filtre tiers) |
| `FUNDING_DEFAULT_ORGANIZATION` | Organisme de financement par défaut à la création | Sélection (tiers) |

### E-mails / Notifications

| Constante | Description | Type |
| --- | --- | --- |
| `FUNDING_MAIL_DEFAULT` | Modèle d'e-mail par défaut | Sélection (modèle d'e-mail) |
| `FUNDING_MAIL_AUTOCOPY_TO` | Adresse(s) en copie automatique des mails | Texte |
| `FUNDING_MAIL_VALIDATION` | Modèle d'e-mail de validation/changement de statut | Sélection (modèle d'e-mail) |
| `FUNDING_MAIL_REPORT` | Modèle d'e-mail du rapport des financements bientôt à terme | Sélection (modèle d'e-mail) |

### Affichage et comportement

| Constante | Description | Type |
| --- | --- | --- |
| `FUNDING_NOCLOSEDFINISHAUTO_EXTENSION` | Ne pas clôturer automatiquement les financements en extension | Texte/booléen |
| `FUNDING_LISTE_THIRDPARTY_PROPAL` | Afficher les propositions de financement dans l'onglet financements des tiers | Texte/booléen |
| `FUNDING_LISTE_THIRDPARTY_PROPAL_SHORTLIST` | Affichage en liste courte des propositions de financement sur le tiers | Texte/booléen |
| `FUNDING_ENABLED_RENTEDIT` | Activer le loyer personnalisé (`amount_rent_edit`) | Texte/booléen |

## Dictionnaires

Le module fournit trois dictionnaires (éditables depuis **Configuration → Dictionnaires**) :

| Table | Libellé | Champs |
| --- | --- | --- |
| `llx_c_funding_duration` | Durées de financement (`Funding_duration`) | `code`, `label` |
| `llx_c_funding_scale` | Échelles de financement (`Funding_scale`) | `code`, `label` |
| `llx_c_funding_type` | Types de financement (`Funding_type`) | `code`, `label` |

## Champs personnalisés (extrafields)

Les extrafields sont gérés via les pages d'administration dédiées :

- `admin/myobject_extrafields.php` (funding)
- `admin/retention_extrafields.php` (retention)

Les tables d'extrafields correspondantes : `llx_funding_funding_extrafields`, `llx_funding_coefficient_extrafields`, `llx_funding_retention_extrafields`.

## Modèles de référence (numérotation)

Le préfixe d'addon est `FUNDING_` + nom de l'objet en majuscules + `_ADDON`. Les modèles de numérotation disponibles se trouvent dans `core/modules/funding/` :

- Funding : `mod_funding_standard.php`, `mod_funding_advanced.php`
- Coefficient : `mod_coefficient_standard.php`, `mod_coefficient_advanced.php`
- Retention : `mod_retention_standard.php`, `mod_retention_advanced.php`

## Pages d'administration

| Page | Rôle |
| --- | --- |
| `admin/setup.php` | Paramètres généraux du module |
| `admin/about.php` | À propos du module |
| `admin/changelog.php` | Historique des versions (affichage du `ChangeLog.md`) |
| `admin/myobject_extrafields.php` | Champs personnalisés Funding |
| `admin/retention_extrafields.php` | Champs personnalisés Retention |
