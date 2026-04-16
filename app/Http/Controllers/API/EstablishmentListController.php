<?php

namespace App\Http\Controllers\API;

/**
 * Lista de estabelecimentos para o frontend TIM/Gplace.
 * Lista vazia até existir modelo/recurso alinhado na API.
 */
class EstablishmentListController extends BaseController
{
    public function __invoke()
    {
        return $this->sendResponse([]);
    }
}
