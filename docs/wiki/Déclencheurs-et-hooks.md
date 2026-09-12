# Déclencheurs et hooks

## Triggers

Le module fournit ses propres déclencheurs (déclarés via `'triggers' => 1`).

- **Fichier** : `core/triggers/interface_99_modFunding_FundingTriggers.class.php`
- **Classe** : `InterfaceFundingTriggers` (préfixe `99` = priorité d'exécution)
- **Préfixe de trigger** (Funding) : `FUNDING_FUNDING` → construit les clés `FUNDING_FUNDING_MODIFY`, `FUNDING_FUNDING_CREATE`, etc.

### Comportements automatisés des triggers

- **Validation d'une commande** depuis une proposition : clone le financement à partir de la proposition liée. Le filtre de recherche ajoute `c.targettype = 'commande'` pour éviter les collisions d'ID entre propositions et commandes (correctif 1.0.9).
- **Création d'une commande** : vérifie le lien avec la proposition pour copier le financement ; pas d'erreur PHP si le financement n'existe pas sur la propo ou sans lien vers une propo (correctif 1.1.0).
- **Changement du mode de règlement** sur le document :
  - De financement → autre règlement : clôture le financement.
  - D'un autre règlement → financement : réouvre le financement (à valider pour les commandes, à brouillon pour les propositions).
- **Mise à jour d'un financement** : trigger `FUNDING_UPDATE` (correctif 1.0.5 : doublon supprimé).
- **Coefficients et retenues de garantie** : triggers dédiés (depuis 1.0.5).
- Le trigger appelle `setRun` pour conserver le même comportement que le bouton « actif » (correctif 1.1.1).
- Nouveau fonctionnement `element@module` des triggers (correctif 1.1.0).

## Hooks (contextes)

Le module s'enregistre sur les contextes de hook suivants (`'hooks' => 'data' => array(...)`):

| Contexte | Utilisation |
| --- | --- |
| `emailtemplates` | Modèles d'e-mails |
| `globalcard` | Fiches globales |
| `formmail` | Formulaires d'envoi de mail |
| `notification` | Notifications |
| `onlinesign` | Signature en ligne |
| `propallist` | Liste des propositions commerciales |
| `orderlist` | Liste des commandes |
| `invoicelist` | Liste des factures |

## Substitutions

- **Fichier** : `core/substitutions/functions_funding.lib.php`
- Déclaré via `'substitutions' => 1`.
- Permet la substitution des types de financement et d'autres variables dans les modèles.
- Hook de notification Dolibarr V16 ajouté (1.0.4).

## Onglets (tabs)

Le module ajoute des onglets (`$this->tabs`) :

| Objet | Onglet | Page | Condition (≥ 16.0.0) |
| --- | --- | --- | --- |
| `thirdparty` | Funding | `funding_list.php?socid=__ID__` | Badge de comptage `getcountForThird`, droit `funding->lists` |
| `propal` | Funding | `funding_card.php?typedoc=propal&iddoc=__ID__` | Badge `getcountForPropal`, droit `funding->read` |
| `order` | Funding | `funding_card.php?typedoc=order&iddoc=__ID__` | Badge `getcountForOrder`, droit `funding->read` |

> Avant Dolibarr 16, les onglets n'ont pas de badge de comptage.

## Hook « dernier financement »

Un hook affiche le dernier financement sur la fiche commande du tiers (ajouté en 1.0.4).

## Autres hooks d'affichage

- Affichage dans les listes de propositions, commandes et factures (classe `actions_funding`).
- Avertissement sur les propositions financières lorsqu'une commande est faite pour celle-ci (1.0.8).
- Ajout de colonnes dans les listes de propositions et commandes (1.0.8).
