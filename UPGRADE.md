<!-- AI generated -->
# Drupal 10.3 to 11.3 Upgrade Guide

This document provides step-by-step instructions for upgrading a Drupal site from version 10.3.0 to 11.3.3. These steps were successfully applied to upgrade this site and can be reused for similar Drupal projects.

## Prerequisites

Before starting the upgrade process, ensure you have:

- **Backup**: Complete database and files backup
- **Development Environment**: DDEV or similar local development environment
- **PHP Version**: PHP 8.3 or higher (required for Drupal 11)
- **Composer**: Latest version of Composer installed
- **Drush**: Drush 13 or higher
- **Database Access**: Ability to export/import databases
- **Git**: Version control for tracking changes

## Phase 1: Update Drupal Core and Modules (10.3.0 → 10.6.3)

Before upgrading to Drupal 11, first update to the latest Drupal 10 version.

### Steps

1. **Update all packages to latest D10 versions**
   ```bash
   composer update
   ```

2. **Import your database backup** (if working with production data)
   ```bash
   ddev import-db --file=db/your-backup.sql
   ```

3. **Run database updates**
   ```bash
   ddev drush updb -y
   ```

4. **Clear caches**
   ```bash
   ddev drush cr
   ```

5. **Verify the site is working**
   - Check the site in browser
   - Run `ddev drush status` to verify Drupal version is now 10.6.x

**Expected Result**: Drupal core updated from 10.3.0 to 10.6.3 with all contrib modules at their latest D10-compatible versions.

## Phase 2: Pre-Upgrade Checks for Drupal 11

### Steps

1. **Check Drupal 11 compatibility**
   ```bash
   ddev drush upgrade_status:analyze
   ```

   Review the output for:
   - Incompatible contrib modules
   - Custom code that needs updates
   - Deprecated API usage

2. **Update PHP version to 8.3**

   Edit `.ddev/config.yaml`:
   ```yaml
   php_version: "8.3"
   ```

   Then restart DDEV:
   ```bash
   ddev restart
   ```

## Phase 3: Remove Incompatible Modules

Before upgrading to Drupal 11, remove modules that are incompatible or no longer needed.

### Modules to Remove

1. **CKEditor (drupal/ckeditor)**
   - Drupal 11 uses CKEditor 5 from core
   - The old CKEditor 4 module is not compatible
   ```bash
   composer remove drupal/ckeditor
   ```

2. **Color module (drupal/color)**
   - Removed from Drupal 11 core
   ```bash
   composer remove drupal/color
   ```

3. **Config Ignore (drupal/config_ignore)**
   - If incompatible version installed
   ```bash
   composer remove drupal/config_ignore
   ```

4. **Stage File Proxy (drupal/stage_file_proxy)**
   - If using dev version, remove and reinstall stable version after upgrade
   ```bash
   composer remove drupal/stage_file_proxy
   ```

## Phase 4: Update Custom Code for Drupal 11

### Update core_version_requirement in Custom Modules and Themes

For each custom module and theme, update the `.info.yml` file:

**Before:**
```yaml
core_version_requirement: ^9 || ^10
```

**After:**
```yaml
core_version_requirement: ^9 || ^10 || ^11
```

**Files to update:**
- `web/modules/custom/[module_name]/[module_name].info.yml`
- `web/themes/custom/[theme_name]/[theme_name].info.yml`

### Fix PHP 8.3 Deprecations

1. **Optional parameter before required parameter**

   If you have code like this in `NextPrevious.php` or similar files:

   **Before:**
   ```php
   public function __construct($configuration = [], $plugin_id, $plugin_definition) {
   ```

   **After:**
   ```php
   public function __construct(array $configuration, $plugin_id, $plugin_definition) {
   ```

2. **Remove deprecated assertion handling from settings.local.php**

   **Remove these lines:**
   ```php
   assert_options(ASSERT_ACTIVE, TRUE);
   \Drupal\Component\Assertion\Handle::register();
   ```

   **Replace with (if needed):**
   ```php
   assert_options(ASSERT_ACTIVE, TRUE);
   // Assertion handling is now automatic in Drupal 11
   ```

## Phase 5: Perform the Drupal 11 Upgrade

### Steps

1. **Update composer.json to allow Drupal 11**

   Edit `composer.json` and change the drupal/core constraint:

   **Before:**
   ```json
   "drupal/core-composer-scaffold": "^10",
   "drupal/core-project-message": "^10",
   "drupal/core-recommended": "^10",
   ```

   **After:**
   ```json
   "drupal/core-composer-scaffold": "^11",
   "drupal/core-project-message": "^11",
   "drupal/core-recommended": "^11",
   ```

2. **Run composer update**
   ```bash
   composer update
   ```

   This will update Drupal core to 11.3.3 and all compatible contrib modules.

3. **Run database updates**
   ```bash
   ddev drush updb -y
   ```

4. **Clear caches**
   ```bash
   ddev drush cr
   ```

5. **Update DDEV configuration**

   Edit `.ddev/config.yaml`:

   **Before:**
   ```yaml
   type: drupal9
   ```

   **After:**
   ```yaml
   type: drupal11
   ```

   Then restart:
   ```bash
   ddev restart
   ```

## Phase 6: Post-Upgrade Fixes

### Fix Deprecated Plugin Issues

1. **Remove blocks using deprecated plugins**

   If you encounter errors like:
   ```
   PluginNotFoundException: The "node_type" plugin does not exist
   ```

   Delete the problematic block configuration:
   ```bash
   ddev drush config:delete block.block.nextprevious
   ```

   **Note**: The `node_type` condition plugin was replaced by `entity_bundle:node` in Drupal 11. You'll need to recreate any affected blocks manually or update their configuration.

