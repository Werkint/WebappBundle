WebappBundle
===============

## Bundle provides better experience when bulding applications with Symfony2

Scripts = js/scss files

Briefly, it allows:
* Autoloading of Scripts, based on the template name (with "template.twig" files "template.js" and "template.scss" are loaded).
* Passing of variables directly to Scripts (yes, to SCSS also). Even arrays and objects are supported.
* Processing of Scripts: they are minimized and merged into single files. Caching helps to avoid high load.
* Clever browser cache: you can use a tag to differentiate cached files (for example, part of GIT hash). While assetic force you to set this tag manually, Webapp handle it automtically.
* Beauty of SCSS mixins: the files are merged before processing, so you can define variables, mixins, etc somethere and use later.
* Repository of Scripts: you can create packages of Scripts, defining dependencies. They are automatically updated on composer.phar update.

### Configuring the bundle

Add bundle to composer dependencies, then add this configuration:

```yaml
werkint_webapp:
    respath:  /res/cached # public path for cached Scripts
    resdir:   %kernel.root_dir%/cache/scripts # directory for cached Scripts
    revpath:  %kernel.root_dir%/cache/revision # tag file (for browser cache)
    scripts:  %kernel.root_dir%/scripts # directory with downloaded packages
```

### Configuring cached  files tagging

For automatic tagging there should be a file with current repository tag. It is convenient to change this file in a git hook, and store there commit hash (symlink or source this file in .git/hooks):
```bash
#!/bin/bash
DIR_CACHE=app/cache
HASH=$(git rev-parse HEAD)
REVISION_FILE="$DIR_CACHE/revision"
```

Path to tag file is stored in "werkint_webapp -> revpath" config parameter.

### Connecting to Scripts repository

Repository is located in http://werkint.com/webapp-scripts/packages. There are files ".packages" with a list of available Scripts and ".hashes" with package hashes. Each package is located in "{url}/packages/{package_name}". List of files is stored in "{package_url}/.package.ini", file hashes in "{package_url}/.hashes".

".package.ini" structure is simple:
```ini
[files]
deps  = jquery # dependencies
files = file1.js,file1.scss # Scripts
res   = img1.gif # resources - files,
                 # that are symlinked directly in the public path
```

If there is a resource-directory, it is zipped and downloaded as archive, then unzipped in local directory. For example, we have "tiny_mce" resource - directory with files, that will be available as "/res/cached/tiny_mce/file.." and should not be processed (tinymce connect them in runtime). It is stored as tiny_mce.zip in repository, and unpacked after download. When Webapp in composer hook updates Scripts, it only checks archive hash, so time is saved.

Firstly, create a directory for downloaded scripts, for example "app/scripts". Then set path to this directory and update hooks in root composer.json:
```javascript
{
    ...
    "scripts":           {
        "post-install-cmd": [
            ...
            "Werkint\\Bundle\\WebappBundle\\Webapp\\ComposerScriptHandler::updateScripts"
        ],
        "post-update-cmd":  [
            ...
            "Werkint\\Bundle\\WebappBundle\\Webapp\\ComposerScriptHandler::updateScripts"
        ]
    },
    "extra":             {
        ...
        "werkint-webapp-scripts": "app/scripts"
    }
}
```

Now, every time you invoke compsoser update, Scrips are updated;

Note, that library does not support IE<9, Firefox<4, Opera<12.