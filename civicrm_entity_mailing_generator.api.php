<?php

/**
 * @file
 * Api information provided by CiviCRM Entity Mailing Generator module
 */

/**
 * @mainpage
 * API Reference Documentation for the CiviCRM Entity Mailing Generator module.
 *
 */


/**
 * Hook add mailing generator recipient builder that are recognized and integrated by CiviCRM Entity Mailing Generaator
 * The API supports the following keys
 *
 * -label: human readable label of mailing generator
 * -generator callback: callback function name for preparing recipient contact_id and email_id, related_contact_id
 *
 */
function hook_civicrm_entity_mailing_generator_info() {
  return [
    'MYMODULE_mailing_generator' => [
      'label' => 'A Mailing Generator',
      'generator callback' => 'MYMODULE_mailing_generator_callback',
    ],
  ];
}

/**
 * Example mailing generator callback
 *
 * We use this function to figure out who should receive the email, and a related contact to pull token data from
 *
 * The module is expecting an array like so
 * [
 *   'recipient' => [
 *     'contact_id' => $contact_id
 *     'email_id' => $email_id
 *   ]
 *   'related_contact' => $related_contact_id
 * ]
 * @param $entity
 * @param $context
 *
 * @return array $mailing_params
 *
 * @see hook_civicrm_entity_mailing_generator_info()
 */
function MYMODULE_mailing_generator_callback($entity, $context) {
  $email_id = 0;
  $contact_id = 0;
  // this is values from a form element placed on form via hook_views_bulk_operations_form_alter().
  // In this case, we have contact reference custom fields on organization contact, and the form element allows admins to choose which one should receive the email
  // from a list of the organization contacts
  // starting from the contact entity returned by the VBO for the Views row
  // you can do some funky stuff with tokens, by looking up the record in the civicrm_entity_mailing_generator_mailing_jobs table
  // the mailing_job_id will be the parent id of the $job variable in hook_civicrm_tokenValues().
  if (!empty($context['form_values']['recipient_type'])) {
    if (!empty($entity->{$context['form_values']['recipient_type']})) {
      $contact_id = $entity->{$context['form_values']['recipient_type']};
      try {
        $result = civicrm_api3('Email', 'get', array(
          'sequential' => 1,
          'contact_id' => $contact_id,
          'is_primary' => 1,
        ));
        if (!empty($result['values'][0]['id'])) {
          $email_id = $result['values'][0]['id'];
        }
      }
      catch (Exception $e) {
        watchdog('MYMODULE', $e->getMessage());
      }
    }
  }

  $mailing_params = [];
  if (!empty($contact_id) && !empty($email_id)) {
    $mailing_params['recipient'] = ['contact_id' => $contact_id, 'email_id' => $email_id];
  }
  // set the related contact to the id of the contact returned by the View
  $mailing_params['related_contact'] = $entity->id;
  return $mailing_params;
}


/**
 * Hook to alter CiviCRM Entity Mailing Generator handlers
 *
 * @param $info
 */
function hook_civicrm_entity_mailing_generator_info_alter(&$info) {
  $info['MYMODULE_mailing_generator']['label'] = 'Some other label';
}