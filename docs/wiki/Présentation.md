# Présentation

Le module **Funding** ajoute à Dolibarr la gestion complète des **dossiers de financement** liés aux documents commerciaux (propositions, commandes, factures). Il est conçu pour les activités de leasing / location avec option d'achat et la gestion de retenue de garantie.

## Objectifs

- Centraliser la gestion des organismes de financement et des dossiers associés aux tiers.
- Suivre le cycle de vie complet d'un dossier de financement : brouillon → validé → envoyé à l'organisme → accepté → en cours → clôturé / racheté / dénoncé.
- Calculer automatiquement loyer et retenue de garantie à partir de coefficients et d'échelles.
- Automatiser les relances (financements bientôt à terme) et la clôture des financements terminés via des tâches planifiées.
- Exposer les données via une API REST complète.
- Générer des documents (PDF et ODT) liés aux financements et coefficients.

## Fonctionnalités principales

### Financements (objet principal)
- Création d'un financement à partir d'une proposition commerciale, d'une commande ou d'une facture.
- Onglet **Funding** ajouté aux fiches tiers, propositions et commandes (avec badge de comptage à partir de Dolibarr 16).
- Calcul automatique du loyer (`amount_rent`) à partir du montant, de la durée, du coefficient et de l'échelle.
- Loyer personnalisable (`amount_rent_edit`) arrondi à l'euro supérieur, uniquement sur les propositions.
- Suivi des statuts du **dossier** (envoi organisme, pièces manquantes, acceptation, etc.) distincts des statuts du **financement**.
- Gestion des **6 emplacements de documents demandés** (`fundoc1`..`fundoc6`) avec pointage de présence, et de **6 documents annexes** (`funfoldoc1`..`funfoldoc6`).
- Gestion des **extensions** de financement.
- Reprise automatique des 4 premiers fichiers et du RIB depuis un autre financement.
- Affichage du RIB (sur le tiers ou sur le financement).
- Édition des notes publiques/privées sur la fiche financement.
- Gestion du presse-papiers pour certains champs.
- Indication si un financement existe déjà pour la commande/facture correspondante lors de l'ouverture d'une proposition.

### Coefficients
- Table de coefficients de loyer (`amount_of` → `amount_to`, `fk_duration`, `coef`, `fk_scale`, `fk_org`).
- Mise à jour forcée du taux et de la retenue de garantie via bouton dédié.

### Retenue de garantie
- Taux de retenue de garantie par tiers (`fk_soc`, `rate`).
- Montant de retenue de garantie calculé sur le financement.

### Automatisation
- **Tâches planifiées** (cron) : clôture des financements terminés et envoi d'un mail récapitulatif des financements bientôt à terme.
- **Triggers** : clonage du financement à la validation d'une commande depuis une proposition, changement de statut automatique selon le mode de règlement, etc.
- **Notifications par mail** configurables (modèle par défaut, validation, rapport).

### Intégration
- **API REST** complète pour financements, coefficients, retenues, dictionnaires (statuts, échelles, durées, types) et organismes.
- **Hooks** sur de nombreux contextes (emailtemplates, globalcard, formmail, notification, onlinesign, propallist, orderlist, invoicelist).
- **Substitutions** personnalisées (`core/substitutions/functions_funding.lib.php`).
- **Onglets** ajoutés aux tiers, propositions et commandes.
- **Multientité** géré sur l'objet Funding (champ `entity`).

## Composants du module

| Composant | Emplacement |
| --- | --- |
| Descripteur du module | `core/modules/modFunding.class.php` |
| Classes métier | `class/funding.class.php`, `class/coefficient.class.php`, `class/retention.class.php` |
| API REST | `class/api_funding.class.php` |
| Pages (listes, fiches, agenda) | `funding_list.php`, `funding_card.php`, `funding_agenda.php`, `fundingindex.php`, `coefficient_*`, `retention_*` |
| Administration | `admin/setup.php`, `admin/about.php`, `admin/changelog.php`, `admin/*_extrafields.php` |
| Triggers | `core/triggers/interface_99_modFunding_FundingTriggers.class.php` |
| Substitutions | `core/substitutions/functions_funding.lib.php` |
| Modèles de documents | `core/modules/funding/` |
| Bibliothèques | `lib/funding*.lib.php` |
| Schémas SQL | `sql/` |
| Traductions | `langs/fr_FR/funding.lang`, `langs/en_US/funding.lang` |

## Famille et positionnement

- **Famille** : `financial`
- **Position dans la famille** : `90`
- **Menu** : intégré sous le menu principal `bank` (Banque & Trésorerie) avec un sous-menu dédié **Funding**.
