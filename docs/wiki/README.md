# Pages du wiki — Module Funding

Ce dossier `docs/wiki/` contient l'ensemble des pages de documentation destinées au **wiki GitHub** du dépôt `BB2A/dolibarr_modules_funding`.

## Contenu

| Fichier | Page wiki |
| --- | --- |
| `Home.md` | Home (page d'accueil) |
| `Présentation.md` | Présentation |
| `Installation.md` | Installation |
| `Configuration.md` | Configuration |
| `Objet-Funding.md` | Objet Funding |
| `Coefficients.md` | Coefficients |
| `Retenue-de-garantie.md` | Retenue de garantie |
| `Permissions.md` | Permissions |
| `Tâches-planifiées.md` | Tâches planifiées |
| `API-REST.md` | API REST |
| `Schéma-de-base-de-données.md` | Schéma de base de données |
| `Déclencheurs-et-hooks.md` | Déclencheurs et hooks |
| `Génération-de-documents.md` | Génération de documents |
| `Changelog.md` | Changelog |

## Comment publier ces pages dans le wiki GitHub

> GitHub ne fournit pas d'API REST/GraphQL pour créer des pages de wiki, et les wikis doivent être initialisés une première fois via l'interface web. Les tokens d'application GitHub ne peuvent pas écrire dans le wiki via git.

1. **Initialiser le wiki** (une seule fois) :
   - Ouvrez https://github.com/BB2A/dolibarr_modules_funding/wiki
   - Cliquez sur **Create the first page** et créez la page `Home` (contenu quelconque, modifiable ensuite).

2. **Cloner le wiki** localement :
   ```bash
   git clone https://github.com/BB2A/dolibarr_modules_funding.wiki.git funding.wiki
   cd funding.wiki
   ```

3. **Copier les pages** depuis ce dossier vers le wiki :
   ```bash
   cp ../dolibarr_modules_funding/docs/wiki/*.md .
   ```
   - `Home.md` remplace `Home.md` existant.
   - Les noms de fichiers deviennent les titres des pages (ex. `Objet-Funding.md` → page « Objet Funding »).

4. **Commit & push** :
   ```bash
   git add -A
   git commit -m "Documentation du module Funding"
   git push
   ```

5. Consulter le wiki : https://github.com/BB2A/dolibarr_modules_funding/wiki

## Notes

- Les liens internes dans `Home.md` utilisent la syntaxe wiki `[Titre](Titre-de-la-page)`.
- Pour les pages dont le titre contient des accents ou espaces, GitHub convertit les espaces en tirets et conserve les accents dans les noms de fichiers du wiki.
- Cette documentation est générée à partir de l'analyse du code source (descripteur `modFunding`, classes, API, SQL, ChangeLog).
