# Schéma de base de données

Le module crée ses tables et dictionnaires lors de l'activation (scripts dans `sql/`). Toutes les tables utilisent le préfixe Dolibarr `llx_` (configurable).

## Tables principales

### `llx_funding_funding`

Table de l'objet principal **Funding**.

```sql
CREATE TABLE llx_funding_funding (
  rowid integer AUTO_INCREMENT PRIMARY KEY,
  ref varchar(128) DEFAULT '(PROV)',
  entity integer DEFAULT 1,           -- multi-entité
  study_number varchar(128),
  folder_number varchar(128),
  fk_org integer NOT NULL,            -- organisme de financement (tiers)
  fk_soc integer NOT NULL,            -- tiers client
  fk_soc_invoice integer,             -- tiers facturation
  amount double,
  amount_maint double,
  amount_total double,
  fk_duration integer NOT NULL,       -- dictionnaire c_funding_duration
  coef real,
  fk_scale integer NOT NULL,          -- dictionnaire c_funding_scale
  amount_rent double,
  amount_rent_edit double,
  date_accepted date,
  date_endvalidity date,
  date_delivery date,
  date_end_calculated date,
  date_signature date,
  date_end date,
  fk_funding_type smallint NOT NULL,  -- dictionnaire c_funding_type
  redemption smallint,
  redemption_number varchar(128),
  retention smallint,
  retention_rate real,
  retention_mount double,
  fk_user_comm integer,
  description text,
  fundoc1 varchar(255), fundoc1check smallint,
  fundoc2 varchar(255), fundoc2check smallint,
  fundoc3 varchar(255), fundoc3check smallint,
  fundoc4 varchar(255), fundoc4check smallint,
  fundoc5 varchar(255), fundoc5check smallint,
  fundoc6 varchar(255), fundoc6check smallint,
  funfoldoc1..6 varchar(255),
  extension smallint DEFAULT 0,
  note_public text,
  note_private text,
  date_creation datetime NOT NULL,
  tms timestamp,
  fk_user_creat integer NOT NULL,
  fk_user_modif integer,
  origin varchar(128) NOT NULL,       -- 'propal', 'order', ...
  origin_id integer NOT NULL,
  import_key varchar(14),
  model_pdf varchar(255),
  last_main_doc varchar(255),
  billed smallint,
  funcheck smallint,
  status_folder smallint,
  status smallint NOT NULL
);
```

### `llx_funding_coefficient`

Table des **coefficients** de loyer.

```sql
CREATE TABLE llx_funding_coefficient (
  rowid integer AUTO_INCREMENT PRIMARY KEY,
  ref varchar(128) DEFAULT '(PROV)',
  amount_of double NOT NULL,
  amount_to double NOT NULL,
  fk_duration integer NOT NULL,
  coef real NOT NULL,
  fk_scale integer NOT NULL,
  fk_org integer NOT NULL,
  date_creation datetime NOT NULL,
  tms timestamp,
  fk_user_creat integer NOT NULL,
  fk_user_modif integer,
  import_key varchar(14),
  status smallint NOT NULL
);
```

### `llx_funding_retention`

Table des taux de **retenue de garantie** par tiers.

```sql
CREATE TABLE llx_funding_retention (
  rowid integer AUTO_INCREMENT PRIMARY KEY,
  ref varchar(128) DEFAULT '(PROV)',
  fk_soc integer NOT NULL,
  rate real NOT NULL,
  date_creation datetime NOT NULL,
  tms timestamp,
  fk_user_creat integer NOT NULL,
  fk_user_modif integer,
  status smallint NOT NULL
);
```

## Tables d'extrafields

| Table | Objet |
| --- | --- |
| `llx_funding_funding_extrafields` | Extrafields Funding |
| `llx_funding_coefficient_extrafields` | Extrafields Coefficient |
| `llx_funding_retention_extrafields` | Extrafields Retention |

## Dictionnaires

| Table | Description | Champs |
| --- | --- | --- |
| `llx_c_funding_duration` | Durées de financement | `rowid`, `code`, `label`, `active` |
| `llx_c_funding_scale` | Échelles de financement | `rowid`, `code`, `label`, `active` |
| `llx_c_funding_type` | Types de financement | `rowid`, `code`, `label`, `active` |

## Triggers d'action

`llx_c_action_trigger` : le module enregistre ses propres triggers d'action (visible dans l'agenda Dolibarr).

## Relations

- Les liaisons entre financements et documents (propositions, commandes) se font via les champs `origin` / `origin_id` **et** via la table native Dolibarr `llx_element_element`.
- Les organismes de financement sont des tiers (`llx_societe`), référencés par `fk_org` et `fk_soc`.
- Les durées, échelles et types sont des dictionnaires (`llx_c_funding_*`), référencés par les clés étrangères `fk_duration`, `fk_scale`, `fk_funding_type`.

## Scripts de migration

| Script | Rôle |
| --- | --- |
| `update_1.1.3-1.1.4.sql` | Migration 1.1.3 → 1.1.4 |
| `update_1.1.5-1.1.6.sql` | Migration 1.1.5 → 1.1.6 (statuts de financement) |
| `dolibarr_18.0.0-19.0.0.sql` | Correction des événements `llx_actioncomm` |
| `dolibarr_19.0.0-20.0.0.sql` | Correction des liaisons d'objets `llx_element_element` |
| `dolibarr_allversions.sql` | Script global toutes versions |

> Au passage de Dolibarr 18 à 19, l'`elementtype` `funding` devient `funding_funding` : `UPDATE llx_actioncomm SET elementtype = 'funding_funding' WHERE elementtype = 'funding'` et `UPDATE llx_element_element SET targettype = 'funding_funding' WHERE targettype = 'funding'`.