### Install Removed Core Themes

Stable and Classy themes were removed from Drupal 11 core. If your site or contrib modules depend on them:

```bash
ddev composer require drupal/stable drupal/classy
```

### Fix NULL Title Deprecations

If you encounter deprecation warnings about `strlen(null)`:

1. **Update routing files** (e.g., `cri_tools.routing.yml`)

   **Before:**
   ```yaml
   defaults:
     _title: NULL
   ```

   **After:**
   ```yaml
   defaults:
     _title: 'Home'
   ```

2. **Hide the title with CSS** (if needed for front page)

   Add to your theme's CSS file (e.g., `screen.css`):
   ```css
   .path-frontpage .page-title {
     clip: rect(1px, 1px, 1px, 1px);
     height: 1px;
     overflow: hidden;
     position: absolute;
     width: 1px;
     word-wrap: normal;
   }
   ```

## Phase 7: Database Server Migration (Optional)

If you need to match your production database server version:

### Migrate from MariaDB to MySQL

1. **Export current database**
   ```bash
   ddev export-db --file=db/pre-migration-backup.sql.gz
   ```

2. **Update DDEV database configuration**

   Edit `.ddev/config.yaml`:

   **Before:**
   ```yaml
   database:
     type: mariadb
     version: "10.6"
   ```

   **After:**
   ```yaml
   database:
     type: mysql
     version: "8.0"
   ```

3. **Migrate the database**
   ```bash
   ddev debug migrate-database mysql:8.0
   ```

4. **Verify the migration**
   ```bash
   ddev drush status
   ddev describe
   ```

## Troubleshooting

### Common Issues and Solutions

#### Issue: "PluginNotFoundException: The 'node_type' plugin does not exist"

**Cause**: Drupal 11 replaced the `node_type` condition plugin with `entity_bundle:node`.

**Solution**:
- Delete affected block configurations: `ddev drush config:delete block.block.[block_id]`
- Recreate blocks using the new plugin system

#### Issue: "strlen(): Passing null to parameter"

**Cause**: PHP 8.3 stricter type checking with NULL values.

**Solution**:
- Replace `_title: NULL` with an actual string value in routing files
- Use CSS to visually hide titles if needed

#### Issue: Missing themes (Stable, Classy)

**Cause**: These themes were removed from Drupal 11 core.

**Solution**:
```bash
ddev composer require drupal/stable drupal/classy
```

#### Issue: CKEditor not working

**Cause**: Drupal 11 uses CKEditor 5, not CKEditor 4.

**Solution**:
- Remove old CKEditor module
- Configure CKEditor 5 through core (no additional module needed)
- Migrate text formats to CKEditor 5 configuration

#### Issue: Module compatibility errors

**Cause**: Some contrib modules may not yet support Drupal 11.

**Solution**:
- Check drupal.org for D11-compatible versions
- Use `composer require drupal/[module]:^3.0` (adjust version as needed)
- Consider alternatives if modules are abandoned
- Check for patches in module issue queues

#### Issue: PHP version errors

**Cause**: Drupal 11 requires PHP 8.3 or higher.

**Solution**:
- Update `.ddev/config.yaml` to use `php_version: "8.3"`
- Run `ddev restart`

## Verification Checklist

After completing all upgrade steps, verify:

- [ ] Site homepage loads without errors
- [ ] `ddev drush status` shows Drupal 11.3.3
- [ ] No PHP deprecation warnings in logs
- [ ] Content types and fields display correctly
- [ ] User login and authentication works
- [ ] Administrative interface is accessible
- [ ] Views and custom modules function properly
- [ ] Theme displays correctly
- [ ] Forms submit successfully
- [ ] File uploads work
- [ ] Media handling functions
- [ ] Search functionality works (if applicable)
- [ ] Multilingual features work (if applicable)

## Final Steps

1. **Review watchdog logs**
   ```bash
   ddev drush watchdog:show --count=50
   ```

2. **Check for remaining deprecations**
   ```bash
   ddev drush upgrade_status:analyze
   ```

3. **Run tests** (if available)
   ```bash
   ddev phpunit web/modules/custom
   ```

4. **Commit your changes**
   ```bash
   git add .
   git commit -m "Upgrade Drupal from 10.3.0 to 11.3.3"
   ```

5. **Deploy to staging environment**
   - Test thoroughly before production deployment
   - Monitor logs for any issues
   - Have rollback plan ready

## Additional Resources

- [Drupal 11 Release Notes](https://www.drupal.org/about/11)
- [Upgrade Status Module](https://www.drupal.org/project/upgrade_status)
- [Drupal 11 Deprecated API](https://www.drupal.org/node/3261520)
- [PHP 8.3 Migration Guide](https://www.php.net/manual/en/migration83.php)
- [CKEditor 5 Migration](https://www.drupal.org/docs/core-modules-and-themes/core-modules/ckeditor-5-module/ckeditor-5-migration)

## Notes

- **Timeline**: Allow 2-4 hours for a typical site upgrade, more for complex custom code
- **Testing**: Always test in a development environment first
- **Backup**: Keep database and files backups at each major step
- **Documentation**: Document any custom changes specific to your site
- **Support**: Check module issue queues on drupal.org for known D11 issues
- **Performance**: Clear all caches after each major step
- **Configuration**: Export configuration after successful upgrade for version control

## Revision History

- **2026-02-08**: Initial documentation of Drupal 10.3 → 11.3 upgrade process
