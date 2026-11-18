<?php
declare(strict_types=1);

namespace App\View\Helper;

use Cake\View\Helper\FormHelper as CakeFormHelper;

/**
 * Ajoute l'option « help » aux champs : un texte d'aide affiché sous le champ
 * et relié à celui-ci par aria-describedby, pour les lecteurs d'écran.
 */
class FormHelper extends CakeFormHelper
{
    /**
     * Champ complet (libellé, saisie, aide, erreur).
     *
     * @param string $fieldName Nom du champ.
     * @param array<string, mixed> $options Options du champ, dont « help ».
     * @return string
     */
    public function control(string $fieldName, array $options = []): string
    {
        $help = $options['help'] ?? null;
        unset($options['help']);
        if (is_string($help) && $help !== '') {
            $base = is_string($options['id'] ?? null) ? $options['id'] : $this->_domId($fieldName);
            $helpId = $base . '-help';
            $options['templateVars']['help'] = sprintf('<span class="help" id="%s">%s</span>', h($helpId), h($help));
            $describedBy = [$helpId];
            if ($this->isFieldError($fieldName)) {
                $describedBy[] = $base . '-error';
            }
            $options['aria-describedby'] = implode(' ', $describedBy);
        }

        return parent::control($fieldName, $options);
    }
}
