# API REST

Le module expose une **API REST** complète (déclarée via `'api' => 1` dans le descripteur) implémentée par la classe `FundingApi` (`class/api_funding.class.php`).

- **Classe** : `FundingApi extends DolibarrApi`
- **Accès** : `{requires user,external}` — authentification requise (utilisateur interne ou externe).
- **Préfixe de base** : `/api/index.php/funding/`

L'API gère trois objets : **Funding**, **Coefficient** et **Retention**, ainsi que des dictionnaires et organismes. Les permissions sont vérifiées via `DolibarrApiAccess::$user->hasRight('funding', ...)`.

## Coefficients

| Méthode HTTP | Endpoint | Droit requis | Description |
| --- | --- | --- | --- |
| GET | `/coefficients/{id}` | `funding/coefficient/read` | Récupère un coefficient |
| GET | `/coefficients/` | `funding/coefficient/read` | Liste les coefficients (tri, pagination, filtres SQL) |
| POST | `/coefficients/` | `funding/coefficient/write` | Crée un coefficient |
| PUT | `/coefficients/{id}` | `funding/coefficient/write` | Modifie un coefficient |
| DELETE | `/coefficients/{id}` | `funding/coefficient/delete` | Supprime un coefficient |

## Fundings

| Méthode HTTP | Endpoint | Droit requis | Description |
| --- | --- | --- | --- |
| GET | `/fundings/{id}` | `funding/read` | Récupère un financement |
| GET | `/fundings/` | `funding/read` | Liste les financements (tri, pagination, filtres SQL) |
| POST | `/fundings/` | `funding/write` | Crée un financement |
| PUT | `/fundings/{id}` | `funding/write` | Modifie un financement |
| DELETE | `/fundings/{id}` | `funding/delete` | Supprime un financement |

## Dictionnaires et organismes

| Méthode HTTP | Endpoint | Droit requis | Description |
| --- | --- | --- | --- |
| GET | `/fundings/statuslist` | `funding/read` | Liste des statuts de financement |
| GET | `/fundings/config` | `funding/read` | Configuration du module (`funding_id_reglement`, `funding_validity_month`) |
| GET | `/dictionary/scales/` | `funding/read` | Liste des échelles (`c_funding_scale`) |
| GET | `/dictionary/durations/` | `funding/read` | Liste des durées (`c_funding_duration`) |
| GET | `/dictionary/types/` | `funding/read` | Liste des types (`c_funding_type`) |
| GET | `/organizations/` | `funding/read` | Liste des organismes de financement |

## Documents d'un financement

La gestion des documents d'un financement se fait via un champ de document spécifique (`docfield` correspondant à `fundoc1`..`fundoc6`).

| Méthode HTTP | Endpoint | Droit requis | Description |
| --- | --- | --- | --- |
| POST | `/fundings/{id}/documents/{docfield}` | `funding/write` | Téléverse un document sur un champ de document donné |
| GET | `/fundings/{id}/documents/{docfield}` | `funding/read` | Récupère le document d'un champ. Si aucun `docfield` n'est spécifié, récupère tous les documents de financement (avec traduction des noms de champs) |
| DELETE | `/fundings/{id}/documents/{docfield}` | `funding/delete` | Supprime le document d'un champ |

## Retentions

| Méthode HTTP | Endpoint | Droit requis | Description |
| --- | --- | --- | --- |
| GET | `/retentions/{id}` | `funding/retention/read` | Récupère une retenue de garantie |
| GET | `/retentions/` | `funding/retention/read` | Liste les retenues de garantie |
| POST | `/retentions/` | `funding/retention/write` | Crée une retenue de garantie |
| PUT | `/retentions/{id}` | `funding/retention/write` | Modifie une retenue de garantie |
| DELETE | `/retentions/{id}` | `funding/retention/delete` | Supprime une retenue de garantie |

## Paramètres communs des listes (GET)

Les endpoints de liste acceptent les paramètres standards Dolibarr :

| Paramètre | Type | Description |
| --- | --- | --- |
| `sortfield` | string | Champ de tri (défaut `t.rowid`) |
| `sortorder` | string | Sens de tri (`ASC` / `DESC`) |
| `limit` | int | Nombre de résultats par page (défaut 100) |
| `page` | int | Numéro de page (défaut 0) |
| `sqlfilters` | string | Filtres au format Dolibarr Universal Filter, ex. `(t.ref:like:'SO-%')` |
| `properties` | string | Restreint les propriétés renvoyées (séparées par virgules) |

## Variables de configuration utilisées par l'API

Le constructeur de `FundingApi` charge :

- `FUNDING_ID_REGLEMENT` : mode de règlement « financement » (utilisé par la logique métier de l'API).
- `FUNDING_VALIDITY_MONTH` : nombre de mois de validité (calcul de `date_endvalidity`).

Ces valeurs sont également exposées en lecture via l'endpoint `GET /fundings/config` :

```bash
curl -H "DOLAPIKEY: <token>" \
  "https://mon.dolibarr.tld/api/index.php/funding/fundings/config"
# {"funding_id_reglement":4,"funding_validity_month":3}
```

## Exemple

```bash
# Lister les financements acceptés
curl -H "DOLAPIKEY: <token>" \
  "https://mon.dolibarr.tld/api/index.php/funding/fundings/?sqlfilters=(t.status:=:4)"

# Récupérer un financement
curl -H "DOLAPIKEY: <token>" \
  "https://mon.dolibarr.tld/api/index.php/funding/fundings/123"
```
