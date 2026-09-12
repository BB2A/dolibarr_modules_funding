# Retenue de garantie

L'objet **Retention** définit le taux de retenue de garantie appliqué par tiers.

- **Classe** : `class/retention.class.php` → `Retention extends CommonObject`
- **Table** : `llx_funding_retention` (`table_element = 'funding_retention'`)
- **Element** : `retention`
- **Extrafields** : supportés
- **Picto** : `fa-piggy-bank infobox-action`

## Champs

| Champ | Type | Description |
| --- | --- | --- |
| `ref` | varchar(128) | Référence (préfixe `(PROV)`) |
| `fk_soc` | integer | Tiers |
| `rate` | real | Taux de retenue de garantie |
| `status` | smallint | Statut |

## Règles de calcul

- Le taux de retenue de garantie d'un financement est déterminé par le taux défini pour le tiers (`fk_soc`).
- Le montant de retenue de garantie (`retention_mount`) est calculé sur le financement.
- La mise à jour forcée du taux met à jour également la retenue de garantie.
- La mise à jour forcée des taux (version 1.0.10) met à jour le taux de la retenue de garantie (correctif : auparavant non mis à jour).

## Modèles de numérotation

- `core/modules/funding/mod_retention_standard.php`
- `core/modules/funding/mod_retention_advanced.php`

## Pages associées

| Page | Rôle |
| --- | --- |
| `retention_card.php` | Fiche d'une retenue de garantie |
| `retention_list.php` | Liste des retenues de garantie |
| `retention_agenda.php` | Événements d'une retenue de garantie |
