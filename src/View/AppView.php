<?php
declare(strict_types=1);

namespace App\View;

use Cake\View\View;

/**
 * @property \App\View\Helper\SleepHelper $Sleep
 */
class AppView extends View
{
    /**
     * Charge le helper de mise en forme et les gabarits de formulaire.
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->addHelper('Sleep');
        // Gabarits de formulaire : message d'erreur relié au champ pour les lecteurs d'écran.
        // Pas d'attributs oninvalid/oninput générés : la politique de sécurité bloque le JavaScript en ligne.
        $this->loadHelper('Form', [
            'autoSetCustomValidity' => false,
            'templates' => [
                'inputContainer' => '<div class="field {{type}}{{required}}">{{content}}{{help}}</div>',
                'inputContainerError' =>
                    '<div class="field {{type}}{{required}} has-error">{{content}}{{help}}{{error}}</div>',
                'error' => '<p class="field-error" id="{{id}}">{{content}}</p>',
                'checkboxContainer' => '<div class="field checkbox{{required}}">{{content}}{{help}}</div>',
            ],
        ]);
    }
}
