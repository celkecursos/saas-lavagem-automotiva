<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Ranking "Lava-rápido do mês" (task-17)
    |--------------------------------------------------------------------------
    |
    | Mínimo de avaliações no mês corrente pra um lava-rápido entrar no
    | ranking — evita que 1 avaliação isolada coloque alguém no topo.
    | Constante de regra de negócio, não precisa de tela de admin na v1
    | (se precisar ficar editável em runtime, migra pra uma settings
    | table, mesmo padrão de parking_billing_settings).
    |
    */
    'min_ratings_for_ranking' => 5,

];
