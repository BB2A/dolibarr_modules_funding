# Objet Funding

L'objet **Funding** est l'objet principal du module. Il représente un dossier de financement associé à un tiers, un organisme de financement et un document commercial (proposition, commande ou facture).

- **Classe** : `class/funding.class.php` → `Funding extends CommonObject`
- **Table** : `llx_funding_funding` (`table_element = 'funding_funding'`)
- **Element** : `funding`
- **Trigger prefix** : `FUNDING_FUNDING`
- **Multientité** : gérée via le champ `entity`
- **Extrafields** : supportés (`isextrafieldmanaged = 1`)
- **Picto** : `fa-piggy-bank infobox-action`

## Statuts du financement (`status`)

Le financement possède deux familles de statuts : le statut du **financement** (`status`) et le statut du **dossier** (`status_folder`).

### Statuts du financement

| Constante | Valeur | Description |
| --- | --- | --- |
| `STATUS_DRAFT` | 0 | Brouillon |
| `STATUS_VALIDATED` | 1 | Validé (nouveau) |
| `STATUS_UPDATE` | 2 | À mettre à jour |
| `STATUS_ACCEPT` | 4 | Accepté |
| `STATUS_DENIED` | 5 | Refusé |
| `STATUS_RUNNING` | 6 | En cours |
| `STATUS_END` | 7 | Terminé |
| `STATUS_CANCELED` | 8 | Annulé |

### Statuts du dossier (`status_folder`)

| Constante | Valeur | Description |
| --- | --- | --- |
| `STATUS_FOLDER_SENDORG` | 1 | Dossier envoyé à l'organisme |
| `STATUS_FOLDER_LACK` | 2 | Pièces manquantes |
| `STATUS_FOLDER_LACKOK` | 3 | Pièces manquantes complétées |
| `STATUS_FOLDER_ACCEPT_RETENTION` | 5 | Accepté avec retenue de garantie |
| `STATUS_FOLDER_EXTENSION` | 9 | Extension |
| `STATUS_FOLDER_REDEEMED` | 20 | Rachat |
| `STATUS_FOLDER_DENOUNCED` | 21 | Dénoncé |
| `STATUS_FOLDER_CLOSED_TRANSFER` | 22 | Clôture par transfert |
| `STATUS_FOLDER_CLOSED_LESSOR` | 23 | Clôture par le bailleur |

> Le statut « Financement clôturé par le bailleur » a été ajouté en version 1.1.5.

## Champs principaux

| Champ | Type | Description |
| --- | --- | --- |
| `ref` | varchar(128) | Référence (préfixe `(PROV)` en brouillon) |
| `entity` | integer | Multientité (défaut 1) |
| `study_number` | varchar(128) | Numéro d'étude |
| `folder_number` | varchar(128) | Numéro de dossier |
| `fk_org` | integer | Organisme de financement (tiers) |
| `fk_soc` | integer | Tiers client |
| `fk_soc_invoice` | integer | Tiers facturation |
| `amount` | double | Montant à financer |
| `amount_maint` | double | Montant maintenance |
| `amount_total` | double | Montant total |
| `fk_duration` | integer | Durée (dictionnaire `c_funding_duration`) |
| `coef` | real | Coefficient appliqué |
| `fk_scale` | integer | Échelle (dictionnaire `c_funding_scale`) |
| `amount_rent` | double | Loyer calculé |
| `amount_rent_edit` | double | Loyer personnalisé (propositions uniquement, arrondi à l'euro supérieur) |
| `date_accepted` | date | Date d'acceptation |
| `date_endvalidity` | date | Date de fin de validité (calculée via `FUNDING_VALIDITY_MONTH`) |
| `date_delivery` | date | Date de livraison |
| `date_end_calculated` | date | Date de fin calculée |
| `date_signature` | date | Date de signature |
| `date_end` | date | Date de fin |
| `fk_funding_type` | smallint | Type de financement (dictionnaire `c_funding_type`) |
| `redemption` | smallint | Rachat (oui/non) |
| `redemption_number` | varchar(128) | Numéro de rachat |
| `retention` | smallint | Retenue de garantie (oui/non) |
| `retention_rate` | real | Taux de retenue de garantie |
| `retention_mount` | double | Montant de retenue de garantie |
| `fk_user_comm` | integer | Commercial |
| `description` | text | Description |
| `fundoc1`..`fundoc6` | varchar(255) | Documents demandés (6 emplacements) |
| `fundoc1check`..`fundoc6check` | smallint | Pointage des documents demandés |
| `funfoldoc1`..`funfoldoc6` | varchar(255) | Documents annexes (6 emplacements) |
| `extension` | smallint | Extension (défaut 0) |
| `note_public` | text | Note publique |
| `note_private` | text | Note privée |
| `origin` | varchar(128) | Origine (`propal`, `order`, …) |
| `origin_id` | integer | ID du document d'origine |
| `model_pdf` | varchar(255) | Modèle PDF |
| `last_main_doc` | varchar(255) | Dernier document généré |
| `billed` | smallint | Facturé |
| `funcheck` | smallint | Pointage |
| `status_folder` | smallint | Statut du dossier |
| `status` | smallint | Statut du financement |

## Cycle de vie

```
Brouillon (0) ──▶ Validé (1) ──▶ [envoi organisme] ──▶ Accepté (4)
                                              │
                                              ▼
                               À mettre à jour (2) ──▶ En cours (6) ──▶ Terminé (7)
                                              │
                                              ▼
                                     Refusé (5) / Annulé (8)
```

- Un financement ne peut pas être **validé** si le mode de règlement du document n'est pas celui configuré dans `FUNDING_ID_REGLEMENT`.
- Le passage en commande reprend le financement s'il est accepté.
- Le retour en brouillon réinitialise le statut du dossier.
- La validation automatique du financement se fait sur une commande.

## Comportements liés au mode de règlement

Lorsque le mode de règlement change sur le document :
- **De financement → autre** : clôture le financement.
- **D'un autre → financement** : réouvre le financement (à valider pour les commandes, à brouillon pour les propositions).

## Origine et liaison

Le financement n'utilise plus `fk_propal`/`fk_order` (supprimés depuis la 1.0.3). La liaison se fait via :
- `origin` : type du document (`propal`, `order`)
- `origin_id` : ID du document

Les liaisons d'objets natives Dolibarr (`llx_element_element`) sont également utilisées.

## Pages associées

| Page | Rôle |
| --- | --- |
| `funding_card.php` | Fiche d'un financement (consultation/édition) |
| `funding_list.php` | Liste des financements (filtres par statut, origine, extension) |
| `funding_agenda.php` | Événements (agenda) d'un financement |
| `fundingindex.php` | Page d'accueil du module |
