# CHANGELOG FUNDING FOR [DOLIBARR ERP CRM](https://www.dolibarr.org)

## [DEV] - 00/2026 Dolibarr 24
- PHP min 8
- Dolibarr min 18

- NEW - 12/09/2026 - API Ajout de la méthode setFundingAcceptedDenied reproduisant le comportement de l'action setAcceptedRefused de funding_card.php
- NEW - 12/09/2026 - API Ajout de la possibilité de marquer un document de financement comme demandé (POST/DELETE fundings/{id}/documents/{docfield}/request), sur le même principe que la case "filecheck" de funding_card.php, seulement lorsque le document correspondant n'est pas fourni
- NEW - 12/09/2026 - API Ajout d'une méthode de validation du financement (POST fundings/{id}/validate) conforme à la fiche web
- NEW - 12/09/2026 - API Ajout de la possibilité de réouvrir un financement via POST fundings/{id}/reopen, aux mêmes conditions que le bouton "ReOpen" de funding_card.php (droit funding:write, origine <> propal, statut >= STATUS_RUNNING, remise au statut STATUS_ACCEPT via FUNDING_REOPEN)
- NEW - 12/09/2026 - API Ajout de l'endpoint GET fundings/config exposant les variables de configuration FUNDING_ID_REGLEMENT et FUNDING_VALIDITY_MONTH

- FIX - 12/09/2026 - API Utilisation de property_exists() au lieu de isset() pour la détection du champ de demande de document (fundoc{N}check) car isset() retourne false pour une propriété déclarée à null


## [1.1.6] - 09/2026 Dolibarr 24
- PHP min 8
- Dolibarr min 18

- NEW - 12/09/2026 - API Ajout de la possibilité de mettre un financement en prolongation via POST fundings/{id}/extension
- NEW - 12/09/2026 - API Ajout de la possibilité de clôturer un financement via POST fundings/{id}/close
- NEW - 11/09/2026 - Ajout de la gestion des variables de configuration FUNDING_ID_REGLEMENT et FUNDING_VALIDITY_MONTH dans le constructeur de FundingApi
- NEW - 11/09/2026 - Ajout de la prise en charge de la traduction des noms de champs dans la méthode de récupération des documents de financement
- NEW - 11/09/2026 - Ajout de la possibilité de récupérer tous les documents de financement si aucun champ de document n'est spécifié dans la méthode getFundingDocument
- NEW - 10/09/2026 - API Ajout de nouvelles méthodes pour les documents
- NEW - 09/09/2026 - Add document upload API endpoints for Funding
- NEW - 09/09/2026 - Update document upload API to use specific doc fields
- NEW - 09/09/2026 - Refactor document upload API to use sendDocumentFunding logic
- NEW - 07/09/2026 - API Ajout de nouvelles méthodes pour récupérer les listes de statuts, échelles, durées et types de financement ainssi que les organisations

- FIX - 11/09/2026 - Remplacer la vérification de l'activation du module de financement par la fonction isModEnabled
- FIX - 10/09/2026 - Supprimer les méthodes d'upload, de récupération et de suppression de documents pour le financement
- FIX - 07/09/2026 - Mise à jour du type de champ 'date_end' et ajout de paramètres à la méthode 'createFromClone'

## [1.1.6] - 00/2026 Dolibarr 23
- PHP min 8
- Dolibarr min 18

- NEW - 24/08/2026 - Ajout d'un script de migration pour la mise à jour des statuts de financement
- NEW - 24/08/2026 - Mise à jour des statuts de financement et ajout de nouveaux statuts dans le formulaire de clôture

- FIX - 00/00/2026 - 

## [1.1.5] - 08/2026 Dolibarr 23
- PHP min 8
- Dolibarr min 18

NEW - 24/08/2026 - Ajout du statut "Financement clôturé par le bailleur" 
NEW - 24/08/2026 - Mise à jour des champs date_end pour être toujours modifiables si le statut n'est pas en clot

## [1.1.4] - 08/2026 Dolibarr 23
- PHP min 8
- Dolibarr min 18

