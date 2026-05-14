# doesdesign_import

Migrates content from the doesdesign Drupal 7 site to Drupal 11.

## Available migrations

| Migration    | Description                                      |
|--------------|--------------------------------------------------|
| user         | User accounts with roles                         |
| file         | Files in use on the D7 site                      |
| media_image  | Media image entities from D7 image fields        |
| term         | Taxonomy terms (material and type vocabularies)   |
| article      | Article nodes (D7 story content type)            |
| page         | Page nodes                                       |
| object       | Object nodes (jewelry items)                     |
| menu_link    | Main menu links                                  |
| url_alias    | URL aliases (path aliases)                       |

## Execution order

Migrations should be run in dependency order. The recommended sequence is:

1. `user`
2. `file`
3. `media_image`
4. `term`
5. `article`, `page`, `object` (nodes, can run in parallel)
6. `menu_link`
7. `url_alias`

## Usage

Import all migrations:

```bash
ddev drush migrate:import --tag=doesdesign
```

Import a single migration:

```bash
ddev drush migrate:import url_alias
```

Check migration status:

```bash
ddev drush migrate:status --tag=doesdesign
```
