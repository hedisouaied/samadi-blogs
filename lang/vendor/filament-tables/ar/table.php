<?php

return [
    'column_manager' => [
        'position' => 'الموضع :position من :count.',
        'position_in_group' => 'الموضع :position من :count في :group.',
        'reordered' => 'تم نقل :column إلى الموضع :position من :count.',
        'reordered_deferred' => 'تم نقل :column إلى الموضع :position من :count. طبّق الأعمدة لحفظ تغييراتك.',
        'reordered_in_group' => 'تم نقل :column إلى الموضع :position من :count في :group.',
        'reordered_in_group_deferred' => 'تم نقل :column إلى الموضع :position من :count في :group. طبّق الأعمدة لحفظ تغييراتك.',
        'actions' => [
            'move_up' => [
                'label' => 'تحريك :column للأعلى',
            ],
            'move_down' => [
                'label' => 'تحريك :column للأسفل',
            ],
        ],
    ],
    'columns' => [
        'editable' => [
            'saving' => 'جارٍ حفظ التغيير…',
            'saved' => 'تم حفظ التغيير.',
            'failure' => 'تعذر تأكيد الحفظ. حدّث الصفحة قبل المحاولة مرة أخرى.',
            'unavailable' => 'لم يعد بالإمكان تعديل هذا العنصر.',
        ],
    ],
    'actions' => [
        'move_record_up' => [
            'label' => 'تحريك :label للأعلى',
        ],
        'move_record_down' => [
            'label' => 'تحريك :label للأسفل',
        ],
    ],
    'reordering' => [
        'position' => ':label، الموضع :position من :count.',
        'moved' => 'تم نقل :label إلى الموضع :position من :count.',
    ],
];