- NEW - 23/08/2026 - Ajout de l'edition des notes sur la fiche financement
- NEW - 23/08/2026 - Ajout d'un préfixe de déclencheur pour la classe Funding et mise à jour des commentaires pour une meilleure clarté
- NEW - 13/08/2026 - Mise à jour des entrées de menu pour le module de financement, changement de 'funding' à 'bank'.
- NEW - 10/08/2026 - Ajout de la gestion du presse-papiers pour certain champs. La description et les notes sont toujours editable
- NEW - 10/08/2026 - Mise à jour des droits d'auteur et simplification de la logique de gestion des statuts dans la classe InterfaceFundingTriggers
- NEW - 07/08/2026 - Mise à jour des droits d'auteur, amélioration des commentaires et ajout de la gestion de la date de fin calculée dans la classe Funding
- NEW - 06/08/2026 - NEW - Mise à jour des droits d'auteur et amélioration des commentaires pour l'affichage dans les listes de Propos, Commandes et Factures action class
- NEW - 06/08/2026 - Modification sructure SQL
- NEW - 06/08/2026 - Ajout d'un fichier de règles pour le standard de codage Dolibarr dans ruleset.xml
- NEW - 06/08/2026 - Amélioration de la logique d'affichage pour la création d'un nouvel objet Funding
- NEW - 03/02/2026 - Ajout des API REST
- NEW - 26/01/2026 - New view messaging

- FIX - 12/08/206 - Optimisation du code sur la fiche financement pour l'affichage des fichiers
- FIX - 12/08/2026 - Nettoyage du code pour les lignes de détail pas de ligne de détail

## [1.1.3] - 01/2025 Dolibarr 22
- NEW - 22/12/2025 - Dans les param sélection de l'organisme par default
- NEW - 22/12/2025 - Déplacement de la position d'affichage de l'organisme
- NEW - 20/12/2025 - Look and fell title and picto
- NEW - 20/12/2025 - Préparation pour présentation de l'agenda format message
- NEW - 14/12/2025 - Boolean pour affichage checkbox RG et Rachat
- NEW - 06/12/2025 - Look and Feel mail des financement avec changement de statut automatique
- NEW - 06/12/2025 - Mail des financements bientot fini ajout des statuts
- NEW - 06/12/2025 - Mail des financements bientot fini garde egalement ceux passé mais toujour actif

- FIX - 05/01/2026 - Supprime les problémes d'accent dans les sujet de mail
- FIX - 04/01/2026 - Ajout des dans en-US car lange prise par default pour les tavhes planifiées
- FIX - 26/12/2025 - Erreur à l'envoie de mail  l'orsque un contact est présent sur le document
- FIX - 22/12/2025 - Supprimer la contrainte de selection redemption et retentation
- FIX - 20/12/2025 - Erreur PHP on page agenda
- FIX - 07/12/2025 - Ajout d'un script sql pour la mise à jour de dolibarr 19.0.0 à 20.0.0 pour que les liésons d'objet llx_element_element
- FIX - 07/12/2025 - Ajout d'un script sql pour la mise à jour de dolibarr 18.0.0 à 19.0.0 pour que les corrections d'evennement llx_actioncomm


## [1.1.2] - 11/2025 Dolibarr 22
- NEW - 17/11/2025 - Envoie d'un mail récapitulatif avec tout les finicements bientôt fini
- NEW - 17/11/2025 - Look and Feel mail

- FIX - 17/11/2025 - Fix php warning dans les cron + lang
- FIX - 09/11/2025 - Update send mail des financements bientot fini 
- FIX - 05/09/2025 - Correction affichage des tableaux


## [1.1.1] - 06/2025
- FIX - 04/06/2025 - Corrections vérificartion date de livraison renseigné sur le document avec retrocompatible
- FIX - 04/06/2025 - Dans le trigger appel à la fonction setRun pour avoir le même comprtement avec le bouton actif 

## [1.1.0] - 04/2025

- NEW - 20/04/2025 - Ajout du parametre durée de validité
- NEW - 03/04/2025 - Ajout d'un champ date d'acceptation et un champ date de validité.
- NEW - 03/04/2025 - Ajout d'un paramettre générale du module "nombres de jours validité" qui permet de calculer la date de fin de validité

