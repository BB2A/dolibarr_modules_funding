# Changelog

Historique des versions du module Funding. Source : `ChangeLog.md` à la racine du dépôt.

## [DEV] — Dolibarr 24
- PHP min 8, Dolibarr min 18.

## [1.1.6] — 09/2026, Dolibarr 24
- NEW : gestion des variables de configuration `FUNDING_ID_REGLEMENT` et `FUNDING_VALIDITY_MONTH` dans le constructeur de `FundingApi`.
- NEW : traduction des noms de champs dans la récupération des documents de financement.
- NEW : récupération de tous les documents de financement si aucun champ de document n'est spécifié (`getFundingDocument`).
- NEW : API — nouvelles méthodes pour les documents (upload, récupération, suppression).
- NEW : API — listes des statuts, échelles, durées, types de financement et organismes.
- FIX : remplacement de la vérification d'activation par `isModEnabled`.
- FIX : mise à jour du type de champ `date_end` et paramètres de `createFromClone`.

## [1.1.6] — 00/2026, Dolibarr 23
- NEW : script de migration des statuts de financement.
- NEW : nouveaux statuts dans le formulaire de clôture.

## [1.1.5] — 08/2026, Dolibarr 23
- NEW : statut « Financement clôturé par le bailleur ».
- NEW : champ `date_end` toujours modifiable si le statut n'est pas clôturé.

## [1.1.4] — 08/2026, Dolibarr 23
- NEW : édition des notes sur la fiche financement.
- NEW : préfixe de déclencheur pour la classe `Funding`.
- NEW : gestion du presse-papiers ; description et notes toujours éditables.
- NEW : date de fin calculée dans la classe `Funding`.
- NEW : API REST.
- NEW : nouvelle vue « messaging ».
- FIX : optimisation de l'affichage des fichiers sur la fiche financement.

## [1.1.3] — 01/2025, Dolibarr 22
- NEW : sélection de l'organisme par défaut, look & feel (titre/picto), booleans RG/Rachat.
- NEW : mail des financements avec changement de statut automatique ; mail des financements bientôt à terme (avec statuts).
- FIX : accents dans les sujets de mail ; en-US par défaut pour les tâches planifiées ; erreurs PHP sur l'agenda.
- FIX : scripts SQL de migration Dolibarr 18→19 (`llx_actioncomm`) et 19→20 (`llx_element_element`).

## [1.1.2] — 11/2025, Dolibarr 22
- NEW : mail récapitulatif des financements bientôt à terme ; look & feel des mails.
- FIX : warnings PHP dans les cron ; corrections d'affichage des tableaux.

## [1.1.1] — 06/2025
- FIX : vérification de la date de livraison renseignée (rétrocompatible).
- FIX : trigger appelle `setRun` pour cohérence avec le bouton actif.

## [1.1.0] — 04/2025
- NEW : durée de validité, date d'acceptation, date de validité, paramètre « nombre de jours validité ».
- FIX : retour en brouillon, erreurs PHP onglet événements, clonage depuis propo.

## [1.0.10] — 03/2025
- FIX : mise à jour forcée des taux met à jour la retenue de garantie.
- FIX : date de livraison prise en compte à la facturation.

## [1.0.9] — 08/2024
- FIX : validation d'une commande, filtre `c.targettype = 'commande'` pour éviter les collisions d'ID.

## [1.0.8] — 02/2024
- NEW : compatibilité Dolibarr 18 (`delivery_date`), avertissement commande sur propo, permission Add, colonnes listes.

## [1.0.7] — 07/2023
- NEW : recherche des 4 premiers fichiers/RIB, loyer personnalisé arrondi supérieur, multientité, `fk_invoice`.

## [1.0.6] — 07/2023
- NEW : recherche du RIB, bouton de mise à jour forcée des taux.

## [1.0.5] — 05/2023 / 10/2022
- NEW : triggers coefficients/retention, scripts SQL, `sendDocumentFunding()`, affichage RIB.
- FIX : trigger `FUNDING_UPDATE` doublonné, retour après suppression.

## [1.0.4] — 03/2022
- NEW : hook notification V16, hook dernier financement, badges d'onglets (V16).
- FIX : PHP 8 warnings, permissions suppression document, sécurité utilisateurs externes.

## [1.0.3] — 02/2022
- NEW : statuts dossier (rachat/dénoncé), mass actions, tâches planifiées, substitutions, pointage.
- FIX : suppression `fk_propal`/`fk_order`, recherche fiche tiers.

## [1.0.2] — 01/2022
- NEW : changelog dans les paramètres, gestion du mode de règlement, bouton annulé, mail par défaut, images en PDF.
- FIX : filtres, droits, affichage mobile.

## [1.0.1] — 10/2021
- Liste des demandes de pré-étude, affichage complet des financements.

## [1.0.0]
- Version initiale.

> La liste complète et détaillée est disponible dans le fichier `ChangeLog.md` du dépôt.
