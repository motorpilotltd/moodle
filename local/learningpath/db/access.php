<?php




$capabilities = array(

    'local/learningpath:add' => array(
        'captype' => 'write',
        'riskbitmask' => RISK_SPAM,
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        )
    ),

    'local/learningpath:delete' => array(
        'captype' => 'write',
        'riskbitmask' => 0,
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        )
    ),

    'local/learningpath:edit' => array(
        'captype' => 'write',
        'riskbitmask' => RISK_SPAM,
        'contextlevel' => CONTEXT_COURSECAT,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        )
    ),
 
);


