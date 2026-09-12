# Génération de documents

Le module fournit ses propres modèles de génération de documents (déclarés via `'models' => 1`). Ils se trouvent dans `core/modules/funding/`.

## Modèles disponibles

### Funding

| Fichier | Type | Description |
| --- | --- | --- |
| `pdf_standard_funding.modules.php` | PDF | Modèle PDF standard du financement |
| `doc_generic_funding_odt.modules.php` | ODT | Modèle ODT générique du financement |

### Coefficient

| Fichier | Type | Description |
| --- | --- | --- |
| `pdf_standard_coefficient.modules.php` | PDF | Modèle PDF standard du coefficient |
| `doc_generic_coefficient_odt.modules.php` | ODT | Modèle ODT générique du coefficient |

## Sous-modules de numérotation

| Objet | Standard | Avancé |
| --- | --- | --- |
| Funding | `mod_funding_standard.php` | `mod_funding_advanced.php` |
| Coefficient | `mod_coefficient_standard.php` | `mod_coefficient_advanced.php` |
| Retention | `mod_retention_standard.php` | `mod_retention_advanced.php` |

## Sélection du modèle

- Le modèle PDF utilisé est stocké dans le champ `model_pdf` du financement.
- Le dernier document généré est référencé dans `last_main_doc`.
- Les modèles sont activables depuis la configuration du module / de l'objet.

## Modèles ODT

Pour les modèles ODT, un template peut être copié automatiquement lors de l'activation :
- Source : `DOL_DOCUMENT_ROOT/install/doctemplates/funding/template_fundings.odt`
- Destination : `DOL_DATA_ROOT/doctemplates/funding/template_fundings.odt`

> Dans la version actuelle, la génération de référence et de document est désactivée dans l'`init()` (`includerefgeneration=0`, `includedocgeneration=0`).

## Gestion des fichiers attachés

- Possibilité d'ajouter des images en pièces jointes ; elles sont transformées en PDF automatiquement (depuis 1.0.2).
- Fonction `sendDocumentFunding()` dédiée à la gestion des fichiers envoyés (meilleure gestion, depuis 1.0.5).
- Permissions dédiées pour la suppression de documents (correctif 1.0.4).
- Enregistrement d'une pièce demandée : plus de message d'erreur s'il n'y a pas de fichier (correctif 1.0.4).