- FIX - 20/04/2025 - Corrections sur ajout date de vilidité
- FIX - 20/04/2025 - PHP erreurs onglet  événements d'un financement
- FIX - 19/04/2025 - Lors de la réation d'une commande on vérifie le lien avec propo pour copier le finanacement. Si le financement n'exister pas sur la propo ou pas de lien vers une propo erreur php.
- FIX - 03/04/2025 - Modification de triggers nouveau fonctionnement element@module
- FIX - 03/04/2025 - Changement du nom de l'editeur


## [1.0.10] - 03/2025
- FIX - La mise à jour forcé des taux ne mettait pas à jour le taux de la retenu de garantie
- FIX - La date de livraison n'etait plus pris en compte quand le document etait passer en facture avant la livraison le statut actif du financement bloqué la mise a jour de la date de signature.

## [1.0.9] - 08/2024
- FIX - Validation d'une commande, on clone le financement à partir d'une proposition. Modification du filtre de recherche de la proposition lié à cette commande en ajoutant 'c.targettype = 'commande'', car les id de commande ou de proposition pouvait être les mêmes qu'un financement ce qui faisait récupérer le mauvais financement.
- FIX - Changement $object->element pour etre conforme avec dolibarr

## [1.0.8] - 02/2024
- FIX - Compatibilitée à partir de la version 18.0.0 dans les commandes le champ date_livraison est maintenant delivery_date.

- NEW - Ajout d'un avertissement sur les propositions financiére l'orsque une commande est faite pour celle-ci.
- NEW - Permission Add plutôt que manage pour le retour en brouillon.
- NEW - Ajout d'une colone dans la liste des propositions et des commandes

## [1.0.7] - 07/2023
- FIX - Loyer personnalisé uniquement sur les propositions pour l'affichage sur les PDF et les mail. Sur les commandes et factures aucun intéré doit refléter le montant réel.
- FIX - Amélioration sur l'envoie du mail pour les financements bientot à therme.

- NEW - Ajout de la recherche des 4 premiers fichier dans un autre financement
- NEW - Loyer personalisé arrondi à l'euro supérieur.
- NEW - Si ouverture proposition financiere indique si un financement existe pour la commande ou facture correspondante
- NEW - Ajout de "entity" en base sur funding pour l'implémentation du multisociété(Coef et retenue de garantie commun au société à définir plus tard)
- NEW - Ajout de "fk-invoice" en base sur funding pour le ratachement future à la facture.

## [1.0.6] - 07/2023
- FIX - Ammélioration de la gestion des erreurs et messages sur l'envoie de fichier
- FIX - Pas de mise à jour du taux et de la retenu de garantie si il y à pas de modification du montant.

- NEW - Ajout de la recherche du RIB dans un autre financement
- NEW - Ajout d'un boutton pour forcer la mise a jour avec taux et retenu de garantie

## [1.0.5] - 05/2023
- FIX - Trigger FUNDING_UPDATE was doubled to update funding
- FIX - Erreur de retour apres la modification d'une commande avec financement annuler $result n'etait pas initialisé

- NEW - Ajout des triggers pour les coeficients et les retenu de garantie
- NEW - Ajout du script sql lors de la mise à jour du module
- NEW - Ajout du positionement de la colonne de selection à gauche avec l'option MAIN_CHECKBOX_LEFT_COLUMN
- NEW - Ajout du script sql lors de la mise à jour de dolibarr

## [1.0.5] - 10/2022

- FIX - Update security check
- FIX - Le retour apres suppression se fait correctement sur la fiche du document ou sur la liste
- FIX - Création de la fonction [sendDocumentFunding()] pour une meilleur gestion des fichiers envoyer
- FIX - Le coef et la retenu de garantie n'ai pas mise à jour si le montant ne change pas
- FIX - Pré-envoyer un e-mail et ajouter une pièce jointe envoyer le e-mail automatiquement

- NEW - Afficher si le RIB est bien présent (Faire attention si le RIB est sur le Tiers Ou sur le financement).
- NEW - Ajouter un bouton pour nettoyer le montant de maintenance et le loyer personnalisé.

