# Tâches planifiées

Le module enregistre deux tâches planifiées (cron jobs) lors de son activation. Elles sont gérées depuis **Configuration → Tâches planifiées** et exécutées par la classe `Funding` (`class/funding.class.php`).

| Libellé | Type | Classe | Méthode | Fréquence | Unité | Statut par défaut |
| --- | --- | --- | --- | --- | --- | --- |
| `CronFundingEnd` | method | `/funding/class/funding.class.php` → `Funding` | `cronFundingEnd` | 1 | jour (86400 s) | désactivé |
| `CronFundingSoonFinished` | method | `/funding/class/funding.class.php` → `Funding` | `cronFundingSoonFinished` | 4 | semaine (604800 s) | désactivé |

## `cronFundingEnd`

Clôture automatiquement les financements arrivés à terme.

- Fréquence : 1 jour.
- Gère le paramètre `FUNDING_NOCLOSEDFINISHAUTO_EXTENSION` pour ne pas clôturer automatiquement les financements en extension.
- La fonction `setRun` est appelée pour conserver le même comportement que le bouton « actif » (correctif 1.1.1).

## `cronFundingSoonFinished`

Envoie un mail récapitulatif de tous les financements bientôt à terme.

- Fréquence : 4 semaines.
- Le mail garde également les financements passés mais toujours actifs.
- Ajoute les statuts dans le rapport.
- Utilise le modèle d'e-mail configuré dans `FUNDING_MAIL_REPORT`.

> Les libellés et commentaires des tâches sont traduits (fichiers `langs/fr_FR/funding.lang` et `langs/en_US/funding.lang`). L'anglais est pris par défaut pour les tâches planifiées (correctif 1.1.3).
