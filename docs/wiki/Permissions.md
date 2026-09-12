# Permissions

Le module déclare ses propres droits d'accès, basés sur l'identifiant `numero = 183004`. Les permissions sont testées dans le code via `$user->rights->funding->...`.

> Remarque : la permission « Add » plutôt que « Manage » est utilisée pour le retour en brouillon (depuis la 1.0.8).

## Droits Funding

| ID | Clé de permission | Droit testé | Description |
| --- | --- | --- | --- |
| 183005 | `RightReadFunding` | `$user->rights->funding->read` | Lecture des financements |
| 183006 | `RightCreateUpdateFunding` | `$user->rights->funding->write` | Création / modification des financements |
| 183007 | `RightDeleteFunding` | `$user->rights->funding->delete` | Suppression des financements |
| 183008 | `RightManageFunding` | `$user->rights->funding->manage` | Gestion des financements |
| 183009 | `RightListsFunding` | `$user->rights->funding->lists` | Accès aux listes de financements |

## Droits Coefficient

| ID | Clé de permission | Droit testé | Description |
| --- | --- | --- | --- |
| 183010 | `RightReadCoefficient` | `$user->rights->funding->coefficient->read` | Lecture des coefficients |
| 183011 | `RightCreateUpdateCoefficient` | `$user->rights->funding->coefficient->write` | Création / modification des coefficients |
| 183012 | `RightDeleteCoefficient` | `$user->rights->funding->coefficient->delete` | Suppression des coefficients |

## Droits Retention

| ID | Clé de permission | Droit testé | Description |
| --- | --- | --- | --- |
| 183013 | `RightReadRetention` | `$user->rights->funding->retention->read` | Lecture des retenues de garantie |
| 183014 | `RightCreateUpdateRetention` | `$user->rights->funding->retention->write` | Création / modification des retenues de garantie |
| 183015 | `RightDeleteRetention` | `$user->rights->funding->retention->delete` | Suppression des retenues de garantie |

## Utilisation dans le menu

Les entrées de menu Funding sont conditionnées par :
- `$conf->funding->enabled` (module activé)
- `$user->rights->funding->read` (lecture) pour la plupart des listes
- `$user->rights->funding->coefficient->read` pour la liste des coefficients
- `$user->rights->funding->retention->read` pour la liste des retenues de garantie

## Sécurité

- Les utilisateurs externes ne voient rien (sécurité ajoutée en 1.0.4).
- La liste complète des financements est visible si l'utilisateur peut voir tous les tiers, même sans le droit de gestion des financements (1.0.1).
