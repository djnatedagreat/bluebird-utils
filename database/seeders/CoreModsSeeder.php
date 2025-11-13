<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CoreModsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
      $core_mod_data = [
        ['type' => 'core','path' => 'civicrm/core/ang/crmUi.js', 'patch_file' => 'civicrm/patches/core/ang/crmUi.js.patch', 'active' => 1],
        ['type' => 'core', 'path' => 'civicrm/core/Civi/Api4/Service/Spec/Provider/DAOFieldsCallbackAdapterSpecProvider.php', 'patch_file' => NULL, 'active' => 0],
        ['type' => 'core', 'path' => 'civicrm/core/CRM/Core/Permission/Drupal.php', 'patch_file' => 'civicrm/patches/core/CRM/Core/Permission/Drupal.php.patch', 'active' => 1],
        ['type' => 'core', 'path' => 'civicrm/core/CRM/Mailing/BAO/MailingRecipients.php', 'patch_file' => NULL, 'active' => 0],
        ['type' => 'core', 'path' => 'civicrm/core/ext/civi_mail/ang/crmMailing/services.js', 'patch_file' => 'civicrm/patches/core/ext/civi_mail/ang/crmMailing/services.js.patch', 'active' => 1],
        ['type' => 'core', 'path' => 'civicrm/core/ext/civi_mail/ang/crmMailing/crmMailingRecipientsAutocomplete.component.js', 'patch_file' => 'civicrm/patches/core/ext/civi_mail/ang/crmMailing/crmMailingRecipientsAutocomplete.component.js.patch', 'active' => 1],
        ['type' => 'core', 'path' => 'civicrm/core/ext/flexmailer/src/Listener/DefaultSender.php', 'patch_file' => 'civicrm/patches/core/ext/flexmailer/src/Listener/DefaultSender.php.patch', 'active' => 1],
        ['type' => 'core', 'path' => 'civicrm/core/js/crm.menubar.js', 'patch_file' => 'civicrm/patches/core/js/crm.menubar.js.patch', 'active' => 1],
        ['type' => 'custom', 'path' => 'civicrm/custom/ext/uk.co.vedaconsulting.mosaico/mosaico.php', 'patch_file' => NULL, 'active' => 1],
        ['type' => 'custom', 'path' => 'civicrm/custom/ext/uk.co.vedaconsulting.mosaico/ang/crmMosaico/BlockMailing.html', 'patch_file' => NULL, 'active' => 1],
        ['type' => 'custom', 'path' => 'civicrm/custom/ext/uk.co.vedaconsulting.mosaico/ang/crmMosaico/BlockDesign.html', 'patch_file' => NULL, 'active' => 1],
        ['type' => 'custom', 'path' => 'civicrm/custom/ext/uk.co.vedaconsulting.mosaico/ang/crmMosaico/EditMailingCtrl/bootstrap-wizard.html', 'patch_file' => NULL, 'active' => 1],
        ['type' => 'custom', 'path' => 'civicrm/custom/ext/uk.co.vedaconsulting.mosaico/ang/crmMosaico/SearchTemplateListButtons.html', 'patch_file' => NULL, 'active' => 1],
        ['type' => 'custom', 'path' => 'civicrm/custom/ext/uk.co.vedaconsulting.mosaico/ang/crmbWizard.js', 'patch_file' => NULL, 'active' => 1],
        ['type' => 'custom', 'path' => 'civicrm/custom/ext/uk.co.vedaconsulting.mosaico/Civi/Api4/MosiacoTemplate.php', 'patch_file' => NULL, 'active' => 1],
        ['type' => 'custom', 'path' => 'civicrm/custom/ext/uk.co.vedaconsulting.mosaico/packages/mosaico/dist/rs/mosaico.min.js', 'patch_file' => NULL, 'active' => 1],
        ['type' => 'custom', 'path' => 'civicrm/custom/ext/uk.co.vedaconsulting.mosaico/templates/CRM/Mosaico/Page/EditorIframe.tpl', 'patch_file' => NULL, 'active' => 1],
        ['type' => 'custom', 'path' => 'civicrm/custom/ext/uk.co.vedaconsulting.mosaico/vendor/intervention/image/src/Intervention/Image/Gd/Decoder.php', 'patch_file' => NULL, 'active' => 1],
      ];

      DB::table('core_mods')->insert($core_mod_data);
    }
}