## [1.0.4] - 03/2022

- FIX - Php V8 warning class html form
- FIX - Enregistrement d'une piéce demandé plus de message d'erreur pas de fichier
- FIX - Permission pour la suppression de document
- FIX - Finnancement pouvant etre actif sans date de livraison
- FIX - Sécurité si utilisateur externe ne vois rien

- NEW - Ajout hook notification Dolibarr V16
- NEW - Ajout hook dernier financement sur comm card du tiers
- NEW - Amélioration pour affichage technicien
- NEW - Ajout de badge dans les onglets pour connaitre le nombre de financement à partir de Dolibarr V16

## [1.0.3] - 02/2022

- FIX - Désactivation visibilité origin and origin_id
- FIX - Erreur sur fonction prolonguation
- FIX - Erreur massaction accept denied
- FIX - Substitution funding type
- FIX - Passage en commande reprend si le financment est accepté
- FIX - Retour brouillon init statut dossier
- FIX - Supprimer fk_propal et fk_order dans la base (non utile) remplacé par origin et orign_id
- FIX - Recherche sur une fiche tier renvoyer sur la liste compléte.

- NEW - Paramettre pour ne pas afficher les propositions de financements dans l'onglet financements des tiers
- NEW - Ajout des listes brouillon
- NEW - Ajout bouton pour indiquer l'envoie du dossier et ajout dans les mass actions
- NEW - Ajout bouton pour indiquer qu'il manque des piéces au dossier
- NEW - Ajout des mass actions
- NEW - Ajout du statut accépté avec retenu de garantie
- NEW - Ajout du statut dossier racheté ou dénoncé lors de la clôture d'un financement manuelement.
- NEW - Ajout des taches planifiés
- NEW - Ajout de substitutions
- NEW - Prise en compte des réglages par defaut
- NEW - Ajout retour liste sur les fiches
- NEW - Ajout du montant de retenue de garantie
- NEW - Ajout commentaire financement terminé
- NEW - Indiquer les documents demandé
- NEW - Ajout du pointage des financements

## [1.0.2] - 01/2022 (Désactiver et activer le module)

- FIX - Désactiver le boutton d'envoie à l'organisme pour plus de clarté car non foctionnel pour le moment
- FIX - Menu nouvelle proposition financière ne filtré pas sur le bon statut. (désactiver activer le module)
- FIX - Correction sur l'historique de certains événements
- FIX - Plus de contrainte si le document n'est pas validé ou déjà livré
- FIX - Correction des droits (problèmes constaté lorsque l'on afficher l'apercu d'un document)
- FIX - Correction affichage liste des documents sur mobile
- FIX - Correction filtrage par date dans les listes
- FIX - Le montant personnalisé ne peut plus être à zéro
- FIX - Erreur à la suppression d'un document sur proposition. La suppression sur une commande est possible uniquement si le statut est inférrieur à actif

- NEW - Ajout du changelog dans les paramètres du module
- NEW - Modification de "à propos" dans les paramettres du module
- NEW - L'osque le mode de règlement change sur le document
        - De financement à autre règlement -> clôture le financement
        - D'un autre règlement à financement -> Réouvre le financement à valider pour les commandes et à brouillon pour les propositions
- NEW - Le financement ne peut pas être validé si le mode de règlement du document n'est pas celui en paramètre (financement)
- NEW - Ajout d'un bouton annulé
- NEW - Amélioration de la page d'index des financements
- NEW - Un mail par défaut est paramétrable
- NEW - Ajout du document rachat signé (seulement si "rachat" est à oui)
- NEW - Validation automatique du financement sur une commande 
- NEW - Possibilité d'ajouter des images dans les pièces jointes (Elles sont transformées en PDF.)
- NEW - Liste déroulante pour sélectionner le paramètre du mode de règlement
- NEW - Liste déroulante pour sélectionner le paramètre du filtre des organismes de financement

## [1.0.1] - 10/2021

- Liste des demandes de pre-etude afficche correctement la liste
- Affichage de la liste compléte des financements si l'utilisateur peut voir tout les tiers et non le droit de managemente des financements

## [1.0.0]

Initial version
