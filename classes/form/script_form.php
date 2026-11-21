<?php
// This file is part of Moodle - https://moodle.org/
/**
 * Trusted managed JavaScript editor.
 *
 * @package   local_scriptmanager
 * @copyright 2026 Andreas Giesen <andreas@108design.com>
 * @license   108design source-available software license; see LICENSE.md
 */

// This file is part of a 108design source-available software product.
//
// Copyright (C) 2026 Andreas Giesen <andreas@108design.com>.
//
// Use and modification are permitted only under the Software License included
// with this distribution. See LICENSE.md for the full terms.
namespace local_scriptmanager\form;

/**
 * Trusted managed JavaScript editor.
 */
final class script_form extends \moodleform {
    /**
     * Define the native form fields and their defaults.
     *
     * @return void
     */
    protected function definition(): void {
        $mform = $this->_form;
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement(
            'text',
            'name',
            get_string('scriptname', 'local_scriptmanager'),
            [ 'maxlength' => 255, 'size' => 60 ]
        );
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addElement(
            'text',
            'identifier',
            get_string('scriptidentifier', 'local_scriptmanager'),
            [ 'maxlength' => 100 ]
        );
        $mform->setType('identifier', PARAM_ALPHANUMEXT);
        $mform->addRule('identifier', null, 'required', null, 'client');

        $mform->addElement('textarea', 'description', get_string('description'), ['rows' => 3]);
        $mform->setType('description', PARAM_TEXT);
        $mform->addElement('advcheckbox', 'enabled', get_string('scriptenabled', 'local_scriptmanager'));
        $mform->addElement('advcheckbox', 'allowfrontpage', get_string('allowfrontpage', 'local_scriptmanager'));
        $mform->setDefault('allowfrontpage', 0);
        $mform->addHelpButton('allowfrontpage', 'allowfrontpage', 'local_scriptmanager');

        $mform->addElement('static', 'fixedprofile', '', get_string('fixedprofile', 'local_scriptmanager'));
        $mform->addElement('static', 'securitynotice', '', get_string('scriptsecuritynotice', 'local_scriptmanager'));
        $mform->addElement(
            'textarea',
            'draftcode',
            get_string('scriptcode', 'local_scriptmanager'),
            ['rows' => 28, 'spellcheck' => 'false', 'wrap' => 'off']
        );
        $mform->setType('draftcode', PARAM_RAW);
        $mform->addRule('draftcode', null, 'required', null, 'client');

        $mform->addElement(
            'textarea',
            'draftrevisionnote',
            get_string('scriptrevisionnote', 'local_scriptmanager'),
            ['rows' => 3, 'maxlength' => 1000]
        );
        $mform->setType('draftrevisionnote', PARAM_TEXT);

        foreach (['name' => 'scriptname', 'description' => 'scriptdescription'] as $field => $key) {
            $mform->addHelpButton($field, $key, 'local_scriptmanager');
        }
        foreach (
            [
            'identifier' => 'scriptidentity',
            'enabled' => 'scriptpublication',
            'draftcode' => 'scriptexecution',
            'draftrevisionnote' => 'scriptrevisions',
            ] as $field => $topic
        ) {
            $element = $mform->getElement($field);
            $element->setLabel(
                $element->getLabel() . ' '
                . \local_scriptmanager\output\context_help::icon($topic, 'scripts')
            );
        }
        $publishlabel = !empty($this->_customdata['restoredversion'])
            ? get_string('publishrestoredrevision', 'local_scriptmanager')
            : get_string('saveandpublishscript', 'local_scriptmanager');
        $buttons = [
            $mform->createElement('submit', 'savedraft', get_string('savescriptdraft', 'local_scriptmanager')),
            $mform->createElement('submit', 'savepublish', $publishlabel, ['class' => 'btn-primary']),
            $mform->createElement('cancel', 'cancel', get_string('cancel')),
        ];
        $mform->addGroup($buttons, 'buttonar', '', [' '], false);
    }
    /**
     * Validate submitted values and return errors keyed by field name.
     *
     * @param mixed $data Submitted or stored data.
     * @param mixed $files Submitted file data.
     * @return array
     */
    public function validation($data, $files): array {
        $errors = parent::validation($data, $files);
        if (strlen((string) ($data['draftcode'] ?? '')) > 500000) {
            $errors['draftcode'] = get_string('scripttoolarge', 'local_scriptmanager');
        }
        return $errors;
    }
}
