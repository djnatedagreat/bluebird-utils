# Documentation

## Purpose
Bluebird CRM is built on top of CiviCRM Core. The structure of the Bluebird CRM codebase is such that CiviCRM Core is 
contained within it. Bluebird has overridden some core files and has directly modified some core files. Therefore, the 
Civi core upgrade needs to account for these customizations. During every Civi Core upgrade an audit needs to be done.
Changes to core need to be merged into any overriden files and any core modifications need to be reincoporated into the 
Bluebird CRM codebase. This utility aims to facilitate that audit by handling some of the audit automatically and
reporting when manual intervention is necessary.

## How to do a CiviCRM Core Upgrade

### Step 1. Take a Backup

1. Backup Database and Filesystem

### Step 2. Make Sure that the list of Core Modifications is up to date

```$ ./bb civi:mod:list```

will show a list of currently registered Core Modifications. **The authoritative source for Core Mods is https://dev.nysenate.gov/projects/bluebird/wiki/Core_Modifications**

```$ ./bb civi:mod:add```

To add a new core modification

```$ ./bb civi:mod:remove```

To remove one.

### Step 3. Get the New Code

1. Download the latest CiviCRM codebase.
2. Backup `modules/civicrm/settings_location.php` in the Bluebird codebase
3. Replace the `modules/civicrm` directory with the new codebase
4. Replace `module/civicrm/settings_location.php` with the file that you backed up.
5. This is probably a good time to create a git commit in the Bluebird CRM repository.

### Step 4. Create a Core Upgrade

`$ ./bb civi:up:add --current "Bluebird 3.10"  bb310 6.4.1 6.9.0 /home/nate/workspace/Bluebird-310/`

Where
* Bluebird 3.10 is the name of the upgrade
* bb310 is a unique alphanumeric identifier
* 6.4.1 is the version of CiviCRM Core that we're upgrading from
* 6.9.0 is the version of CiviCRM Core that we're upgrading to
* /home/nate/workspace/Bluebird-310/ is the path to the Bluebird codebase that we're upgrading

This will do some analysis and determine what core and override files can be considered "safe" and what files require some 
attention.

#### Safe

Generally, files are considered "safe" when they haven't changed since our last civi core upgrade. 
Because they haven't changed, we can assume that an override file does not need to be reviewed.

#### Attention

However, if a core file has been changed since our last upgrade, then we need to update our override
file and incorporate those changes.

Note that Core Modifications always require attention. Core Modifications get removed when we replace the core codebase.
So, we always have to re-implement those customizations.

### Step 5. Patch Core File Mods and Reconcile Override Files

```$ ./bb civi:up:status```

`civi:up:status` lists the status of all files that either (1) require a core mod or (2) are a custom override.
To limit the output to only the files requiring attention...

```$ ./bb civi:up:status --attention```

> **The goal is to work through this list of files needing attention reconciling and updating as needed. As each file
> gets reconciled, it gets marked as "completed".**

#### Resolving Modified Core Files
As noted above, all core file mods will need to be re-implemented. There are patch files stored in `civicrm/patches`.
You can see if those patches can still be cleanly applied with the command...

```$ ./bb civi:up:patch [--check]```

This will attempt tp apply the patch for every core file that has a registered patch.
When possible, it will apply the patch.

##### When a patch fails...

You'll need to manually edit the file and add the custom code. You'll also want to create a new patch
that considers the latest updates to that file.

#### Reconciling Override Files

There are a few commands that help with this, and each developer is free to figure the process that works best for them.
The intention is that when an override file is fully reconciled and doesn't require further testing, that it is
marked as "complete". This will remove it from the `civi:up:status --attention` list.

Here's my personal workflow:

1. Find the next override file to reconcile with:

```$ ./bb civi:up:status --attention```

2. See what's been changed in Core with:

```$ ./bb civi:up:diff --custom [file path]```

3. (Optionally) I take a closer look at the override file to see if there are overrides that can easily be removed.
   Overrides are generally bad practice. So, I try to remove them or move the customizations to extensions when possible.
   I use the diff/compare tool in PHP storm to analyze the differences between the core file and it's override. When
   I find something that can be removed or moved, I generally make a note (create a separate issue) and handle it
   after I've finished the entire reconciliation process.

4. When possible I use the following to automatically apply the differences found in (2) to the override file.

```$ ./bb civi:up:patch --custom [file path]```

When the patch fails to apply, I use PHP Storm's diff utility to manually resolve conflicts and merge changes.

5. Mark the file as complete with:

```$ ./bb civi:up:complete --custom [file path]```


## Built With

Laravel Zero is an open-source software licensed under the MIT license.

------
<p align="center">
    <img title="Laravel Zero" height="100" src="https://raw.githubusercontent.com/laravel-zero/docs/master/images/logo/laravel-zero-readme.png" alt="Laravel Zero Logo" />
</p>

<p align="center">
  <a href="https://github.com/laravel-zero/framework/actions"><img src="https://github.com/laravel-zero/laravel-zero/actions/workflows/tests.yml/badge.svg" alt="Build Status" /></a>
  <a href="https://packagist.org/packages/laravel-zero/framework"><img src="https://img.shields.io/packagist/dt/laravel-zero/framework.svg" alt="Total Downloads" /></a>
  <a href="https://packagist.org/packages/laravel-zero/framework"><img src="https://img.shields.io/packagist/v/laravel-zero/framework.svg?label=stable" alt="Latest Stable Version" /></a>
  <a href="https://packagist.org/packages/laravel-zero/framework"><img src="https://img.shields.io/packagist/l/laravel-zero/framework.svg" alt="License" /></a>
</p>

Laravel Zero was created by [Nuno Maduro](https://github.com/nunomaduro) and [Owen Voke](https://github.com/owenvoke), and is a micro-framework that provides an elegant starting point for your console application. It is an **unofficial** and customized version of Laravel optimized for building command-line applications.

- Built on top of the [Laravel](https://laravel.com) components.
- Optional installation of Laravel [Eloquent](https://laravel-zero.com/docs/database/), Laravel [Logging](https://laravel-zero.com/docs/logging/) and many others.
- Supports interactive [menus](https://laravel-zero.com/docs/build-interactive-menus/) and [desktop notifications](https://laravel-zero.com/docs/send-desktop-notifications/) on Linux, Windows & MacOS.
- Ships with a [Scheduler](https://laravel-zero.com/docs/task-scheduling/) and  a [Standalone Compiler](https://laravel-zero.com/docs/build-a-standalone-application/).
- Integration with [Collision](https://github.com/nunomaduro/collision) - Beautiful error reporting
- Follow the creator Nuno Maduro:
    - YouTube: **[youtube.com/@nunomaduro](https://www.youtube.com/@nunomaduro)** — Videos every weekday
    - Twitch: **[twitch.tv/enunomaduro](https://www.twitch.tv/enunomaduro)** — Streams (almost) every weekday
    - Twitter / X: **[x.com/enunomaduro](https://x.com/enunomaduro)**
    - LinkedIn: **[linkedin.com/in/nunomaduro](https://www.linkedin.com/in/nunomaduro)**
    - Instagram: **[instagram.com/enunomaduro](https://www.instagram.com/enunomaduro)**
    - Tiktok: **[tiktok.com/@enunomaduro](https://www.tiktok.com/@enunomaduro)**


## Support the development
**Do you like this project? Support it by donating**

- PayPal: [Donate](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=66BYDWAT92N6L)
- Patreon: [Donate](https://www.patreon.com/nunomaduro)