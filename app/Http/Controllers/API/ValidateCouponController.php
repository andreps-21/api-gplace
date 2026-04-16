<?php

namespace App\Http\Controllers\API;

use App\Actions\ValidateCoupon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ValidateCouponController extends BaseController
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request, ValidateCoupon $action)
    {
        $validator = $this->getValidationFactory()
            ->make(
                $request->all(),
                $this->rules($request)
            );

        if ($validator->fails()) {
            return $this->sendError('Erro de Validação.', $validator->errors()->toArray(), 422);
        }

        try {
            $coupon = $action->execute(
                $request->name,
                $request->total
            );

            return $this->sendResponse($coupon);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 403);
        }
    }

    private function rules(Request $request, $primaryKey = null, bool $changeMessages = false)
    {
        $rules = [
            'name' => ['required', 'string'],
            'total' => ['required', 'numeric']
        ];

        $messages = [];

        return !$changeMessages ? $rules : $messages;
    }
}
