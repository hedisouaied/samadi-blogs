<?php

return [
    'column_manager' => [
        'position' => 'Position :position sur :count.',
        'position_in_group' => 'Position :position sur :count dans :group.',
        'reordered' => ':column déplacé à la position :position sur :count.',
        'reordered_deferred' => ':column déplacé à la position :position sur :count. Appliquez les colonnes pour enregistrer vos modifications.',
        'reordered_in_group' => ':column déplacé à la position :position sur :count dans :group.',
        'reordered_in_group_deferred' => ':column déplacé à la position :position sur :count dans :group. Appliquez les colonnes pour enregistrer vos modifications.',
        'actions' => [
            'move_up' => [
                'label' => 'Monter :column',
            ],
            'move_down' => [
                'label' => 'Descendre :column',
            ],
            'reorder' => [
                'label' => 'Réordonner la colonne',
            ],
        ],
    ],
    'columns' => [
        'editable' => [
            'saving' => 'Enregistrement de la modification…',
            'saved' => 'Modification enregistrée.',
            'failure' => 'L’enregistrement n’a pas pu être confirmé. Actualisez la page avant de réessayer.',
            'unavailable' => 'Cet élément ne peut plus être modifié.',
        ],
        'icon' => [
            'boolean' => [
                'true' => 'Oui',
                'false' => 'Non',
            ],
        ],
        'select' => [
            'loading_message' => 'Chargement…',
            'no_options_message' => 'Aucune option disponible.',
            'no_search_results_message' => 'Aucune option ne correspond à votre recherche.',
            'placeholder' => 'Sélectionnez une option',
            'searching_message' => 'Recherche…',
            'search_prompt' => 'Commencez à taper pour rechercher',
        ],
    ],
    'actions' => [
        'reorder_record' => [
            'label' => 'Réordonner l’élément :key',
        ],
        'move_record_up' => [
            'label' => 'Monter :label',
        ],
        'move_record_down' => [
            'label' => 'Descendre :label',
        ],
        'toggle_record_content' => [
            'label' => 'Développer/réduire l’élément :key',
        ],
    ],
    'loading' => 'Chargement…',
    'reordering' => [
        'position' => ':label, position :position sur :count.',
        'moved' => ':label déplacé à la position :position sur :count.',
    ],
    'result_count' => '{0} Aucun résultat|{1} :count résultat|[2,*] :count résultats',
];
