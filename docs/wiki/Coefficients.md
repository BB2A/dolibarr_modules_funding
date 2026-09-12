# Coefficients

L'objet **Coefficient** définit les taux de loyer appliqués selon des tranches de montant, une durée, une échelle et un organisme de financement.

- **Classe** : `class/coefficient.class.php` → `Coefficient extends CommonObject`
- **Table** : `llx_funding_coefficient` (`table_element = 'funding_coefficient'`)
- **Element** : `coefficient`
- **Extrafields** : supportés
- **Picto** : `fa-piggy-bank infobox-action`

## Champs

| Champ | Type | Description |
| --- | --- | --- |
| `ref` | varchar(128) | Référence (préfixe `(PROV)`) |
| `amount_of` | double | Montant de départ (borne inférieure) |
| `amount_to` | double | Montant d'arrivée (borne supérieure) |
| `fk_duration` | integer | Durée (dictionnaire `c_funding_duration`) |
| `coef` | real | Coefficient de loyer appliqué |
| `fk_scale` | integer | Échelle (dictionnaire `c_funding_scale`) |
| `fk_org` | integer | Organisme de financement (tiers) |
| `status` | smallint | Statut |

## Règles de calcul

- Le coefficient applicable à un financement est déterminé par : `fk_org` (organisme), `fk_duration` (durée), `fk_scale` (échelle) et le montant compris entre `amount_of` et `amount_to`.
- Le loyer (`amount_rent`) est calculé à partir du montant, du coefficient et de la durée.
- Un bouton **« Forcer la mise à jour du taux »** permet de recalculer le taux et la retenue de garantie même si le montant n'a pas changé.
- Le coef et la retenue de garantie ne sont **pas** mis à jour si le montant ne change pas (sauf action forcée).

## Modèles de numérotation

- `core/modules/funding/mod_coefficient_standard.php`
- `core/modules/funding/mod_coefficient_advanced.php`

## Pages associées

| Page | Rôle |
| --- | --- |
| `coefficient_card.php` | Fiche d'un coefficient |
| `coefficient_list.php` | Liste des coefficients |
| `coefficient_agenda.php` | Événements d'un coefficient |

## Triggers

Les triggers dédiés aux coefficients ont été ajoutés en version 1.0.5.
